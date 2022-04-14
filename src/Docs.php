<?php

namespace Nunahsan\ApiDocs;

use ReflectionMethod;
use file;

class Docs extends \Illuminate\Support\ServiceProvider {

    protected static $objects = [];
    protected static $json_output = [];
    protected static $api_list = [];

    public function boot() {
        $this->loadViewsFrom(__DIR__ . '/views', 'apidocs');
    }

    public function register() {
        
    }

    public static function setConfig($objects = []) {
        self::$objects = $objects;

        foreach ($objects as $obj) {
            $className = $obj[0];
            $methods = $obj[1];
            foreach ($methods as $methodName) {
                self::processValidationRule($className, $methodName);
            }
        }
    }

    protected static function processValidationRule($className, $methodName) {
        $func = new ReflectionMethod($className, $methodName);
        $f = $func->getFileName();
        $start_line = $func->getStartLine();
        $end_line = $func->getEndLine() - 1;

        $source = file($f);
        $source = implode('', array_slice($source, 0, count($source)));
        $source = preg_split("/" . PHP_EOL . "/", $source);

        $body = '';
        for ($i = $start_line; $i < $end_line; $i++) {
            $body .= "{$source[$i]}\n";
        }

        preg_match('/\$ApiDocs \= (\[.*?\])\;/is', $body, $matches, PREG_UNMATCHED_AS_NULL);

        if (!isset($matches[1])) {
            return;
        }

        $str = $matches[1];

        $str = str_replace('[', '{', $str);
        $str = str_replace(']', '}', $str);
        $str = str_replace('=>', ':', $str);

        $arrs = json_decode($str, true);
        if (empty($arrs)) {
            return;
        }

        self::$api_list[] = $arrs['url'];

        $arrs['body'] = self::constructorElement($arrs['validation']['body']);
        $arrs['header'] = self::constructorElement($arrs['validation']['header']);
        self::$json_output[$className][$methodName] = $arrs;
    }

    protected static function constructorElement($elements) {
        $elems = [];
        foreach ((array) $elements as $k => $v) {
            $x = explode('|', $v);

            $description = NULL;
            $extra = [];
            foreach ((array) $x as $v2) {
                $y = explode(':', $v2);
                if (count($y) == 2) {
                    if ($y[0] == 'description') {
                        $description = $y[1];
                    } else if ($y[0] == 'in') {
                        $extra['options'] = explode(',', $y[1]);
                    } else if ($y[0] == 'min') {
                        $extra['length']['min'] = $y[1];
                    } else if ($y[0] == 'max') {
                        $extra['length']['max'] = $y[1];
                    }
                }
            }

            $elems[] = [
                'param' => $k,
                'required' => in_array('required', $x),
                'type' => self::defineType($x),
                'description' => $description,
                'extra' => json_encode($extra)
            ];
        }
        return $elems;
    }

    protected static function defineType($array = []) {
        $type = 'string';
        if (in_array('integer', $array)) {
            $type = 'integer';
        } else if (in_array('array', $array)) {
            $type = 'array';
        } else if (in_array('boolean', $array)) {
            $type = 'boolean';
        } else if (in_array('email', $array)) {
            $type = 'email';
        }
        return $type;
    }

    public static function getOutput() {
        return self::$json_output;
    }

    public static function getApiList() {
        return self::$api_list;
    }
    
    public static function cleanUpRule($apiDocs = []) {
        $res = $apiDocs['validation']['body'] ?? [];
        foreach ((array) $res as $attribute => $rule) {
            if (is_array($rule)) {
                unset($res[$attribute]['description']);
                foreach ((array) $rule as $k => $rule2) {
                    if ($rule2 == 'description' || (is_string($rule2) && substr($rule2, 0, 12) == 'description:')) {
                        unset($res[$attribute][$k]);
                    }
                }
            } else {
                $x = explode('|', $rule);
                foreach ((array) $x as $k => $rule) {
                    if (substr($rule, 0, 12) == 'description:') {
                        unset($x[$k]);
                    }
                }
                $res[$attribute] = implode('|', $x);
            }
        }
        return $res;
    }

}

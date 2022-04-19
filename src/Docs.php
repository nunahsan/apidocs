<?php

namespace Nunahsan\ApiDocs;

use ReflectionMethod;
use file;

class Docs extends \Illuminate\Support\ServiceProvider {

    protected static $objects = [];
    protected static $json_output = [];
    protected static $api_list = [];
    protected static $reserveChar = [
        'Ø', 'Ì', 'Í', '‡', 'Š', 'Œ', 'œ'
    ];

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

    protected static function regex_cleanup($str) {
        preg_match('/\$ApiDocs.*?(\[.*?[^\]]\]);/is', $str, $str);
        if (!empty($str)) {
            return $str[1];
        }
        return false;
    }

    protected static function regex_cleanup2($str) {
        $rs = self::$reserveChar;
        $codeChar = ['[', ']', '=>', '::', '{', ']'];

        $str = preg_replace("/\\\'/is", $rs[0], $str);
        $str = preg_replace('/\\\"/is', "$rs[0]$rs[0]", $str);
        $str = preg_replace("/'(\w+)'/is", '"$1"', $str);
        $str = preg_replace('/\s+/', ' ', $str);

        $str = preg_replace_callback('/".*?[^"]"/is', function ($m) use ($rs, $codeChar) {
            $m[0] = str_replace("'", $rs[0], $m[0]);
            foreach ((array) $codeChar as $k => $v) {
                $m[0] = str_replace($v, $rs[(int) $k + 1], $m[0]);
            }
            return $m[0];
        }, $str);

        //take out response attribute
        preg_match("/\"response\".*?('.*?[^']')/is", $str, $strResponse);
        if (!empty($strResponse) && isset($strResponse[1])) {
            $strResponse = $strResponse[1];
            $str = preg_replace("/(\"response\".*?)('.*?[^']')/is", '$1__RESPONSE__', $str);
        }

        $str = preg_replace_callback("/'.*?[^']'/is", function ($m) use ($rs, $codeChar) {
            $m[0] = str_replace('"', $rs[0], $m[0]);
            foreach ((array) $codeChar as $k => $v) {
                $m[0] = str_replace($v, $rs[(int) $k + 1], $m[0]);
            }
            return $m[0];
        }, $str);

        $str = preg_replace("/'(.*?[^'])'/", '"$1"', $str);
        $str = preg_replace("/(\w+::.*?[^,])([,\s\]])/", '"$1"$2', $str);

        //process body
        $done = false;
        while (!$done) {
            $strNew = self::regex_body_array_to_string($str);
            if ($str == $strNew) {
                $done = true;
            } else {
                $str = $strNew;
            }
        }

        //process extra comma
        $str = preg_replace("/,\s+\]/", ']', $str);

        //replace php syntax to json syntax
        $str = str_replace('[', '{', $str);
        $str = str_replace(']', '}', $str);
        $str = str_replace('=>', ':', $str);

        //put back response attribute
        if ($strResponse) {
            $strResponse = preg_replace('/"(.*?[^"])"/is','\"$1\"',$strResponse);
            $strResponse = preg_replace("/'(.*?[^'])'/is",'"$1"',$strResponse);
            $str = str_replace('__RESPONSE__', $strResponse, $str);
        }
        
        //store back original content
        $str = str_replace("$rs[0]$rs[0]", '\"', $str);
        $str = str_replace($rs[0], "'", $str);
        foreach ((array) $codeChar as $k => $v) {
            $str = str_replace($rs[(int) $k + 1], $v, $str);
        }

        return $str;
    }

    protected static function regex_body_array_to_string($str) {
        $str = preg_replace_callback('/("body".*?=>.*?\[.*?)(\[.*?\])/is', function ($m) {
            $m[2] = preg_replace("/,\s+\]/", ']', $m[2]);
            $m[2] = '"' . implode('|', json_decode($m[2], true)) . '"';
            return $m[1] . $m[2];
        }, $str);
        return $str;
    }

    protected static function get_content($className, $methodName) {
        $func = new ReflectionMethod($className, $methodName);
        $f = $func->getFileName();
        $start_line = $func->getStartLine();
        $end_line = $func->getEndLine() - 1;

        $source = file($f);
        $source = implode('', array_slice($source, 0, count($source)));
        $source = preg_split("/" . PHP_EOL . "/", $source);

        $content = '';
        for ($i = $start_line; $i < $end_line; $i++) {
            $content .= "{$source[$i]}\n";
        }

        return $content;
    }

    protected static function processValidationRule($className, $methodName) {
        $str = self::get_content($className, $methodName);

        //get $ApiDocs data
        $str = self::regex_cleanup($str);

        //process using regex
        $str = self::regex_cleanup2($str);

        //convert to json
        $arrs = json_decode($str, true);
        if (empty($arrs)) {
            return;
        }

        $arrs['response'] = isset($arrs['response']) ? json_decode(preg_replace("/'(.*?[^'])'/is", '"$1"', $arrs['response']), true, 512, JSON_BIGINT_AS_STRING) : [];

        self::$api_list[] = $arrs['name'];
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

                if ($y[0] == 'description') {
                    $desc = [];
                    for ($i = 1; $i < count($y); $i++) {
                        $desc[] = $y[$i];
                    }
                    $description = implode('', $desc);
                } else if (count($y) == 2) {
                    if ($y[0] == 'in') {
                        $extra['options'] = explode(',', $y[1]);
                    } else if ($y[0] == 'min') {
                        $extra['length']['min'] = $y[1];
                    } else if ($y[0] == 'max') {
                        $extra['length']['max'] = $y[1];
                    }
                } else if (count($y) == 3) {
                    if (!isset($extra['addonRule'])) {
                        $extra['addonRule'] = [];
                    }
                    $extra['addonRule'][] = $y[0] . ':' . $y[2];
                }
            }

            $elems[] = [
                'param' => $k,
                'required' => in_array('required', $x),
                'type' => self::defineType($x),
                'description' => $description,
                'extra' => json_encode($extra)
            ];

            if ($k == 'password' && in_array('confirmed', $x)) {
                $elems[] = [
                    'param' => 'password_confirmation',
                    'required' => in_array('required', $x),
                    'type' => self::defineType($x),
                    'description' => $description,
                    'extra' => json_encode($extra)
                ];
            }
        }
        return $elems;
    }

    protected static function defineType($array = []) {
        $type = 'String';
        if (in_array('integer', $array)) {
            $type = 'Integer';
        } else if (in_array('array', $array)) {
            $type = 'Array';
        } else if (in_array('boolean', $array)) {
            $type = 'Boolean';
        } else if (in_array('email', $array)) {
            $type = 'Email';
        } else if (in_array('date_format:Y-m-d H:i:s', $array)) {
            $type = 'DateTime Y-m-d H:i:s';
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

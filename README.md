# apidocs
Laravel package - api document generator

# sample usage
```
#routes file

use Nunahsan\ApiDocs\Docs;

Route::get('/docs', function (Request $request) {
    Docs::setConfig([
        [
            App\Http\Controllers\Controller::class, [
                'method1', 'method2', 'method3'
            ]
        ]
    ]);
    
    return view('apidocs::view1')
            ->with('data', Docs::getOutput())
            ->with('apiList', Docs::getApiList())
            ->with('header', [
                'title' => 'API For ' . env('APP_NAME'),
                'version' => 'V1.0',
                'endpoint' => 'https://endoint.api.com'
    ]);
});
```
```
# controller file

public function method1(Request $request) {
    $ApiDocs = [
        "name" => "Auth : Login",
        "url" => "/api/test1",
        "method" => "POST",
        "description" => "This is api description",
        "validation" => [
            "header" => [
                "content-type" => "required|string|description:application/json",
                "authorization" => "required|string|description:Bearer Token"
            ],
            "body" => [
                "name" => "required|string|max:50|min:3|description:hello world",
                "description" => "required|string|min:3|max:200",
                "status" => "required|integer|in:0,1",
                "seq" => "required|integer|min:0",
                "image_url" => "required|string|min:5",
                "banner" => "integer|in:1,2"
            ]
        ],
        "response" => '{"data":"timezone":"Asia\/Kuala_Lumpur","environment":"development","execution_duration":"0.012310028076172 sec","log_id":"760497ff-5a22-48c8-aa09-4fb42e502be1","message":"Granted"}'
    ];

    $validator = Validator::make($request->all(), $ApiDocs['validation']['body']);

    if ($validator->fails()) {
        return Response()->json($validator->errors());
    }
}

```

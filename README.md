# apidocs
Laravel package - api document generator

# sample usage
```
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
                'endpoint_development' => 'https://endoint1',
                'endpoint_sandbox' => 'https://endoint2',
                'endpoint_live' => 'https://endoint3'
    ]);
});

```

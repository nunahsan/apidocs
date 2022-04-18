<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <title>API Documentation</title>
        <style>
            body{
                position:relative;
            }
            .panel-left{
                width: calc(100vw - 350px);
                float: left;
            }
            .panel-right{
                position: fixed;
                right: 10px;
                width: 280px;
                top: 12px;
                height: calc(100vh - 25px);
                overflow-y: auto;
            }
            h5 {
                font-size:16px;
            }
            h6 {
                font-size:14px;
            }
            h7 {
                font-size:12px;
            }
            table td,th {
                font-size:14px;
            }
            .list-group-item {
                font-size:14px;
            }
            code {
                display: block;
                background-color: #333;
                color:#fff;
                border-radius: 3px;
                padding:0 10px 10px;
                white-space: pre-wrap;
                font-family: monospace, sans-serif;
            }
        </style>
    </head>
    <body >
        <div class="container-fluid">
            <div class="panel-right">
                <div id="list-example" class="list-group mt-2">
                    <a class="list-group-item list-group-item-action" href="#list-item-x">API Summary</a>
                    @foreach($apiList as $key => $item)
                        <a class="list-group-item list-group-item-action" href="#list-item-{{$key}}">{{$item}}</a>
                    @endforeach
                </div>
            </div>
            <div class="panel-left">
                
                <div>
                    <div class="mt-2 mb-5 head" id="list-item-x">
                        <h5>{{$header['title']}}</h5>
                        <h6>Version: {{$header['version']}}</h6>
                        <h6>API End Point: <i>{{$header['endpoint']}}</i></h6>
                    </div>

                    <?php $key = -1; ?>
                    @foreach($data as $class)
                        @foreach($class as $method) 
                            <?php $key++; ?>
                            <div id="list-item-{{$key}}" class="card text-dark mb-4">
                                <div class="card-header text-primary"><b>{{$method['name']}} :</b> {{$method['method']}} {{$method['url']}}</div>
                                <div class="card-body">
                                    <h6>Description :</h6>
                                    <p>{{$method['description']}}</p>

                                    <h6>Header :</h6>
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr class="table-light">
                                                <th>Key</th>
                                                <th>Type</th>
                                                <th>Required</th>
                                                <th>Additional Rules</th>
                                                <th>Description</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($method['header'] as $item) 
                                            <tr>
                                                <td>{{$item['param']}}</td>
                                                <td>{{$item['type']}}</td>
                                                <td>{{$item['required']?'Yes':'No'}}</td>
                                                <td>{{$item['extra']!='[]'?$item['extra']:''}}</td>
                                                <td>{{$item['description']}}</td>
                                            </tr>
                                        @endforeach
                                        @if(empty($method['header']))
                                            <tr>
                                                <td colspan="5"><i>N/A</i></td>
                                            </tr>
                                        @endif
                                        </tbody>
                                    </table>

                                    <h6>Body :</h6>
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr class="table-light">
                                                <th>Key</th>
                                                <th>Type</th>
                                                <th>Required</th>
                                                <th>Additional Rules</th>
                                                <th>Description</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($method['body'] as $item) 
                                            <tr>
                                                <td>{{$item['param']}}</td>
                                                <td>{{$item['type']}}</td>
                                                <td>{{$item['required']?'Yes':'No'}}</td>
                                                <td>{{$item['extra']!='[]'?$item['extra']:''}}</td>
                                                <td>{{$item['description']}}</td>
                                            </tr>
                                        @endforeach
                                        @if(empty($method['body']))
                                            <tr>
                                                <td colspan="5"><i>N/A</i></td>
                                            </tr>
                                        @endif
                                        </tbody>
                                    </table>
                                    
                                    <h6><b>Sample Response :</b></h6>
                                    <div>
                                        <code>{{json_encode($method['response'],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)}}</code>
                                    </div>                                    
                                </div>
                            </div>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script>
            $(document).ready(function(){
                $(".list-group-item").on('click', function() {
                    $('.list-group-item').removeClass('active');
                    $(this).addClass('active');
                });
                if (window.location.hash) {
                    $(".list-group-item[href='"+window.location.hash+"']").addClass('active')
                } else {
                    $(".list-group-item[href='#list-item-x']").addClass('active')
                }
            });
        </script>
    </body>
</html>

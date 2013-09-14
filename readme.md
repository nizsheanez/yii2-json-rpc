JsonRpc Server and Client for Yii2


##Usage Server

1) Install with Composer

~~~php
"require": {
    "nizsheanez/yii2-json-rpc": "1.*",
},

php composer.phar update
~~~

2) Add action to controller

~~~php
public function actions()
{
    return array(
        'index' => array(
            'class' => '\nizsheanez\JsonRpc\Action',
        ),
    );
}
~~~

3) All methods of controller now available as JsonRpc methods

4) Enjoy!


##Usage Client

~~~php
$client = new \nizsheanez\JsonRpc\Client('http://url/of/webservice');

$response = $client->someMethod($arg1, $arg2);
~~~


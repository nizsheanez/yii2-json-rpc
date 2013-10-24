<?php
namespace nizsheanez\jsonRpc\traits;

use nizsheanez\jsonRpc\Protocol;

trait Client
{

    public function callServer($method, $params, $url)
    {
        $request = $this->getRequest($method, $params);
        $ctx = $this->getHttpStreamContext($request);
        $jsonResponse = file_get_contents($this->url, false, $ctx);
        return $jsonResponse;
    }

    public function getHttpStreamContext($request)
    {
        $jsonRequest = json_encode($request);

        $ctx = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: " . Protocol::MIME . "\r\n",
                'content' => $jsonRequest
            ]
        ]);

        return $ctx;
    }

    public static function isValidRequest($request)
    {
        $version = isset($request['jsonrpc']) && $request['jsonrpc'] == '2.0';
        $method = isset($request['method']);
        $id = isset($request['id']);
        return $version && $method && $id;
    }

    protected function getRequest($method = null, $params = null)
    {
        return [
            'jsonrpc' => '2.0',
            'method' => $method,
            'params' => $params,
            'id' => $this->newId()
        ];
    }

    public function newId()
    {
        return md5(microtime());
    }

}
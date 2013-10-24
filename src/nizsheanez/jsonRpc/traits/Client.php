<?php
namespace nizsheanez\jsonRpc\traits;

use nizsheanez\jsonRpc\Exception;

trait Client
{
    public function callServer($method, $params, $url)
    {
        $request = $this->getRequest($method, $params);
        $ctx = $this->getHttpStreamContext($request);
        $jsonResponse = file_get_contents($url, false, $ctx);


        if ($jsonResponse === '') {
            throw new Exception('fopen failed', Exception::INTERNAL_ERROR);
        }

        $response = json_decode($jsonResponse);

        if ($response === null) {
            throw new Exception('JSON cannot be decoded', Exception::INTERNAL_ERROR);
        }

        if ($response->id != $id) {
            throw new Exception('Mismatched JSON-RPC IDs', Exception::INTERNAL_ERROR);
        }

        if (property_exists($response, 'error')) {
            throw new Exception($response->error->message, $response->error->code);
        } else if (property_exists($response, 'result')) {
            return $response->result;
        } else {
            throw new Exception('Invalid JSON-RPC response', Exception::INTERNAL_ERROR);
        }
    }

    public function getHttpStreamContext($request)
    {
        $jsonRequest = json_encode($request);

        $ctx = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: " . Exception::MIME . "\r\n",
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
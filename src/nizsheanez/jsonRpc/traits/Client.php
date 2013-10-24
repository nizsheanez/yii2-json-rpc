<?php
namespace nizsheanez\jsonRpc\traits;

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


    public static function checkContentType()
    {
        return empty($_SERVER['CONTENT_TYPE']) || $_SERVER['CONTENT_TYPE'] != self::MIME;
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
        if (!$this->data) {
            $this->data = [
                'jsonrpc' => '2.0',
                'method' => $menthod,
                'params' => $params,
                'id' => $this->newId()
            ];
        }
        return $this->data;
    }

    public function getMethod()
    {
        return $this->data['method'];
    }

    public function getRequestId()
    {
        return $this->data['id'];
    }

    public function getParams()
    {
        return isset($this->data['params']) ? $this->data['params'] : null;
    }

}
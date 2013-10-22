<?php
namespace nizsheanez\jsonRpc;

class Protocol extends \yii\base\Object
{
    const PARSE_ERROR = -32700;
    const INVALID_REQUEST = -32600;
    const METHOD_NOT_FOUND = -32601;
    const INVALID_PARAMS = -32602;
    const INTERNAL_ERROR = -32603;


    const MIME = 'application/json-rpc';

    protected $message;
    protected $request;

    public static function client($method, $params)
    {
        $protocol = new static;
        $protocol->request = $protocol->createClientRequest($method, $params);
        return $protocol;
    }

    public static function server($message)
    {
        $protocol = new static;
        $protocol->message = $message;
        $protocol->request = json_decode($message, true);
        if (!static::isValidRequest($protocol->request)) {
            throw new Exception("Invalid Request", Protocol::INVALID_REQUEST);
        }
        return $protocol;
    }

    public function getHttpStreamContext()
    {
        $jsonRequest = json_encode($this->request);

        $ctx = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: " . Protocol::MIME . "\r\n",
                'content' => $jsonRequest
            ]
        ]);

        return $ctx;
    }

    public function getMethod()
    {
        return $this->request['method'];
    }

    public function getRequestId()
    {
        return $this->request['id'];
    }

    public static function checkContentType()
    {
        return empty($_SERVER['CONTENT_TYPE']) || $_SERVER['CONTENT_TYPE'] != self::MIME;
    }

    protected function getRequest()
    {
        return $this->request;
    }

    protected static function isValidRequest($request)
    {
        return isset($request['jsonrpc']) && $request['jsonrpc'] == '2.0' && isset($request['method']);
    }

    public function getParams()
    {
        return isset($this->request['params']) ? $this->request['params'] : null;
    }

    /**
     * @param null $output
     * @param null $exception
     */
    public function answer($output = null, $exception = null)
    {
        $answer = array(
            'jsonrpc' => '2.0',
            'id' => isset($this->request['id']) ? $this->request['id'] : null,
        );
        if ($exception) {
            $answer['error'] = $exception->getErrorAsArray();
        }
        if ($output) {
            $answer['result'] = $output;
        }
        return json_encode($answer);
    }

    public function createClientRequest($menthod, $params) {
        $id = md5(microtime());
        $request = [
            'jsonrpc' => '2.0',
            'method' => $menthod,
            'params' => $params,
            'id' => $id
        ];
        return $request;
    }

}
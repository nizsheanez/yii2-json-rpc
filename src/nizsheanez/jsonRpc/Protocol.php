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
    protected $data;

    public static function client($method, $params)
    {
        $protocol = new static;
        $protocol->data = $protocol->getRequest($method, $params);
        return $protocol;
    }

    public static function server($message)
    {
        $protocol = new static;
        $protocol->message = $message;
        $protocol->data = json_decode($message, true);
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
        return $this->data['method'];
    }

    public function getRequestId()
    {
        return $this->data['id'];
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

    public static function isValidResponse($request)
    {
        $version = isset($request['jsonrpc']) && $request['jsonrpc'] == '2.0';
        $method = isset($request['method']);
        $data = isset($request['result']) || isset($request['error']);
        $additional = true;
        if (isset($request['error'])) {
            $additional = isset($request['error']['code'], $request['error']['message']);
        }
        return $version && $method && $data && $additional;
    }

    public function getParams()
    {
        return isset($this->data['params']) ? $this->data['params'] : null;
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

    /**
     * @param null $output
     * @param null $exception
     */
    public function getResponse($output = null, $exception = null)
    {
        $answer = array(
            'jsonrpc' => '2.0',
            'id' => isset($this->data['id']) ? $this->data['id'] : $this->newId(),
        );
        if ($exception) {
            if ($exception instanceof Exception) {
                $answer['error'] = $exception->getErrorAsArray();
            } else {
                $answer['error'] = [
                    'code' => self::INTERNAL_ERROR,
                    'message' => 'Internal error'
                ];
            }
        }
        if ($output) {
            $answer['result'] = $output;
        }

        if (self::isValidResponse($answer)) {
            $answer['error'] = [
                'code' => self::INTERNAL_ERROR,
                'message' => 'Internal error'
            ];
        }

        return json_encode($answer);
    }

    public function newId()
    {
        return md5(microtime());
    }
}
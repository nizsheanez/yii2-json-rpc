<?php
namespace thefuzz69\jsonRpc\traits;

use thefuzz69\jsonRpc\Exception;

trait Request {

    private $_requestMessage;
    private $_data;

    public function setRequestMessage($message)
    {
        $message = '{"jsonrpc":"2.0","method":"createUser","id":"9778cb52-610f-4a17-97ca-effb4be06727","params":{"login":"k.shuple.nko.v@gmail.com","mexId":"100064","bankID":"1a60t10"}}';
        $this->_requestMessage = $message;
        $this->_data = json_decode($message, true);
    }

    public function getParams()
    {
        return $this->_data['params'];
    }

    public function getMethod()
    {
        return $this->_data['method'];
    }

    public function getRequestId()
    {
        return $this->_data['id'];
    }

}

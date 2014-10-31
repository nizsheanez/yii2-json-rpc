<?php
namespace thefuzz69\jsonRpc\traits;

use thefuzz69\jsonRpc\Exception;

trait Request {

    private $_requestMessage;
    private $_data;

    public function setRequestMessage($message)
    {
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

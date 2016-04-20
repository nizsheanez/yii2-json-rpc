<?php

namespace nizsheanez\jsonRpc\traits;

trait Request
{
    private $_requestMessage;
    private $_data;

    public function setRequestMessage($message)
    {
        $this->_requestMessage = $message;
        $this->_data           = json_decode($message, true);
    }

    public function getParams($method)
    {
        $params = [];
        $args   = isset($this->_data['params']) ? $this->_data['params'] : [];
        foreach ($method->getParameters() as $param) {
            /* @var $param ReflectionParameter */
            if (isset($args[$param->getName()])) {
                $params[] = $args[$param->getName()];
            } elseif ($param->isDefaultValueAvailable()) {
                $params[] = $param->getDefaultValue();
            } else {
                $params[] = null;
            }
        }

        return $params;
    }

    public function getMethod()
    {
        return isset($this->_data['method']) ? $this->_data['method'] : null;
    }

    public function getRequestId()
    {
        return isset($this->_data['id']) ? $this->_data['id'] : $this->newId();
    }
}
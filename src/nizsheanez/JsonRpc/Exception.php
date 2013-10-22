<?php
namespace nizsheanez\jsonRpc;

/**
 * @author sergey.yusupov, alex.sharov
 */
class Exception extends \yii\base\Exception
{

    private $data = null;

    public function __construct($message, $code, $data = null)
    {
        $this->data = $data;
        parent::__construct($message, $code);
    }

    public function getErrorAsArray()
    {
        $result = array(
            'code' => $this->getCode(),
            'message' => $this->getMessage(),
        );
        if ($this->data !== null) $result['data'] = $this->data;
        return $result;
    }
}
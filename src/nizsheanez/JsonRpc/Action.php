<?
namespace nizsheanez\jsonRpc;

use Yii;
use ReflectionClass;
use nizsheanez\JsonRpc\Exception;
use yii\web\HttpException;


/**
 * @author alex.sharov
 */
class Action extends \yii\base\Action
{
    protected $request;

    public function run()
    {
        $this->failIfNotAJsonRpcRequest();
        Yii::beginProfile('service.request');
        $this->request = $output = null;
        try {
            $this->request = $this->getRequest();
            try {
                $output = $this->tryToRunMethod();
            } catch (Exception $e) {
                Yii::error($e, 'service.error');
                throw new Exception($e->getMessage(), Exception::INTERNAL_ERROR);
            }

            $this->answer($output);
        } catch (Exception $e) {
            $this->answer($output, $e);
        }
        Yii::endProfile('service.request');
    }

    /**
     * @param null $output
     * @param null $exception
     */
    protected function answer($output = null, $exception = null)
    {
        $answer = array(
            'jsonrpc' => '2.0',
            'id' => isset($this->request['id'])? $this->request['id'] : null,
        );
        if ($exception) {
            $answer['error'] = $exception->getErrorAsArray();
        }
        if ($output) {
            $answer['result'] = $output;
        }
        echo json_encode($answer);
    }

    /**
     * @return string|callable|\ReflectionMethod
     */
    protected function getHandler()
    {
        $class = new ReflectionClass($this->controller);
        $method = $class->getMethod($this->request['method']);

        return $method;
    }

    /**
     * @param string|callable|\ReflectionMethod $method
     * @param array $params
     * @return mixed
     */
    protected function runMethod($method, $params)
    {
        return $method->invokeArgs($this->controller, $params);
    }

    protected function tryToRunMethod()
    {
        $method = $this->getHandler();

        ob_start();

        Yii::beginProfile('service.request.action');
        $result = $this->runMethod($method, isset($this->request['params']) ? $this->request['params'] : null);
        Yii::endProfile('service.request.action');

        $output = ob_get_clean();
        if ($output) Yii::info($output, 'service.output');

        if (!$class->hasMethod($this->request['method']))
            throw new Exception("Method not found", Exception::METHOD_NOT_FOUND);

        return $output;
    }

    protected function failIfNotAJsonRpcRequest()
    {
        if (Yii::$app->request->requestType != 'POST'
            || empty($_SERVER['CONTENT_TYPE'])
            || $_SERVER['CONTENT_TYPE'] != "application/json-rpc"
        ) throw new HttpException(404, "Page not found");
    }

    /**
     * @throws Exception
     */
    protected function getRequest()
    {
        $request = json_decode(file_get_contents('php://input'), true);

        if (!$this->isValidRequest($request)) {
            throw new Exception("Invalid Request", Exception::INVALID_REQUEST);
        }

        return $request;
    }

    protected function isValidRequest($request)
    {
        return isset($request['jsonrpc']) && $request['jsonrpc'] == '2.0' && isset($request['method']);
    }

}

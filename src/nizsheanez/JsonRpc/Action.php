<?
namespace nizsheanez\jsonRpc;

use Yii;
use ReflectionClass;
use yii\web\HttpException;


/**
 * @author alex.sharov
 */
class Action extends \yii\base\Action
{
    /**
     * @var Protocol
     */
    protected $protocol;

    public function run()
    {
        $this->failIfNotAJsonRpcRequest();
        Yii::beginProfile('service.request');
        $this->request = $output = null;
        try {
            $this->protocol = Protocol::server(file_get_contents('php://input'));
            try {
                $output = $this->tryToRunMethod();
            } catch (Exception $e) {
                Yii::error($e, 'service.error');
                throw new Exception($e->getMessage(), Protocol::INTERNAL_ERROR);
            }

            echo $this->protocol->answer($output);
        } catch (Exception $e) {
            echo $this->protocol->answer($output, $e);
        }
        Yii::endProfile('service.request');
    }


    /**
     * @return string|callable|\ReflectionMethod
     */
    protected function getHandler()
    {
        $class = new ReflectionClass($this->controller);

        if (!$class->hasMethod($this->protocol->getMethod()))
            throw new Exception("Method not found", Protocol::METHOD_NOT_FOUND);

        $method = $class->getMethod($this->protocol->getMethod());

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
        $this->runMethod($method, $this->protocol->getParams());
        Yii::endProfile('service.request.action');

        $output = ob_get_clean();
        if ($output) {
            Yii::info($output, 'service.output');
        }

        return $output;
    }

    protected function failIfNotAJsonRpcRequest()
    {
        if (Yii::$app->request->requestType != 'POST' || Protocol::checkContentType()) {
            throw new HttpException(404, "Page not found");
        }
    }


}

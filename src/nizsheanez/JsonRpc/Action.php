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
    public function run()
    {
        $this->failIfNotAJsonRpcRequest();
        Yii::beginProfile('service.request');
        $request = $output = null;
        try {
            $request = $this->getRequest();
            try {
                $output = $this->tryToRunMethod($request);
            } catch (Exception $e) {
                Yii::error($e, 'service.error');
                throw new Exception($e->getMessage(), Exception::INTERNAL_ERROR);
            }

            $this->answer($request, $output);
        } catch (Exception $e) {
            $this->answer($request, $output, $e);
        }
        Yii::endProfile('service.request');
    }

    protected function answer($request, $output = null, $exception = null)
    {
        $answer = array(
            'jsonrpc' => '2.0',
            'id' => isset($request['id'])? $request['id'] : null,
        );
        if ($exception) {
            $answer['error'] = $exception->getErrorAsArray();
        }
        if ($output) {
            $answer['result'] = $output;
        }
        echo json_encode($answer);
    }

    protected function tryToRunMethod($request)
    {
        $class = new ReflectionClass($this->controller);
        $method = $class->getMethod($request['method']);

        ob_start();

        Yii::beginProfile('service.request.action');
        $result = $method->invokeArgs($this->controller, isset($request['params'])? $request['params'] : null);
        Yii::endProfile('service.request.action');

        $output = ob_get_clean();
        if ($output) Yii::info($output, 'service.output');

        if (!$class->hasMethod($request['method']))
            throw new Exception("Method not found", Exception::METHOD_NOT_FOUND);

        return $output;
    }
    private function failIfNotAJsonRpcRequest()
    {
        if (Yii::$app->request->requestType != 'POST'
            || empty($_SERVER['CONTENT_TYPE'])
            || $_SERVER['CONTENT_TYPE'] != "application/json-rpc"
        ) throw new HttpException(404, "Page not found");
    }

    /**
     * @param $request
     * @throws Exception
     */
    private function getRequest()
    {
        $request = json_decode(file_get_contents('php://input'), true);

        if ($request === null
            || !isset($request['jsonrpc'])
            || $request['jsonrpc'] != '2.0'
            || !isset($request['method'])
        ) throw new Exception("Invalid Request", Exception::INVALID_REQUEST);
    }

}

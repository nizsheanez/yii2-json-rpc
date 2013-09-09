<?
namespace nizsheanez\JsonRpc;

use Yii;
use ReflectionClass;
use nizsheanez\JsonRpc\Exception;
use yii\web\HttpException;


/**
 * @author alex.sharov
 */
class Action extends \yii\base\Action
{
    public function actionIndex()
    {
        $this->failIfNotAJsonRpcRequest();
        Yii::beginProfile('service.request');
        $request = $result = null;
        try {
            $request = json_decode(file_get_contents('php://input'), true);
            $this->failIfRequestIsInvalid($request);
            try {
                $class = new ReflectionClass($this->controller);

                if (!$class->hasMethod($request['method']))
                    throw new Exception("Method not found", -32601);

                $method = $class->getMethod($request['method']);

                ob_start();

                Yii::beginProfile('service.request.action');
                $result = $method->invokeArgs($this->controller, isset($request['params'])? $request['params'] : null);
                Yii::endProfile('service.request.action');

                $output = ob_get_clean();
                if ($output) Yii::info($output, 'service.output');

            } catch (Exception $e) {
                Yii::error($e, 'service.error');
                throw new Exception($e->getMessage(), -32603);
            }

            if (!empty($request['id'])) {
                echo json_encode(array(
                    'jsonrpc' => '2.0',
                    'id' => $request['id'],
                    'result' => $output,
                ));
            }
        } catch (Exception $e) {
            echo json_encode(array(
                'jsonrpc' => '2.0',
                'id' => isset($request['id'])? $request['id'] : null,
                'error' => $e->getErrorAsArray(),
            ));
        }
        Yii::endProfile('service.request');
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
    private function failIfRequestIsInvalid($request)
    {
        if ($request === null
            || !isset($request['jsonrpc'])
            || $request['jsonrpc'] != '2.0'
            || !isset($request['method'])
        ) throw new Exception("Invalid Request", -32600);
    }

}

<?
namespace nizsheanez\jsonRpc;

/**
 * @author sergey.yusupov, alex.sharov
 */
class Client
{

    protected $url;

    public function __construct($url = null)
    {
        $this->url = $url;
    }

    public function __call($name, $arguments)
    {
        $ctx = Protocol::client($name, $arguments)->getHttpStreamContext();
        $jsonResponse = file_get_contents($this->url, false, $ctx);

        if ($jsonResponse === '') {
            throw new Exception('fopen failed', Protocol::INTERNAL_ERROR);
        }

        $response = json_decode($jsonResponse);

        if ($response === null) {
            throw new Exception('JSON cannot be decoded', Protocol::INTERNAL_ERROR);
        }

        if ($response->id != $id) {
            throw new Exception('Mismatched JSON-RPC IDs', Protocol::INTERNAL_ERROR);
        }

        if (property_exists($response, 'error')) {
            throw new Exception($response->error->message, $response->error->code);
        } else if (property_exists($response, 'result')) {
            return $response->result;
        } else {
            throw new Exception('Invalid JSON-RPC response', Protocol::INTERNAL_ERROR);
        }

    }
}

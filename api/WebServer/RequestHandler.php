<?php


namespace BS_API\WebServer;


use BS_API\FunctionGroups\Common;
use BS_API\Utility\GlobalConfig;
use BS_API\Utility\GlobalFunctions;
use Swoole\Http\Response;
use Swoole\Http\Request;
use Swoole\Http\Server;

class RequestHandler
{
    private Server $server;
    private Request $request;
    private Response $response;
    private array $requestArray;

    public static function getInstance(Server $server, Request $request, Response $response)
    {
        return new RequestHandler($server, $request, $response);
    }

    private function __construct(Server $server, Request $request, Response $response)
    {
        $this->server = $server;
        $this->request = $request;
        $this->response = $response;
        if ($this->request->server['request_method'] === 'POST') {
            $this->requestArray = array(
                'data' => json_decode($this->request->getContent(), true),
                'method' => 'POST',
                'server' => $this->server
            );
        } else {
            $this->requestArray = array(
                'data' => $this->request->get,
                'method' => 'GET',
                'server' => $this->server
            );
        }
        $this->response->header('Access-Control-Allow-Origin', GlobalConfig::ALLOW_ORIGIN);
    }

    public function execute()
    {
        if (isset($this->requestArray['data']['function'])) {
            $this->requestArray['data']['function'] = GlobalFunctions::camelize($this->requestArray['data']['function']);
            if (in_array($this->requestArray['data']['function'], GlobalConfig::FUNCTION_CONFIG['Common'])) {
                $response = Common::getInstance()->{$this->requestArray['data']['function']}(
                    $this->requestArray, $this->request, $this->response
                );
            } else {
                $response = GlobalFunctions::generateResponse(GlobalConfig::UNDEFINED_FUNCTION);
            }
        } else {
            $response = GlobalFunctions::generateResponse(GlobalConfig::MISSING_PARAMETER_FUNCTION);
        }
        $this->sendResponse($response);
    }

    private function sendResponse($responseData)
    {
        $this->response->end(is_array($responseData) ? json_encode($responseData) : $responseData);
    }
}
<?php


namespace BS_API;


use BS_API\Utility\GlobalConfig;
use BS_API\Utility\GlobalFunctions;

class RequestProcessing
{
    private array $request;

    public function __construct()
    {
        header('Access-Control-Allow-Origin: ' . GlobalConfig::ALLOW_ORIGIN);
        if ($post_request = file_get_contents('php://input')) {
            $request_data = json_decode($post_request, true);
            if (!$request_data) {
                echo json_encode(GlobalFunctions::generateResponse(GlobalConfig::UNPARSABLE_FORMAT));
                exit();
            }
            $this->request = array(
                'data' => $request_data,
                'method' => 'POST'
            );
        } else {
            $this->request = array(
                'data' => $_GET,
                'method' => 'GET'
            );
        }
    }

    public function execute()
    {
        if ($this->request['method'] === 'GET') {
            return GlobalFunctions::generateResponse(GlobalConfig::FORBIDDEN_METHOD);
        }
        if (isset($this->request['data']['function'])) {
            $this->request['data']['function'] = GlobalFunctions::camelize($this->request['data']['function']);
            if (!isset($this->request['data']['function_group']) or
                $this->request['data']['function_group'] === 'Common') {
                return GlobalFunctions::generateResponse(GlobalConfig::UNDEFINED_FUNCTION_GROUP);
            }
            $this->request['data']['function_group'] =
                GlobalFunctions::camelize($this->request['data']['function_group']);
            if (isset(GlobalConfig::FUNCTION_CONFIG[$this->request['data']['function_group']])) {
                if (in_array($this->request['data']['function'],
                    GlobalConfig::FUNCTION_CONFIG[$this->request['data']['function_group']])) {
                    $be_called_class_name = __NAMESPACE__ . '\FunctionGroups\\' . $this->request['data']['function_group'];
                    $be_called_class = new $be_called_class_name;
                    return $be_called_class->{$this->request['data']['function']}($this->request);
                } else {
                    return GlobalFunctions::generateResponse(GlobalConfig::UNDEFINED_FUNCTION);
                }
            } else {
                return GlobalFunctions::generateResponse(GlobalConfig::UNDEFINED_FUNCTION_GROUP);
            }
        } else {
            return GlobalFunctions::generateResponse(GlobalConfig::MISSING_PARAMETER_FUNCTION);
        }
    }
}
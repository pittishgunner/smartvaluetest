<?php
/**
 * Created by PhpStorm.
 * User: MaD DucK
 * Date: 04.10.2018
 * Time: 09:45
 */

namespace App\JsonRpc;


class Server
{
    const VER = '2.0';
    private $_exec;

    public function __construct(Executor $exec)
    {
        $this->_exec = $exec;
    }

    public function respond($inputJson = "")
    {
        $output = $this->processIncoming($inputJson);

        if ($output === null) {
            return null;
        }

        return json_encode($output);
    }

    public function processIncoming($inputJson)
    {
        if (empty($inputJson)) {
            return self::error(null, 1000, 'Input string is empty');
        }
        if (!is_string($inputJson)) {
            return self::error(null, 1100, 'Input is not a string');
        }
        $inOb = json_decode($inputJson, true);
        if (!$inOb && !is_array($inOb)) {
            return self::error(null, 1200, 'Input string is not a valid json string');
        }
        if (empty($inOb)) {
            return self::error(null, 1300, 'Input json is empty');
        }
        if (isset($inOb[0])) {
            return $this->processMany($inOb);
        }
        return $this->processOne($inOb);
    }

    private static function error($id, $code, $message)
    {
        return [
            'jsonrpc' => self::VER,
            'id' => $id,
            'error' => [
                'code' => $code,
                'message' => $message
            ]
        ];
    }

    public function processMany($inOb)
    {
        $many = [];
        foreach ($inOb as $one) {
            $many[] = $this->processOne($one);
        }
        return $many;
    }

    public function processOne($inOb)
    {

        // jsonrpc
        // A String specifying the version of the JSON-RPC protocol. MUST be exactly "2.0"

        if (!isset($inOb['jsonrpc']) || (isset($inOb['jsonrpc']) && $inOb['jsonrpc'] != self::VER)) {
            return self::error(null, 1400, 'Server only accepts jsonrpc version 2.0');
        }

        // method
        // A String containing the name of the method to be invoked.
        // Method names that begin with the word rpc followed by a period character (U+002E or ASCII 46) are reserved
        // for rpc-internal methods and extensions and MUST NOT be used for anything else.
        if (!isset($inOb['method']) || (isset($inOb['method']) && empty($inOb['method']))) {
            return self::error(null, 1500, 'Method is missing');
        }
        if (!is_string($inOb['method'])) {
            return self::error(null, 1600, 'Method must be a string');
        }
        if (substr($inOb['method'], 0, 4) == "rpc-") {
            return self::error(null, 1700, 'Internal method: ' . $inOb['method'] . ' cannot be used');
        }

        // params
        // A Structured value that holds the parameter values to be used during the invocation of the method.
        // This member MAY be omitted.
        // 4.2 Parameter Structures
        // If present, parameters for the rpc call MUST be provided as a Structured value.
        // Either by-position through an Array or by-name through an Object.

        if (array_key_exists('params', $inOb)) {
            $params = $inOb['params'];
            if (!is_array($params)) {
                return self::error(null, 1800, "Params cannot be null if provided");
            }
        } else {
            $params = [];
        }
        $isNotification = false;
        if (!isset($inOb['id'])) {
            $isNotification = true;
        }
        if ($isNotification)
            return $this->executeNotification($inOb['method'], $params);
        else
            return $this->executeQuery($inOb['id'], $inOb['method'], $params);
    }

    public function executeNotification($method, $params)
    {
        try {
            $this->_exec->execute($method, $params);
        } catch (\Exception $exception) {

        }
    }

    public function executeQuery($id, $method, $params)
    {
        try {
            $results = $this->_exec->execute($method, $params);
            return self::success($id, $results);
        } catch (\Exception $exception) {
            $code = $exception->getCode();
            $message = $exception->getMessage();

            return self::error($id, $code, $message);
        }
    }

    private static function success($id, $result)
    {
        return [
            'jsonrpc' => self::VER,
            'id' => $id,
            'result' => $result
        ];
    }

}
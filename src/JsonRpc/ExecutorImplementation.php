<?php
/**
 * Created by PhpStorm.
 * User: MaD DucK
 * Date: 04.10.2018
 * Time: 13:50
 */

namespace App\JsonRpc;


class ExecutorImplementation implements Executor
{
    public function execute($method, $params)
    {
        switch ($method) {
            case "add":
                return $params[0] + $params[1];
                break;
            default:
                throw new \Exception("Method: \"" . $method . "\" is not implemented", 4000);
                break;
        }

    }

}
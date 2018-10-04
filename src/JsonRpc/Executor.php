<?php

namespace App\JsonRpc;


interface Executor
{
    public function execute($method, $params);
}
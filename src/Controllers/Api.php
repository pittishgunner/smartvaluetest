<?php

namespace App\Controllers;

use App\JsonRpc\Locations;
use App\JsonRpc\Server;

class Api extends Base
{
    public function aIndex()
    {

    }

    public function aLocations()
    {
        $p = $_POST;
        $server = new Server(new Locations($this->db));
        $respond = $server->respond(json_encode($p));
        header('Content-Type: application/json');
        echo $respond;
        exit;
    }
}
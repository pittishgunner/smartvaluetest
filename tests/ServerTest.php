<?php

namespace Tests;

use App\JsonRpc\ExecutorImplementation;
use App\JsonRpc\Server;
use PHPUnit\Framework\TestCase;

class ServerTest extends TestCase
{

    public function testInputEmpty()
    {
        $in = '';
        $out = '{"jsonrpc":"2.0","id":null,"error":{"code":1000,"message":"Input string is empty"}}';
        $this->compare($in, $out);
    }

    private function compare($input, $expectedOutput)
    {
        $server = new Server(new ExecutorImplementation());
        $actualOutput = $server->respond($input);

        $this->assertSame($expectedOutput, $actualOutput);
    }

    public function testInputNotString()
    {
        $in = 465465;
        $out = '{"jsonrpc":"2.0","id":null,"error":{"code":1100,"message":"Input is not a string"}}';
        $this->compare($in, $out);
    }

    public function testInputNotValidJson()
    {
        $in = '{"}';
        $out = '{"jsonrpc":"2.0","id":null,"error":{"code":1200,"message":"Input string is not a valid json string"}}';
        $this->compare($in, $out);
    }

    public function testInputEmptyJson()
    {
        $in = '{}';
        $out = '{"jsonrpc":"2.0","id":null,"error":{"code":1300,"message":"Input json is empty"}}';
        $this->compare($in, $out);
    }

    public function testMissingVersion()
    {
        $in = '{"id":null}';
        $out = '{"jsonrpc":"2.0","id":null,"error":{"code":1400,"message":"Server only accepts jsonrpc version 2.0"}}';
        $this->compare($in, $out);
    }

    public function testMissingMethod()
    {
        $in = '{"jsonrpc":"2.0"}';
        $out = '{"jsonrpc":"2.0","id":null,"error":{"code":1500,"message":"Method is missing"}}';
        $this->compare($in, $out);
    }

    public function testMethodNotString()
    {
        $in = '{"jsonrpc":"2.0","method":{"test":"me"}}';
        $out = '{"jsonrpc":"2.0","id":null,"error":{"code":1600,"message":"Method must be a string"}}';
        $this->compare($in, $out);
    }

    public function testMethodInternalNotAllowed()
    {
        $in = '{"jsonrpc":"2.0","method":"rpc-init"}';
        $out = '{"jsonrpc":"2.0","id":null,"error":{"code":1700,"message":"Internal method: rpc-init cannot be used"}}';
        $this->compare($in, $out);
    }

    public function testParamProvidedButNull()
    {
        $in = '{"jsonrpc":"2.0","method":"add","params":null}';
        $out = '{"jsonrpc":"2.0","id":null,"error":{"code":1800,"message":"Params cannot be null if provided"}}';
        $this->compare($in, $out);
    }

    public function testOneQueryMethodNotImplemented()
    {
        $in = '{"jsonrpc":"2.0","id":12,"method":"substract","params":[1,2]}';
        $out = '{"jsonrpc":"2.0","id":12,"error":{"code":4000,"message":"Method: \"substract\" is not implemented"}}';
        $this->compare($in, $out);
    }

    public function testOneQuery()
    {
        $in = '{"jsonrpc":"2.0","id":12,"method":"add","params":[1,2]}';
        $out = '{"jsonrpc":"2.0","id":12,"result":3}';
        $this->compare($in, $out);
    }

    public function testTwoQueries()
    {
        $in = '[{"jsonrpc":"2.0","id":11,"method":"add","params":[1,2]},{"jsonrpc":"2.0","id":12,"method":"add","params":[2,2]}]';
        $out = '[{"jsonrpc":"2.0","id":11,"result":3},{"jsonrpc":"2.0","id":12,"result":4}]';
        $this->compare($in, $out);
    }

    public function testOneNotification()
    {
        $in = '{"jsonrpc":"2.0","id":null,"method":"add","params":[1,2]}';
        $out = null;
        $this->compare($in, $out);
    }

    public function testTwoNotifications()
    {
        $in = '[{"jsonrpc":"2.0","id":null,"method":"add","params":[1,2]},{"jsonrpc":"2.0","id":null,"method":"add","params":[2,2]}]';
        $out = '[null,null]';
        $this->compare($in, $out);
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: MaD DucK
 * Date: 04.10.2018
 * Time: 14:31
 */

namespace Tests;

use App\Db\Db;
use PHPUnit\Framework\TestCase;

class DbTest extends TestCase
{

    public function testConnection()
    {
        $config = require __DIR__ . '/../config.php';
        $db = new Db($config['db'], true);
        $this->assertEquals($db->connected, true);
    }

    public function testFailureWithoutTable()
    {
        $config = require __DIR__ . '/../config.php';
        $db = new Db($config['db'], true);
        $db->getById(1);
        $this->assertEquals($db->error, "Please use \$db->t() to set your working table before running queries<br>" . "\n");
    }

    public function testFailureWhenDeletingAllRecords()
    {
        $config = require __DIR__ . '/../config.php';
        $db = new Db($config['db'], true);
        $db->t('bogus');
        $db->delete('');
        $this->assertEquals($db->error, "For security reasons you cannot delete all rows in a table without properly specifying a condition.You can use \$db->delete(\"1=1\") if you are certain you want to erase all records.");
    }

    public function testFailureWhenUpdateingAllRecords()
    {
        $config = require __DIR__ . '/../config.php';
        $db = new Db($config['db'], true);
        $db->t('bogus');
        $db->update(['id' => 1], '');
        $this->assertEquals($db->error, "For security reasons you cannot update all the rows in a table without properly specifying a condition.You can use \$db->update(\$data,\"1=1\") if you are certain you want to update all records.");
    }

    public function testQueryFailure()
    {
        $config = require __DIR__ . '/../config.php';
        $db = new Db($config['db'], true);
        $db->t("bogus")->query("SSELECTT");
        $isErrorFalse = ($db->error === false ? true : false);
        $this->assertEquals($isErrorFalse, false);
    }

    public function testRowByIdGeneration()
    {
        $config = require __DIR__ . '/../config.php';
        $db = new Db($config['db'], true);
        $db->t("bogus");
        $db->getById(890);
        $this->assertEquals($db->generatedSql, 'SELECT * FROM `bogus` WHERE `id`=890 LIMIT 0, 1');
    }
}

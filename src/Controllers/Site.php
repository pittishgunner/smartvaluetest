<?php

namespace App\Controllers;


use App\Core;
use SebastianBergmann\Timer\Timer;

class Site extends Base
{
    function __construct($params)
    {
        parent::__construct($params);
    }

    public function aIndex()
    {
        echo $this->render([
            'sample' => date("H:i:s")
        ]);
    }

    public function a404()
    {
        Core::httpError();
        echo $this->render();
    }

    public function a500()
    {
        echo $this->render();
    }

    public function aDbtest()
    {
        $this->title = 'DB Schema Timing Test';
        $db = $this->db;

        Timer::start();
        $db->t('locations_countries');
        $hits = [];
        $reads = $inserts = $updates = 0;
        $howMany = 100;
        /*reads*/
        for ($i = 1; $i <= $howMany; $i++) {
            $reads++;
            $randomCode = strtoupper(chr(rand(64, 90)) . chr(rand(64, 90)));
            $result = $db->getRow(['code' => $randomCode], false);
            if ($result !== false)
                $hits[] = '[' . $result->code . '] ' . $result->name;
        }
        $hits = array_unique($hits);
        asort($hits);
        $time = Timer::stop();

        /*inserts*/
        Timer::start();
        for ($i = 1; $i <= $howMany; $i++) {
            $inserts++;
            $insert_id = $db->insertOne([
                'id' => 250,
                'name' => rand(1, 99999),
                'code' => rand(1, 999),
                'prefix' => rand(1, 999)
            ]);
            if ($insert_id > 0) {
                $deleted = $db->delete(['id' => 250]);
            }
        }

        /*updates*/
        Timer::start();
        for ($i = 1; $i <= $howMany; $i++) {
            $updates++;
            $record = $db->getRow([
                'id' => rand(1, 200)
            ], false);
            if ($record) {
                $db->update(
                    ['name' => $record->name],
                    ['id' => $record->id]
                );
            }
        }

        /**/
        $time = Timer::stop();
        $summary = Timer::resourceUsage($time);


        echo $this->render([
            'db' => $db,
            'reads' => $reads,
            'hits' => $hits,
            'inserts' => $inserts,
            'updates' => $updates,
            'summary' => $summary,
        ]);
    }

}
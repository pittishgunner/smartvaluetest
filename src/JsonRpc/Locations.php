<?php
/**
 * Created by PhpStorm.
 * User: MaD DucK
 * Date: 04.10.2018
 * Time: 09:45
 */

namespace App\JsonRpc;


class Locations implements Executor
{
    private $_db;

    public function __construct($db)
    {
        $this->_db = $db;
    }

    public function execute($method, $params)
    {
        switch ($method) {
            case "getCountriesByCode":
                if (!isset($params['countryCode']) || (isset($params['countryCode']) && empty($params['countryCode'])))
                    throw new \Exception("Parameter: \"countryCode\" is required", 5000);
                $db = $this->_db->t('locations_countries');
                $location = $db->getRow(['code' => $params['countryCode']]);
                if (!$location)
                    return false;
                else return [
                    'name' => $location['name'],
                    'prefix' => $location['prefix'],
                ];
                break;
            default:
                throw new \Exception("Method: \"" . $method . "\" is not implemented", 4000);
                break;
        }

    }
}
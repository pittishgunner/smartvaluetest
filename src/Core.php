<?php

namespace App;

use App\Controllers\Site;
use App\Db\Db;

class Core
{

    public $controller = 'Site';
    public $action = '404';
    public $params = [];
    public $db = null;
    public $config;

    public function __construct($config, $db = null)
    {
        if ($db !== null) {
            if (!isset($config[$db])) {
                Core::errorOut(500, 'Database configuration was not found');
            }
            $this->db = Db::instantiate($config[$db]);
            if ($this->db->error!==false) {
                Core::errorOut(500,$this->db->error);
            }
        }
        $this->config = $config;
        $params = $_REQUEST;
        $urlParts = [];
        if (isset($_GET['ur1']) && !empty($_GET['ur1'])) {
            unset($params['ur1']);
            $url = strtolower(rtrim($_GET['ur1'], '/'));
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $urlParts = explode('/', $url);
            if (count($urlParts) == 1 || count($urlParts) == 2) {
                if (file_exists(__DIR__ . '/Controllers/' . ucfirst($urlParts[0]) . '.php')) {
                    $this->controller = ucfirst($urlParts[0]);
                }
            }
        } else $this->action = 'index';


        if (count($urlParts) == 2) {
            $this->action = $urlParts[1];
        } elseif (count($urlParts) == 1) {
            $this->action = 'index';
        }
        $this->params = $params;
        if ($this->action == '404') {
            $this->controller = 'Site';
        }
        $className = 'App\\Controllers\\' . $this->controller;
        $classInst = new $className([
            'urlParams' => $this->params,
            'controller' => $this->controller,
            'action' => $this->action,
            'db' => $this->db
        ]);
        if (method_exists($classInst,'a' . ucfirst($this->action))) {
            call_user_func(
                [$classInst, 'a' . ucfirst($this->action)]
            );
        } else {
            $this->controller="Site";
            $this->action="404";
            $className = 'App\\Controllers\\' . $this->controller;
            $classInst = new $className([
                'urlParams' => $this->params,
                'controller' => $this->controller,
                'action' => $this->action,
                'db' => $this->db
            ]);
            call_user_func(
                [$classInst, 'a' . ucfirst($this->action)]
            );
        }

    }

    public static function errorOut($code = 200, $message = '')
    {
        if ($code != 200) {
            Core::httpError($code, $message);
        }
        $Site = new Site(['urlParams' => [], 'controller' => 'Site', 'action' => '500', 'db' => null]);
        echo $Site->render(['message' => $message]);
        exit;
    }

    public static function httpError($code = 404, $message = 'The requested page was not found')
    {
        $message = str_replace(["\n", "\r"], '', $message);
        header((isset($_SERVER['SERVER_PROTOCOL'])?$_SERVER['SERVER_PROTOCOL']:'HTTP/1.0') . ' ' . $code . ' ' . $message, true, 500);
    }

    public static function p($ob, $return = false)
    {
        $s = '<pre>';
        $s .= ($ob === null ? '===null' : print_r($ob, true));
        $s .= '</pre>';
        if ($return)
            return $s;
        else
            echo $s;
    }

}
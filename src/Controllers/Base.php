<?php

namespace App\Controllers;


class Base
{
    public $urlParams = [];
    public $controller = '';
    public $action = '';
    public $db = null;
    public $layout = 'main';
    public $title = 'SmartValue Test - Alex M.';

    function __construct($params)
    {
        $this->urlParams = $params['urlParams'];
        $this->controller = $params['controller'];
        $this->action = $params['action'];
        $this->db = $params['db'];
    }

    public function render($data = [])
    {
        $viewContent = '';
        if ($this->controller && $this->action) {
            $viewFile = __DIR__ . '/../Views/' . $this->controller . '/' . $this->action . '.php';
            if (file_exists($viewFile)) {
                $viewContent = $this->renderInternal($viewFile, $data);
            }
        }
        $mainContent = $this->renderInternal(__DIR__ . '/../Views/Layouts/' . $this->layout . '.php', $data);
        $mainContent = str_replace(['[[CONTENT]]'], [$viewContent], $mainContent);
        return $mainContent;
    }

    protected function renderInternal($_viewFile_, $_data_ = null)
    {
        if (is_array($_data_))
            extract($_data_, EXTR_PREFIX_SAME, 'data');
        else
            $data = $_data_;
        ob_start();
        ob_implicit_flush(false);
        require($_viewFile_);
        return ob_get_clean();
    }

}
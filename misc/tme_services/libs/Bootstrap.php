<?php

class Bootstrap {
    function __construct() {
		Session::init();
        $url = isset($_GET['url']) ? $_GET['url'] : null;
        $url = rtrim($url, '/');
        $url = explode('/', $url);
        
        if (empty($url[0])) {
            require 'controllers/index.php';
            $controller = new Index();
            //Session::init();
            //Session::destroy();
            if(Session::get('tokens')	!=	'') {
				$controller->index('0');
			} else {
				$controller->index('1');
			}
            return false;
        }

        $file = 'controllers/' . trim($url[0]) . '.php';
		
        if (file_exists($file)) {
            require $file;
        } else {
           // $this->error();
        }
		
        $controller = new $url[0];
        
        $controller->loadModel($url[0]);
		
        // calling methods
        
        if (isset($url[3])) {
            if (method_exists($controller, $url[1])) {
                $controller->{$url[1]}($url[2], $url[3]);
            } else {
              //  $this->error();
            }
        } else if (isset($url[2])) {
            if (method_exists($controller, $url[1])) {
                $controller->{$url[1]}($url[2]);
            } else {
               // $this->error();
            }
        } else {
            if (isset($url[1])) {
                if (method_exists($controller, $url[1])) {
                    $controller->{$url[1]}();
                } else {
					//$this->error();
                    $controller->index($url[1]);
                }
            } else {
                $controller->index();
            }
        }
    }

    function error() {
        require 'controllers/error.php';
        $controller = new Error();
        $controller->index();
        return false;
    }

}

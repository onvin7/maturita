<?php

class Router {
    public function handleRequest() {
        $controller = $_GET['controller'] ?? 'Home'; // Výchozí: veřejná část
        $action = $_GET['action'] ?? 'index';
    
        $controllerName = ucfirst($controller) . 'Controller';
        $controllerFile = "../app/controllers/$controllerName.php";
    
        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            $controllerInstance = new $controllerName();
    
            if (method_exists($controllerInstance, $action)) {
                $controllerInstance->$action();
            } else {
                echo "Action '$action' not found.";
            }
        } else {
            echo "Controller '$controllerName' not found.";
        }
    }
    
}

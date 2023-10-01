<?php

namespace contentfreigabe\backend\Controller;

use contentfreigabe\backend\Controller\FrontControllerInterface;
use contentfreigabe\backend\Library\NotFoundException;
use contentfreigabe\backend\Application;
use ReflectionClass;


class FrontController implements FrontControllerInterface{

    protected $controller;
    protected $action;                    
    protected $permission;

    public function __construct($options = array()){
        if(empty($options)){
            $this->parseUri();
        } else {
            
            if (isset($options["controller"])) {
                $this->setController($options["controller"]);
            }
            if (isset($options["action"])) {
                $this->setAction($options["action"]);     
            }
        }
    }

    public function parseUri(){
        $url = (isset($_GET['_url']) ? $_GET['_url'] : '');
        //var_dump($_GET['_url']);
        $urlparts = explode('/', $url);

        $controllerName = (isset($urlparts[0]) && $urlparts[0] ? $urlparts[0] : 'index');
        $actionName = (isset($urlparts[1]) && $urlparts[1] ? $urlparts[1] : 'index');


        if(isset($controllerName)){
            $this->setController($controllerName);
        }

        if(isset($actionName) && isset($controllerName)){
            $this->setAction($actionName);
           
        }
    }

    public function setController($controller) {
        
        $this->controller = $controller;

    }

    public function setAction($action) {    
        $actionMethodName = $action. "Action";
    
        $controllerClassName = '\\contentfreigabe\backend\\Controller\\'.ucfirst(strtolower($this->controller))."Controller";
        $reflector = new ReflectionClass($controllerClassName);
        if (!$reflector->hasMethod($actionMethodName)) {
            throw new NotFoundException(
                "The controller action '$action' has been not defined in '$controllerClassName'");
        }
        $this->action = $actionMethodName;
    }

   

    public function run(){
    try{
            $controllerName = ucfirst($this->controller.'Controller');
            $controller = Application::getController($controllerName);
            $actionMethod = $this->action;
            $controller->$actionMethod();
            //echo ini_get('post_max_size');
       
            
    }
        catch (NotFoundException $e) {
        http_response_code(404);
        echo 'Page not found: ' . $controllerName. '::' . $this->action;
    }   catch (\Exception $e) {
        http_response_code(500);
        echo 'Exception: ' . $e->getMessage() . ' ' . $e->getTraceAsString();
    }
    }
}
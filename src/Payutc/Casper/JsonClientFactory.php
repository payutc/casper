<?php

namespace Payutc\Casper;

class UnknownClientException extends \Exception {}

class JsonClientFactory {
    protected $clients;
    protected static $instance;
    
    // Load clients from session if any
    public function __construct(){
        if(isset($_SESSION["casper_clients"])){
            $this->clients = $_SESSION["casper_clients"];
        }
        else {
            $this->clients = array();
        }
    }
    
    // Create a client for a specific $service if it doesn't exist
    public function createClient($service){
        if(!isset($this->clients[$service])) {
            $this->clients[$service] = new \JsonClient\AutoJsonClient(Config::get("server_url"), $service);
        }
    }
    
    // Get a client for a specific $service
    public function getClient($service){
        if(!isset($this->clients[$service])){
            throw new UnknownClientException();
        }
        
        if(isset($_SESSION["casper_json_client_cookie"])){
            $this->clients[$service]->cookie = $_SESSION["casper_json_client_cookie"];
        }
        
        return $this->clients[$service];
    }
    
    // Set the common cookie
    public function setCookie($cookie){
        $_SESSION["casper_json_client_cookie"] = $cookie;
    }
    
    // Get the common cookie
    public function getCookie(){
        if(isset($_SESSION["casper_json_client_cookie"])){
            return $_SESSION["casper_json_client_cookie"];
        }
        
        return false;
    }
    
    // Get the common cookie
    public function destroyCookie(){
        if(isset($_SESSION["casper_json_client_cookie"])){
            unset($_SESSION["casper_json_client_cookie"]);
        }
        
        return false;
    }
    
    // Get an instance of this class
    public static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new JsonClientFactory();
        }
        
        return self::$instance;
    }
}
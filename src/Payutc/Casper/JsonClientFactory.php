<?php

namespace Payutc\Casper;

class UnknownClientException extends \Exception {}

class JsonClientFactory {
    protected $clients;
    protected static $instance;
    
    // Load clients from session if any
    public function __construct(){
        $this->clients = array();
        if(isset($_SESSION["casper_json_client_cookie"])) {
            $this->cookie = $_SESSION["casper_json_client_cookie"];
        }
        else {
            $this->cookie = array();
        }
    }
    
    // Create a client for a specific $service if it doesn't exist
    public function createClient($service){
        if(!isset($this->clients[$service])) {
            $this->clients[$service] = new \JsonClient\AutoJsonClient(Config::get("server_url"), $service);
            $this->clients[$service]->cookie = $this->cookie;
        }
    }
    
    // Get a client for a specific $service
    public function getClient($service){
        if(!isset($this->clients[$service])){
            throw new UnknownClientException("Unknown client $service");
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

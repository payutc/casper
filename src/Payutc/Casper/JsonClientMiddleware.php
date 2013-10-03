<?php
namespace Payutc\Casper;

use \Payutc\Casper\Config;
use \Payutc\Casper\JsonClientFactory;

class JsonClientMiddleware extends \Slim\Middleware
{
    protected $services = array(
        "RELOAD",
        "MYACCOUNT",
        "TRANSFER"
    );
    
    public function call()
    {   
        // Get reference to application
        $app = $this->app;
        
        // If we have no cookie, redirect to login
        if($app->request()->getResourceUri() != '/login' && !JsonClientFactory::getInstance()->getCookie()) {
            $app->getLog()->debug("No cookie, redirecting to login");
            $app->response()->redirect($app->urlFor('login'));
        }
        
        // Create the client for each service (if it does not exist)
        foreach($this->services as $service){
            $app->getLog()->debug("Creating json_client for service $service");
            JsonClientFactory::getInstance()->createClient($service);
        }
        
        // Get user and app status
        $status = JsonClientFactory::getInstance()->getClient("MYACCOUNT")->getStatus();

        // Connect the application
        if(empty($status->application)){
            $app->getLog()->debug("No app logged in, calling loginApp");
            // Connexion de l'application
            try {
                JsonClientFactory::getInstance()->getClient("MYACCOUNT")->loginApp(array(
                    "key" => Config::get("application_key")
                ));
            } catch (\JsonClient\JsonException $e) {
                $app->getLog()->error("Application login error: ".$e->getMessage());
                throw $e;
            }
        }
    
        // If no user loaded, go to cas
        if($app->request()->getResourceUri() != '/login' && $app->request()->getResourceUri() != '/register' && empty($status->user)){
            $app->getLog()->debug("No user logged in, redirect to login route");
            $app->response()->redirect($app->urlFor('login'));
    	}
    
        // Save user data in environment
        $env = $app->environment();
        $env["user_data"] = $status->user_data;
        
        // Run inner middleware and application
        $this->next->call();
    }
}
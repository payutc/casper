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
        // Get reference to application and environment
        $app = $this->app;
        $env = $app->environment();
        
        // If we are in websale gateway
        if (strpos($app->request()->getPathInfo(), '/validation') === 0) {
            // Consider us as logged in if we already have a cookie
            $env["loggedin"] = JsonClientFactory::getInstance()->getCookie() ? true : false;
    
            // Create the client for WEBSALECONFIRM
            $app->getLog()->debug("Creating json_client for service WEBSALECONFIRM");
            JsonClientFactory::getInstance()->createClient("WEBSALECONFIRM");
    
            // Get user and app status
            $status = JsonClientFactory::getInstance()->getClient("WEBSALECONFIRM")->getStatus();

            // Connect the application if required
            if(empty($status->application)){
                $app->getLog()->debug("No app logged in for WEBSALECONFIRM, calling loginApp");
        
                try {
                    JsonClientFactory::getInstance()->getClient("WEBSALECONFIRM")->loginApp(array(
                        "key" => Config::get("application_key")
                    ));
                } catch (\JsonClient\JsonException $e) {
                    $app->getLog()->error("Application login error for WEBSALECONFIRM: ".$e->getMessage());
                    throw $e;
                }
            }

            // If no user loaded, consider our cookie us as not logged in
            if(empty($status->user)){
                $env["loggedin"] = false;
        	}
        }
        
        // If not in websale or in websale with a logged in user
        if ($app->request()->getResourceUri() != '/cgu' && (strpos($app->request()->getPathInfo(), '/validation') !== 0 || $env["loggedin"])) {
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
            $env["user_data"] = $status->user_data;
        }
        
        try {
            // Run inner middleware and application
            $this->next->call();
        }
        catch(\JsonClient\JsonException $e){
            if($app->request()->getResourceUri() != '/login' && $e->getType() == "Payutc\Exception\CheckRightException"){
                $app->getLog()->debug("Caught CheckRightException (".$e->getMessage()."), redirect to login route");
                $app->response()->redirect($app->urlFor('login'));
            }
        }
    }
}

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
        if(!JsonClientFactory::getInstance()->getCookie()) {
            if($app->request()->getResourceUri() != '/login') {
                $app->getLog()->debug("No cookie, redirecting to login");
                $app->redirect($app->urlFor('login'));
            }
        }
        
        // Create the client for each service (if it does not exist)
        foreach($this->services as $service){
            $app->getLog()->debug("Creating json_client for service $service");
            JsonClientFactory::getInstance()->createClient($service);
        }
        
        // Run inner middleware and application
        $this->next->call();
    }
}
<?php
namespace Payutc;

class SoapCookies extends \Slim\Middleware
{
    public function call()
    {
        global $MADMIN;
        
        // Get reference to application
        $app = $this->app;

        // L'utilisateur est déjà connecté avec un cookie
        if(isset($_SESSION['cookies'])) {
            // On charge la session soap
        	$MADMIN->_cookies = $_SESSION['cookies'];
        } elseif($app->request()->getResourceUri() != '/login') {
        	$app->redirect($app->urlFor('login'));
        }
        
        // Run inner middleware and application
        $this->next->call();
    }
}
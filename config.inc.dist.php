<?php

// Nom de l'instance de payutc (pour affichage)
$_CONFIG["title"] = "payutc";

// URL de casper (avec le / final)
$_CONFIG["casper_url"] = "http://localhost/casper/";

// URL du serveur payutc (avec le / final)
$_CONFIG["server_url"] = "http://localhost/payutc/web/";

// Clé de l'application
$_CONFIG["application_key"] = "";

// Configuration de Slim
$_CONFIG['slim_config'] = array(
    'mode' => 'developement',
    'debug' => true,
    'log.level' => \Slim\Log::DEBUG,
    'log.enabled' => true,
    'log.writer' => new \Slim\Extras\Log\DateTimeFileWriter(array(
        'path' => __DIR__.'/logs',
        'name_format' => 'Y-m-d',
        'message_format' => '%label% - %date% - %message%'
    ))
);

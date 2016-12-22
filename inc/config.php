<?php

    /* Exibir erros */ 
    ini_set('display_errors', 1); 
    ini_set('display_startup_errors', 1); 
    error_reporting(E_ALL);     

	/* Endereço do server, sem http:// */ 
	$server = 'localhost'; 
	$hosts = [
		'200.144.183.86' 
	]; 

	/* Load libraries for PHP composer */ 
    require (__DIR__.'/../vendor/autoload.php'); 

	/* Load Elasticsearch Client */ 
	$client = \Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build(); 

?>
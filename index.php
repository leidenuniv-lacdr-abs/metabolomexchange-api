<?php
/**
 * Copyright 2015 Michael van Vliet (Leiden University), Thomas Hankemeier 
 * (Leiden University)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 * 		http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/

header("Access-Control-Allow-Origin: *"); // required for all clients to connect

// add some logic
require_once 'src/flight/Flight.php';

try {

	// config
	Flight::set('defaultApiVersion', '1');
	Flight::set('providers', array(
		"http://feeds.metabolomexchange.org/golm.php",
		"http://feeds.metabolomexchange.org/meryb.php",
		"http://feeds.metabolomexchange.org/metabolomics-workbench.php",
		"http://feeds.metabolomexchange.org/metabolights.php"
	));

	// homepage with basic how to
	Flight::route('GET /', function(){ Flight::render('home.php', array()); });
	
	// define API routes based on version
	$apiVersion = Flight::get('defaultApiVersion');

	// see if we overwrite the default version of the api
	if (isset(Flight::request()->query->version)){ 
		$apiVersion = Flight::request()->query->version; 
	}

	// determine the location of the api methods to expose
	$apiVersionRoutes = 'version/'.$apiVersion.'/routes.php';
	if (file_exists($apiVersionRoutes)){ 
		require_once('version/'.$apiVersion.'/routes.php');
	} else {
		throw new Exception('Unknown api version ('. $apiVersion .')');
	}

} catch (Exception $e) {
	Flight::set('error_message', $e->getMessage());
}	

// ERROR fallback
Flight::route('*', function(){ 
	Flight::json(
		array(
			'error'=>'page not found!', 
			'stacktrace' => (Flight::get('error_message')) ? Flight::get('error_message') : '' )
		);	
	exit(); 
});

// lift off
Flight::start();
?>

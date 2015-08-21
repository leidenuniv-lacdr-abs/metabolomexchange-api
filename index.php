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
		"golm" => "http://feeds.metabolomexchange.org/golm.php",
		"meryb" => "http://feeds.metabolomexchange.org/meryb.php",
		"mwbs" => "http://feeds.metabolomexchange.org/metabolomics-workbench.php",
		"mtbls" => "http://feeds.metabolomexchange.org/metabolights.php"
	));
	
	// define API routes based on version
	Flight::set('apiVersion', Flight::get('defaultApiVersion'));

	// see if we overwrite the default version of the api
	if (isset(Flight::request()->query->version)){ 
		Flight::set('apiVersion', Flight::request()->query->version);
	}

	// determine the location of the api methods to expose
	Flight::set('apiVersionRoot', 'version/'. Flight::get('apiVersion') .'/');

	// set cache folder
	Flight::set('useCache', false);
	Flight::set('apiVersionCacheRoot', Flight::get('apiVersionRoot') .'/.cache/');
	if (!is_dir(Flight::get('apiVersionCacheRoot'))){
		try {
			mkdir(Flight::get('apiVersionCacheRoot'), 0777, true);
			Flight::set('useCache', true);
		} catch (Exception $e){
			echo $e;
			exit();
			Flight::set('useCache', false);
		}
	}

	// try to load the correct logic for the version of the api
	Flight::set('apiVersionRoutes', Flight::get('apiVersionRoot') . 'api.php');
	if (file_exists(Flight::get('apiVersionRoutes'))){ 
		require_once(Flight::get('apiVersionRoutes'));
	} else {
		throw new Exception('Unknown api route(s) ('. Flight::get('apiVersionRoutes') .')');
	}


	// homepage with basic how to
	Flight::route('GET /', function(){ 

		$versions = array();
		$versionsRoot = 'version';
		$versionDirectories = scandir($versionsRoot);
		foreach ($versionDirectories as $vdIdx => $versionDirectory){
			$version = $versionsRoot . DIRECTORY_SEPARATOR . $versionDirectory;
			$readmeFile = $version . DIRECTORY_SEPARATOR . 'README';
			if (is_dir($version) && is_file($readmeFile)){
				$readmeText = file_get_contents($readmeFile);
				$versions[] = array('version'=>$versionDirectory, 'readme'=>$readmeText);

				if (Flight::get('apiVersion') == $versionDirectory){
					$readme = $readmeText;
				}
			}
		}

		Flight::render('home.php', array('versions'=>$versions, 'readme'=>$readme, 'defaultApiVersion'=>Flight::get('defaultApiVersion') , 'apiVersion'=>Flight::get('apiVersion'))); 
	});

	// implementation of (required) routes
	Flight::route('GET /providers', array('Api','providers'));
	Flight::route('GET /provider/@shortname', array('Api','provider'));
	
	Flight::route('GET /datasets', array('Api','datasets'));
	Flight::route('GET /dataset/@shortname/@accession', array('Api','dataset'));	
	Flight::route('GET /datasets/@search', array('Api','search'));
	
	Flight::route('GET /stats', array('Api','stats'));


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

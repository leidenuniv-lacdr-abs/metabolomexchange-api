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

require_once('apiHelper.php');

Flight::route('GET /api/providers', function(){

	$providers = array(); // init response
	foreach (Helper::providers(Flight::get('providersJson')) as $providerIdx => $provider){
		unset($provider['datasets']); // remove datasets
		$providers[] = $provider;
	}

	Flight::json($providers);
});

Flight::route('GET /api/provider/@shortname', function($shortname){
	
	$provider = array();

	foreach (Helper::providers(Flight::get('providersJson')) as $providerIdx => $p){
		if ($p['shortname'] == $shortname){
			$provider = $p;
			break; // found the correct provider!
		}
	}

	Flight::json($provider);
});

// GET::dataset (one by provider shortname and dataset accession)
Flight::route('GET /api/provider/@shortname/@accession', function($shortname, $accession){

	$dataset = array();

	foreach (Helper::datasets(Flight::get('providersJson')) as $dIdx => $d){
		if ( // find the matching dataset
			((string) $d['provider'] == (string) $shortname) && 
			((string) $d['accession'] == (string) $accession) 
		){
			$dataset = $d;
			break; // found the correct dataset!
		}
	}
	
	Flight::json($dataset);
});

// GET all datasets
Flight::route('GET /api/datasets', function(){	
	Flight::json(Helper::datasets(Flight::get('providersJson')));
});

// GET find dataset matching $query
Flight::route('GET /api/datasets/@search', function($search){	

	$datasets = array();
	$needle = strtolower($search);

	foreach(Helper::datasets(Flight::get('providersJson')) as $dIdx => $d){
		$haystack = strtolower('_'.json_encode($d));
		if (stripos($haystack, $needle) >= 1) {
			$datasets[] = $d;
		}
	}
	
	Flight::json($datasets);
});

?>
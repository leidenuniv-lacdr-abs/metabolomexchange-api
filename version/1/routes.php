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

Flight::route('GET /providers', function(){
	$providers = array();
	foreach (Helper::providers(Flight::get('providers')) as $pIdx => $p){
		$providers[] = $p;
	}
	Flight::json($providers);
});

Flight::route('GET /provider/@shortname', function($shortname){
	
	$providers = Flight::get('providers');
	
	$provider = Helper::fetchFeed($providers[$shortname]);
	$provider['shortname'] = $shortname;

	Flight::json($provider);
});

// GET::dataset (one by provider shortname and dataset accession)
Flight::route('GET /provider/@shortname/@accession', function($shortname, $accession){

	$dataset = array();

	$providers = Flight::get('providers');
	$provider = Helper::fetchFeed($providers[$shortname]);
	foreach ($provider['datasets'] as $dIdx => $dataset){
		if ((string) $dataset['accession'] == (string) $accession){
			$dataset['provider'] = $shortname;
			break; // found the correct dataset!
		} else {
			$dataset = array();
		}
	}
	
	Flight::json($dataset);
});

// GET all datasets
Flight::route('GET /datasets', function(){	
	Flight::json(Helper::datasets(Flight::get('providers')));
});

/**
 * GET find dataset matching $search
 *
 * $search supports the use of 'and' & 'or' syntax, including combinations
 * 
 * /datasets/mutation&Katayonn > all datasets that match both 'mutation' and 'Katayonn'
 * /datasets/mutation Katayonn > all datasets that match 'mutation' and/or 'Katayonn'
 * /datasets/mutation|Katayonn > all datasets that match 'mutation' and/or 'Katayonn' 
 * 
 **/
Flight::route('GET /datasets/@search', function($search){	

	$datasets = array();
	$datasetTimestamps = array(); // used for sorting in the end
	$needle = strtolower($search);
	$orNeedles = array();

	if (strpos($needle, '|') >= 1){ // search for multiple words or combinations
		$orNeedles = explode('|', $needle);
	} elseif (strpos($needle, ' ') >= 1){ // search for multiple words or combinations
		$orNeedles = explode(' ', $needle);		
	} else { // search for a single keyword
		$orNeedles[] = $needle;
	}

	foreach(Helper::datasets(Flight::get('providers')) as $dIdx => $d){

		$haystack = strtolower('_'.json_encode($d));
		foreach ($orNeedles as $needleIdx => $orNeedle){

			// determine if there are & statements
			if (strpos($orNeedle, '&') >= 1){
				$andNeedles = explode('&', $needle);
				$match = 0;
				foreach ($andNeedles as $needleIdx => $andNeedle){				
					if (stripos($haystack, $andNeedle) >= 1) {
						$match++;
					}
				}
				if ($match == count($andNeedles)){
					$datasets[] = $d;
				}
			} else { // no 'and' statements
				if (stripos($haystack, $orNeedle) >= 1) {
					$datasets[] = $d;
				}
			}

		}
	}

	if (!empty($datasets)){ // make sure all are unique
		$uniqueDatasets = array();
		foreach ($datasets as $dIdx => $d){
			if (!in_array($d, $uniqueDatasets)){
				$datasetTimestamps[$dIdx]  = $d['timestamp']; // collect timestamps of unique datasets
				$uniqueDatasets[] = $d;
			}
		}

		$datasets = $uniqueDatasets;
		array_multisort($datasetTimestamps, SORT_DESC, $datasets); // sort by timestamp desc
	}

	Flight::json($datasets);
});

?>
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


class Api {

    /**
     * @ApiDescription(section="general", description="Retrieve stats about the publicly available datasets.")
     * @ApiMethod(type="get")
     * @ApiRoute(name="/stats")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{}")
     */	
	public static function stats() {
		Flight::json(Helper::stats(Flight::get('providers')));
    }        
	

    /**
     * @ApiDescription(section="provider", description="Retrieve all providers sharing publicly available datasets.")
     * @ApiMethod(type="get")
     * @ApiRoute(name="/providers")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{}")
     */	
	public static function providers() {
		$providers = array();
		foreach (Helper::providers(Flight::get('providers')) as $pIdx => $p){
			$providers[] = $p;
		}
		
		return Flight::json($providers);
    }

    /**
     * @ApiDescription(section="provider", description="Get all info about a single provider, including a list of the datasets of that provider.")
     * @ApiMethod(type="get")
     * @ApiRoute(name="/provider/{shortname}")
     * @ApiParams(name="shortname", type="string", nullable=false, description="Provider shortname", sample="mtbls")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{}")
     */	
	public static function provider($shortname) {
		
		$providers	= Flight::get('providers');
		$provider	= Helper::fetchFeed($providers[$shortname]);
		
		$provider['shortname'] = $shortname;

		return Flight::json($provider);
    } 

    /**
     * @ApiDescription(section="dataset", description="Get all info about a single dataset.")
     * @ApiMethod(type="get")
     * @ApiRoute(name="/dataset/{shortname}/{accession}")
     * @ApiParams(name="shortname", type="string", nullable=false, description="Provider shortname", sample="mtbls")
     * @ApiParams(name="accession", type="string", nullable=false, description="Unique identifier of dataset provided by the provider", sample="MTBLS105")     
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{}")
     */	
	public static function dataset($shortname, $accession) {
		
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
    } 

    /**
     * @ApiDescription(section="dataset", description="Retrieve all publicly available datasets by all providers.")
     * @ApiMethod(type="get")
     * @ApiRoute(name="/datasets")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{}")
     */	
	public static function datasets($shortname, $accession) {
		Flight::json(Helper::datasets(Flight::get('providers')));
    }        

    /**
     * @ApiDescription(section="dataset", description="Find datasets matching $search")
     * @ApiMethod(type="get")
     * @ApiRoute(name="/datasets/{search}")
     * @ApiParams(name="search", type="string", nullable=false, description="
	 * $search supports the use of 'and' & 'or' syntax, including combinations
	 * 
	 * /datasets/mutation&Katayonn > all datasets that match both 'mutation' and 'Katayonn'.
	 * /datasets/mutation Katayonn > all datasets that match 'mutation' and/or 'Katayonn'.
	 * /datasets/mutation|Katayonn > all datasets that match 'mutation' and/or 'Katayonn'.    
     * ")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{}")
     */	
	public static function search($search) {

		$datasets = array();
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
					$uniqueDatasets[] = $d;
				}
			}

			$datasets = $uniqueDatasets;
		}

		Flight::json($datasets);
    } 

}

?>
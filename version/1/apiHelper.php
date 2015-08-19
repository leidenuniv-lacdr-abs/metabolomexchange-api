<?php

class Helper {

	static public function fetchFeed($feed = null){
		return json_decode(file_get_contents($feed), true);
	}

	static public function providers($providers = array()){
		
		// init response		
		$response = array(); // init response

		foreach ($providers as $pIdx => $p){
			$feed = Helper::fetchFeed($p);
			unset($feed['datasets']);
			$feed['shortname'] = $pIdx;
			$response[] = $feed;
		}

		return $response;
	}

	static public function datasets($providers = array()){
		
		// init response		
		$response = array(); // init response

		$datasets = array();
		foreach ($providers as $pIdx => $p){
			$feed = Helper::fetchFeed($p);
			foreach ($feed['datasets'] as $dIdx => $dataset){
				$dataset['provider'] = $pIdx; // add provider id to dataset
				$datasets[] = $dataset; // add dataset to datasets
			}
		}

		// sort dataset by date / provider
		$timestampIdx = array();
		$providerIdx = array(); 
		foreach ($datasets as $dIdx => $dataset){
			// keep track on sorting info
			$timestampIdx[$dIdx]  = $dataset['timestamp'];
			$providerIdx[$dIdx]  = $dataset['provider'];
		}
		
		array_multisort($timestampIdx, SORT_DESC, $providerIdx, SORT_ASC, $datasets);

		$response = $datasets;

		return $response;
	}	

	static public function stats($providers = array()){

		// init response		
		$stats = array();

		// provider details
	    $ps = Helper::providers($providers);
	    $providerDetails = array();
	    foreach ($ps as $pIdx => $provider){
	        $providerDetails[$provider['shortname']] = $provider;
	    }

		// fetch (sorted) datasets
		$datasets = Helper::datasets($providers);

		// setup basic numbers
		$stats['providers'] = count($providers);
		$stats['datasets'] = count($datasets);

		// temp data
		$d = array();
		$lowestYear = (int) 9999;
		$currentYear = (int) date("Y");
		$currentMonth = (int) date("m");

		foreach (array_reverse($datasets) as $dIdx => $dataset){
			
			$providerName = $providerDetails[$dataset['provider']]['name'];
			$year = (int) date("Y", $dataset['timestamp']);			
			$month = (int) date("m", $dataset['timestamp']);

			// update helper veriables
			if ($lowestYear > $year){ $lowestYear = $year; }

			if (!isset($d[$providerName])){ $d[$providerName] = array(); }
			if (!isset($d[$providerName][$year])){ $d[$providerName][$year] = array(); }
			if (!isset($d[$providerName][$year][$month])){ $d[$providerName][$year][$month] = 0; }

			$d[$providerName][$year][$month]++;
		}

		// build data
		$data = array();
		$years = range($lowestYear,$currentYear);
		$months = range(1,12);
		$providerDatasetCounter = array();
		$providerHistory = array();
		foreach ($providerDetails as $shortname => $provider){

			$providerHistoryData = array();

			if (!isset($providerDatasetCounter[$shortname])) { $providerDatasetCounter[$shortname] = 0; }

			foreach ($years as $yIdx => $year){
				foreach ($months as $mIdx => $month){
					$skip = false;
					if ($year == (int) date("Y") && $month > (int) date("m")){
						$skip = true;
					}

					if (!$skip){
						$increase = isset($d[$provider['name']][$year][$month]) ? $d[$provider['name']][$year][$month] : 0;

						$providerDatasetCounter[$shortname] += $increase;

						if ($year > 2011){ // hide data in graphs before 2011
							$providerHistoryData[] = array(
								'x'=>mktime(0,0,0,$month,1,$year),//$year . "-" . $month, 
								'y'=>$providerDatasetCounter[$shortname],
								'y0'=>$providerDatasetCounter[$shortname],
								'series'=>$shortname,
								'size'=>$providerDatasetCounter[$shortname],
								'y1'=>$providerDatasetCounter[$shortname],
							);

							$data[] = array(
								'provider'=>$provider['name'],
								'ym'=>$year . "-" . $month,
								'y'=>$year,
								'm'=>$month,
								'total'=>$providerDatasetCounter[$shortname],
								'increase'=>$increase
							);
						}
					}
				}
			}

			$providerHistory[] = array("key"=>$provider['name'], "values"=>$providerHistoryData);
		}

		$stats['data'] = $data;
		$stats['providerDatasets'] = $providerDatasetCounter;
		$stats['providerHistory'] = $providerHistory;

		return $stats;
	}

}

?>
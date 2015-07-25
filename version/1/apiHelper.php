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

		foreach ($providers as $pIdx => $p){
			$feed = Helper::fetchFeed($p);
			foreach ($feed['datasets'] as $dIdx => $dataset){
				$dataset['provider'] = $pIdx;
				$response[] = $dataset;
			}
		}

		return $response;
	}	

}

?>
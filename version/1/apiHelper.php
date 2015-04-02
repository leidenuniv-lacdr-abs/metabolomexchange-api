<?php

class Helper {

	static public function fetchFeed($feed = null){
		return json_decode(file_get_contents($feed), true);
	}

	static public function providers($providerJson){
			// init response
		$providers = array();

		foreach (Helper::fetchFeed($providerJson) as $providerUrlIdx => $providerUrl){
			$providers[] = Helper::fetchFeed($providerUrl);
		}

		return $providers;
	}

	static public function datasets($providerJson){
			// init response
		$datasets = array();

		foreach (Helper::providers($providerJson) as $providerIdx => $provider){
			foreach ($provider['datasets'] as $dIdx => $dataset){
				$dataset['provider'] = $provider['shortname'];			
				$datasets[] = $dataset;
			}
		}

		return $datasets;
	}	

}

?>
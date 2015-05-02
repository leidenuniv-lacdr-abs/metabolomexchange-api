<?php

class Helper {

	static public function fetchFeed($feed = null){
		return json_decode(file_get_contents($feed), true);
	}

	static public function providers($arrProviders = array()){
		
		$providers = array(); // init response

		foreach ($arrProviders as $pIdx => $p){
			$providers[] = Helper::fetchFeed($p);
		}

		return $providers;
	}

	static public function datasets($providers){
			// init response
		$datasets = array();

		foreach ($providers as $providerIdx => $provider){
			foreach ($provider['datasets'] as $dIdx => $dataset){
				$dataset['provider'] = $provider['shortname'];			
				$datasets[] = $dataset;
			}
		}

		return $datasets;
	}	

}

?>
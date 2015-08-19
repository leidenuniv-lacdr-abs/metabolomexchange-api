<h1>MetabolomeXchange API</h1>

<?php
	if (isset($versions) && is_array($versions)){
		foreach ($versions as $vIdx => $version){
			echo '<pre>Version ';
			echo $version['version'];
			echo ' :';
			echo $version['readme'];
			echo '</pre>';
		}
	}

?>

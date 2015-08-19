<?php
	echo "\n###############################################";
	echo "\n # Creating documentation for API(s)";
	echo "\n###############################################";

	$versionsRoot = 'version';
	$versionDirectories = scandir($versionsRoot);
	foreach ($versionDirectories as $vdIdx => $versionDirectory){
		$version = $versionsRoot . DIRECTORY_SEPARATOR . $versionDirectory;
		$readme = $version . DIRECTORY_SEPARATOR . 'README';
		if (is_dir($version) && is_file($readme)){
			$exec = "php apidoc.php version=" . $versionDirectory;
			echo "\n # Calling: " . $exec;
			exec($exec);
		}
	}

	echo "\n # Done creating documentation for API(s)";
	echo "\n###############################################\n\n";
?>
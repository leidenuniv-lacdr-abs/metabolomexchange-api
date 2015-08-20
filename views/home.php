		Version

		<?php
			if (isset($versions) && is_array($versions) && count($versions) > 1){
				echo '<select>';
				foreach ($versions as $vIdx => $version){
					if ($apiVersion == $version['version']){ 
						$selected = ' selected'; 
					} else { 
						$selected = ''; 
					}
					echo '<option' . $selected . ' value="'.$version['version'].'">'.$version['version'].'</option>';
				}
				echo '</select>';
			} else {
				echo $apiVersion;
			}
		?>

		<p><?=$readme?></p>

		<?php
			require_once('version' . DIRECTORY_SEPARATOR . $apiVersion . DIRECTORY_SEPARATOR . 'apidocs' . DIRECTORY_SEPARATOR . 'index.html');
		?>
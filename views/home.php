		<?php

			$intro = '';		

			if (isset($versions) && is_array($versions) && count($versions) > 1){
				$intro .= '<form>';
				$intro .= '<strong>Version</strong> ';
				$intro .= '<select name="version" onchange="this.form.submit()">';
				foreach ($versions as $vIdx => $version){
					if ($apiVersion == $version['version']){ 
						$selected = ' selected'; 
					} else { 
						$selected = ''; 
					}
					$intro .= '<option' . $selected . ' value="'.$version['version'].'">';
					$intro .= $version['version'];
					if ($defaultApiVersion == $version['version']) { $intro .= ' (default)'; }
					$intro .= '</option>';
				}
				$intro .= '</select>';
				$intro .= '</form>';
				$intro .= '<br />';
			}

			$replace = array(
				'MetabolomeXchange.org'
			);
			$replaceWith = array(
				'<a target="_blank" href="http://www.metabolomexchange.org">MetabolomeXchange.org</a>'
			);

			$intro .= '<p>'.str_replace($replace, $replaceWith, $readme).'</p>';
			$apiDoc = file_get_contents('version' . DIRECTORY_SEPARATOR . $apiVersion . DIRECTORY_SEPARATOR . 'apidocs' . DIRECTORY_SEPARATOR . 'index.html');

			$apiDoc = str_replace('<!-- INTRO -->' , $intro, $apiDoc);

			echo $apiDoc;
		?>
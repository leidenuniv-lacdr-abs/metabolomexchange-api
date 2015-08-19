<?php

date_default_timezone_set('Europe/Amsterdam');

require_once 'src/php-apidoc/Builder.php';
require_once 'src/php-apidoc/Exception.php';
require_once 'src/php-apidoc/Extractor.php';

require_once 'src/flight/Flight.php';

use Crada\Apidoc\Builder;
use Crada\Apidoc\Exception;

if (isset($argv)){
	parse_str(implode('&', array_slice($argv, 1)), $_GET);
}

$version = 1;
if (isset($_GET['version'])){
	$version = $_GET['version'];
} 
require 'version/'.$version.'/api.php';

$classes = array('Api');
$output_dir  = __DIR__ . DIRECTORY_SEPARATOR . 'version' . DIRECTORY_SEPARATOR . $version . DIRECTORY_SEPARATOR . 'apidocs';
$output_file = 'MetabolomeXchange API'; // defaults to index.html

try {
    $builder = new Builder($classes, $output_dir, $output_file);
    $builder->generate();
} catch (Exception $e) {
    echo 'There was an error generating the documentation: ', $e->getMessage();
}	

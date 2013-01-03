#!/usr/bin/php
<?php
define(NEWLINE, "\n");

if(count($argv) <= 1){
	die(
'
*****************************************************
Language Builder Script for the SilverStripe3.
kudos to the original Author:  Roman Schmid, aka Banal
modified to work with silverstripe3 by: Francisco Arenas, aka dospuntocero
*****************************************************
This script searches all .php and .ss files in a given 
directory for calls to the translate function _t.
All found instances will be saved as a yml language file

Usage:  LangBuilder.php <dir>
dir     The directory to search for files. Usually this
        should be your module directory.
        
Examples:
LangBuilder.php mymodule
Will search the "mymodule" folder that\'s in the same 
directory as the LangBuilder.php file. Will extract
all translatable entities and store them in 
mymodule/lang/en.yml
*****************************************************
'
	);
}

$dir = $argv[1];
if(!is_dir($dir)){
	die('ERROR: Not a valid directory: '. $dir . NEWLINE);
}

if(trim($dir) === '/')
	die('ERROR: You must be joking' . NEWLINE);

echo 'Performing search on: ' . $dir . NEWLINE;
$realdir = realpath($dir);
$dest = isset($argv[2]) ? $argv[2] : $realdir . '/lang/en.yml';

$base = dirname($base);
if(is_dir($base))
	die('ERROR: Output directory: ' . $base . ' does not exist!' . NEWLINE);


$extensions = array('.php', '.ss');
$entries = array();

function searchContent($file)
{
	global $entries;
	echo 'Searching content of file: ' . $file . NEWLINE;

	$handle = fopen($file, 'r');
	$content = fread($handle, filesize($file));
	fclose($handle);
	
	if(!$content){
		echo '-- Could not read contents of file!' . NEWLINE;
		return;
	}
	
	$matches = array();
	// convert escaped quotes or it will mess with our regex
	$content = str_replace(array('\"', "\'"), array('&&qtd;', '&&qts;'), $content);
	// match all translatable entities. Entry 2 is to ignore (quotes)
	// the entries we need are in 1 (entity name) and 3 (translatable string)
	$found = preg_match_all(
		'{\b_t\s*\(\s*(?:\'|")([^\'"]+)(?:\'|")\s*,\s*(\'|")((:?\\\2|.)*?)\2}si', 
		$content, $matches
	);
	
	if(!$found){
		echo '-- No translatable strings found.' . NEWLINE;
		return;
	} else {
		echo '-- ' . $found . ' translatable strings found.' . NEWLINE;
	}
	
	for($i = 0; $i < $found; $i++){
		$key = $matches[1][$i];
		
		// find the entity name.
		$entity = strrchr($key, '.');
		
		// if there's no class in the entity name, use the filename
		if(trim($entity) === ''){
			$entity = $key;
			$key = basename($file);
		} else {
			$key = substr($key, 0, -strlen($entity));
			$entity = ltrim($entity, '.');
		}
		
		if(!isset($entries[$key]))
			$entries[$key] = array();
			
		if(!isset($entries[$key][$entity]))
			$entries[$key][$entity] = stripslashes(
				// convert our quotes back to normal
				str_replace(array('&&qtd;', '&&qts;'), array('"', "'"), $matches[3][$i])
			);
	}
	//print_r($matches);
	
}

function replaceDir($searchDir)
{
	global $extensions;
	echo 'Entering directory: '. $searchDir . NEWLINE;
	$handle = opendir($searchDir);
	while (($file = readdir($handle)) !== false) {
		// skip dot and double-dot files
		if($file === '.' || $file === '..')
			continue;
		
		$fpath = $searchDir . '/' . $file;
		
		if(is_file($fpath)){
			$ext = strtolower(strrchr($file, '.'));
			if(in_array($ext, $extensions)){
				searchContent($fpath);
			}
		} else if(is_dir($fpath)){
			replaceDir($fpath);
		}
	}
}

// recursively search all sub-directories
replaceDir($realdir);

// now we have a populated $entries array
$output = 'en:'.NEWLINE;
foreach($entries as $key => $arr){
	$output .= '  '.NEWLINE . '# Output for class or file: '. $key . NEWLINE;
	$output .= '  '.$key.':'.NEWLINE;
	foreach($arr as $entry => $trnl){
		$output .= '    '.$entry.':"'.str_replace("'", "\'", $trnl).'"'.NEWLINE;
	}
}

$handle = fopen($dest, 'w');
echo '---------------------------------------------------' . NEWLINE;
if(fwrite($handle, $output) === false){
	echo 'ERROR: Unable to write to: ' . $dest . NEWLINE;
} else {
	echo 'Language entities successfully written to: ' . NEWLINE . $dest . NEWLINE;
}
echo '---------------------------------------------------' . NEWLINE;
fclose($handle);

?>
<?php


/**

 * The IMREE API
 * =============
 * 
 * At the moment, this only returns an xml file based on the requested server command
 */


if(isset($_POST['command'])) {
	if($_POST['command'] === "group") {
		$file = "sample_group.xml";
	} else if($_POST['command'] === "item") {
		$file = "sample_item1.xml";
	} else {
		die("That command does not exist");
	}
	$string = file_get_contents($file);
	echo $string;
	
} else {
	echo "<h1>IMREE API</h1><p>This API is not yet documented.</p>";
}
<?php

function exhibit_data($exhibit_id) {
	$conn = db_connect();
	$exhibit_results = db_query($conn, "SELECT * FROM exhibits WHERE exhibit_id = ".  db_escape($exhibit_id));
	$exhibit['exhibit_properties'] = $exhibit_results[0];
	$results = db_query($conn, "SELECT * FROM modules WHERE exhibit_id = ".  db_escape($exhibit_id) . " AND module_parent_id = 0");
	for($i = 0; $i< count($results); $i++) {
		$child_modules = exhibit_child_modules($results[$i]['module_id']);
		if($child_modules) {
			$results[$i]['child_modules'] = $child_modules;
		}
		$child_assets = exhibit_module_assets($results[$i]['module_id']);
		if($child_assets) {
			$results[$i]['child_assets'] = $child_assets;
		}
	}
	if(count($results)) {
		$exhibit['modules'] = $results;
	}
	return $exhibit;
}

function exhibit_module_assets($module_id) {
	$conn = db_connect();
	$assets = db_query($conn, "SELECT * FROM module_assets WHERE module_id = ".db_escape($module_id));
	if(count($assets)) {
		return $assets;
	} else {
		return false;
	}
}

function exhibit_child_modules($module_parent_id) {
	$conn = db_connect();
	$children = db_query($conn, "SELECT * FROM modules WHERE module_parent_id = ".  db_escape($module_parent_id));
	if(count($children)) {
		for($i = 0; $i < count($children); $i++) {
			$grandkids = exhibit_child_modules($children[$i]['module_id']);
			if($grandkids) {
				$children[$i]['child_modules'] = $grandkids;
			}
			$assets = exhibit_module_assets($children[$i]['module_id']);
			if($assets) {
				$children[$i]['child_assets'] = $assets;
			}
		}
		return $children;
	} else {
		return false;
	}
}



?>

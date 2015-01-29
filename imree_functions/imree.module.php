<?php

function imree_module_get_exhibit_id($module_id) {
	$conn = db_connect();
	$results = db_query($conn, "SELECT * FROM modules WHERE module_id = ".db_escape($module_id));
	if(count($results)) {
		if($results[0]['module_parent_id'] == 0) {
			return $results[0]['exhibit_id'];
		} else {
			return imree_module_get_exhibit_id($results[0]['module_parent_id']);
		}
	} else {
		return false;
	}
} 

function imree_module_location_id($module_id) {
	$conn = db_connect();
	$results = db_query($conn, "SELECT * FROM device_locations WHERE device_location_module_id = ".db_escape($module_id));
	if(count($results)) {
		return $results[0]['device_location_id'];
	} else {
		return 0;
	}
}

function imree_module_get_top_two_modules($module_id) {
	global $imree_module_parent_ids_array;
	imree_module_parent_ids($module_id);
	$imree_module_parent_ids_array[] = $module_id;
	
	array_reverse($imree_module_parent_ids_array);
	if(count($imree_module_parent_ids_array)===0) {
		return array(0,0);
	} else if (count($imree_module_parent_ids_array) === 1) {
		return array($imree_module_parent_ids_array[0], 0);
	} else {
		return array($imree_module_parent_ids_array[0], $imree_module_parent_ids_array[1]);
	}
}

$imree_module_parent_ids_array = array();
function imree_module_parent_ids($module_id) {
	global $imree_module_parent_ids_array;
	$results = db_query(db_connect(), "SELECT * FROM modules WHERE module_id = ".db_escape($module_id));
	if($results[0]['module_parent_id'] != 0) {
		$imree_module_parent_ids_array[] = $results[0]['module_parent_id'];
		imree_module_parent_ids($results[0]['module_parent_id']);
	}
}

function imree_prefill_new_exhbit($exhibit_id) {
    $conn = db_connect();
    db_exec($conn, build_insert_query($conn, 'modules', array(
	'module_order' => 0,
	'module_name' => "A new chapter",
	'module_parent_id' => 0,
	'module_type' => 'narrative',
	'exhibit_id'=> $exhibit_id,
    )));
    $narrative_id = $conn->lastInsertId();
    db_exec($conn, build_insert_query($conn, 'modules', array(
	'module_order' => 0,
	'module_name' => "An empty grouping",
	'module_parent_id' => $narrative_id,
	'module_type' => 'grid',
	'exhibit_id'=> 0,
    )));
}
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
<?php


function imree_group_new($people_group_name, $creator_id) {
	$conn = db_connect();
	$results = db_exec($conn, build_insert_query($conn, "people_group", array(
	    'people_group_name' => $people_group_name,
	    'people_group_creator' => $creator_id,
	    'people_group_created' => date("Y-m-d H:i:s"),
	)));
	if($results) {
		return $results['last_id'];
	} else {
		return false;
	}
}
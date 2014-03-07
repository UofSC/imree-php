<?php

function exhibit_data($exhibit_id) {
	$conn = db_connect();
	$exhibit_results = db_query($conn, "SELECT * FROM exhibits WHERE exhibit_id = ".  db_escape($exhibit_id));
	$exhibit['exhibit_properties'] = $exhibit_results[0];
	$results = db_query($conn, "SELECT * FROM modules WHERE exhibit_id = ".  db_escape($exhibit_id) . " AND module_parent_id = 0 ORDER BY module_order ASC ");
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
	global $imree_absolute_path;
	$conn = db_connect();
	$assets = db_query($conn, "SELECT module_assets.*, asset_data.asset_data_type AS module_type, asset_data.asset_data_name, 1 AS asset FROM module_assets LEFT JOIN asset_data USING (asset_data_id) WHERE module_id = ".db_escape($module_id). " ORDER BY module_asset_order ASC");
	if(count($assets)) {
		for($i =0; $i < count($assets); $i++) {
			if($assets[$i]['asset_data_id'] > 0) {
				$assets[$i]['asset_url'] = $imree_absolute_path . "file/" . $assets[$i]['asset_data_id'];
				if(strpos($assets[$i]['module_type'], 'image') !== false) {
					$assets[$i]['asset_resizeable'] = '1';
				} else {
					$assets[$i]['asset_resizeable'] = '0';
				}
			} else {
				$assets[$i]['asset_url'] = "";
				$assets[$i]['asset_resizeable'] = '0';
			}
		}
		return $assets;
	} else {
		return false;
	}
}

function exhibit_child_modules($module_parent_id) {
	$conn = db_connect();
	$children = db_query($conn, "SELECT *, 0 AS asset FROM modules WHERE module_parent_id = ".  db_escape($module_parent_id) . " ORDER BY module_order ASC");
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

<?php

function exhibit_data($exhibit_id) {
	$conn = db_connect();
	$exhibit_results = db_query($conn, "SELECT * FROM exhibits WHERE exhibit_id = ".  db_escape($exhibit_id));
	$exhibit['exhibit_properties'] = $exhibit_results[0];
	$results = db_query($conn, "SELECT * FROM modules WHERE exhibit_id = ".  db_escape($exhibit_id) . " AND module_parent_id = 0 ORDER BY module_order ASC ");
	for($i = 0; $i< count($results); $i++) {
		$child_modules = array_merge(exhibit_child_modules($results[$i]['module_id']), exhibit_module_assets($results[$i]['module_id'])); 
		$children = array();
		foreach($child_modules as $mod) {
			if(isset($mod['module_order'])) {
				$index = $mod['module_order'];
			} else {
				$index = $mod['module_asset_order'];
			}
			$order = str_pad($index, 6, "0",STR_PAD_LEFT)  . "000000"; //makes a 12 char string like: 000024000000
			if(isset($children[$order])) {
				$order = substr($order, 0, 6) + random_string(6);
			}
			$children[$order] = $mod;
		}
		
		ksort($children);
		$k=0;
		foreach($children as $key=>$mod) {
			$children[$key]['original_order'] = $k;
			$k++;
		}
		
		if(count($child_modules)) {
			$results[$i]['child_modules'] = array_values($children);
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
	$assets = db_query($conn, "
		SELECT 
			module_assets.*, 
			sources.source_common_name,
			sources.source_url,
			sources.source_credit_statement,
			asset_data.asset_data_type AS module_type, 
			asset_data.asset_data_name, 1 AS asset 
		FROM 
			module_assets 
			LEFT JOIN asset_data USING (asset_data_id) 
			LEFT JOIN sources ON (asset_data.asset_data_source_repository = sources.source_id)
		WHERE module_id = ".db_escape($module_id). " 
		ORDER BY module_asset_order ASC");
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
		return array();
	}
}

function exhibit_child_modules($module_parent_id) {
	$conn = db_connect();
	$children = db_query($conn, "SELECT *, 0 AS asset FROM modules WHERE module_parent_id = ".  db_escape($module_parent_id) . " ORDER BY module_order ASC");
	if(count($children)) {
		for($i = 0; $i < count($children); $i++) {
			$grandchild_modules = array_merge(exhibit_child_modules($children[$i]['module_id']), exhibit_module_assets($children[$i]['module_id'])); 
			$grandkids = array();
			foreach($grandchild_modules as $mod) {
				if(isset($mod['module_order'])) {
					$index = $mod['module_order'];
				} else {
					$index = $mod['module_asset_order'];
				}
				$order = str_pad($index, 6, "0",STR_PAD_LEFT)  . "000000"; //makes a 12 char string like: 000024000000
				if(isset($grandkids[$order])) {
					$order = substr($order, 0, 6) + random_string(6);
				}
				$grandkids[$order] = $mod;
			}

			ksort($grandkids);
			$k=0;
			foreach($grandkids as $key=>$mod) {
				$grandkids[$key]['original_order'] = $k;
				$k++;
			}

			if(count($grandkids)) {
				$children[$i]['child_modules'] = array_values($grandkids);
			}
		}
		return $children;
	} else {
		return array();
	}
}


function exhibit_clone($exhibit_id, $device=NULL){
    // exhibit -> module -> module_data -> asset_data
    $conn = db_connect();
    //$modules = $exhibit_data['modules'];
    //$exhibit_props = $exhibit_data['exhibit_properties'];
    
    $exhibit_data = db_query($conn, "SELECT * FROM exhibits WHERE exhibit_id = ".db_escape($exhibit_data));
    $module_data = db_query($conn, "SELECT * FROM modules WHERE exhibit_id = ".db_escape($exhibit_id));
    
    foreach($module_data as $results){
       $results['exhibit_id']; //New modules are needed to link to the new exhibit_id <<<<-------- @todo figure out how to find this
	  
	  /**
	   * the new exhibit id can be found when you insert the new exhibit data using db_exec and build_insert_query. build_insert_query does all the escaping for you :-) 
	   * 1: $result = db_exec($conn, build_insert_query($conn, 'modules', array('exhibit_name'=>$exhibit_results[0]['exhibit_name'], 'exhibit_sub_name' => $exhibit_results[0]['exhibit_sub_name]... ));
	   * The new exhibit can be identified now by $result['last_id'];
	   */
       $new_modules = "INSERT INTO modules (module_name, module_display_name, module_display_child_names, 
                                            module_sub_name, exhibit_id, module_parent_id, module_order,
                                            module_type, module_display_date_start, module_display_date_end,
                                            thumb_display_columns, thumb_display_rows
                                           )
                       VALUES (".db_escape($results['module_name']).", ".db_escape($results['module_display_name']).", ".db_escape($results['module_display_child_names']).", "
                                .db_escape($results['module_sub_name']).", ".db_escape($new_exhibit_id).", ".db_escape($results['module_parent_id']).", "
                                .db_escape($results['module_order']).", ".db_escape($results['module_type']).", ".db_escape($results['module_display_date_start']).", "
                                .db_escape($results['module_display_date_end']).", ".db_escape($results['thumb_display_columns']).", ".db_escape($results['thumb_display_rows'])."
                              )";
    }  
    //@todo find a way to get new exhibit_id, test queries, make sure new modules for exhibit clone pull correct module_asset_id and asset_data_id 
   
}

?>
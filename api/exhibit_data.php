<?php

require_once("../../config.php");
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
	$results = array();
	foreach($assets as $item) {
		$results[] = exhibit_module_asset($item['module_asset_id']);
	}
	return $results;
}

function exhibit_module_asset($module_asset_id) {
    global $imree_absolute_path;
    $conn = db_connect();
    $results = db_query($conn, "SELECT 
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
            WHERE module_asset_id = ".db_escape($module_asset_id));
    $asset = $results[0];
    if($asset['asset_data_id'] > 0) {
            $asset['asset_url'] = $imree_absolute_path . "file/" . $asset['asset_data_id'];
            $asset['asset_resizeable'] = strpos($asset['module_type'], 'image') !== false ? '1' : '0';
    } else {
            $asset['asset_url'] = "";
            $asset['asset_resizeable'] = '0';
    }
    $asset['description_textflow'] = "<![CDATA[
        ". html_to_textflow(str_replace(array("<B>","<I>","<U>","</B>","</I>","</U>"), array("<b>","<i>","<u>","</b>","</i>","</u>"),$asset['description']), "whiteSpaceCollapse='preserve' "). "
            ]]>";
    $asset['description'] = "<![CDATA[
        " . $asset['description'] . "
            ]]>";
    
    $asset['relations'] = module_asset_relations($asset['module_asset_id']);
    return $asset;
}


function module_asset_relations($module_asset_id) {
    $conn = db_connect();
    $results = db_query($conn, "SELECT * FROM module_asset_relations WHERE module_asset_A_id = ".db_escape($module_asset_id));
    $relations = array();
    foreach ($results as $item) {
        $asset = exhibit_module_asset($item['module_asset_B_id']);
        $asset['relation'] = $item['module_asset_relation_type'];
        $relations[] = $asset;
    }
    return $relations;
}


function exhibit_child_modules($module_parent_id) {
	$conn = db_connect();
	$children = db_query($conn, "SELECT *, 0 AS asset FROM modules WHERE module_parent_id = ".  db_escape($module_parent_id) . " ORDER BY module_order ASC");
	if(count($children)) {
		for($i = 0; $i < count($children); $i++) {
			$grandchild_modules = array_merge(exhibit_child_modules($children[$i]['module_id']), exhibit_module_assets($children[$i]['module_id'])); 
			$grandkids = array();
			
			for($j = 0; $j < count($grandchild_modules); $j++) {
				$mod = $grandchild_modules[$j];
				if(isset($mod['module_order'])) {
					$index = $mod['module_order'];
					if($j != $index) {
						db_exec($conn, "UPDATE modules SET module_order = ".db_escape($j)." WHERE module_id = ".db_escape($mod['module_id']));
					}
				} else {
					$index = $mod['module_asset_order'];
					if($j != $index) {
						db_exec($conn, "UPDATE module_assets SET module_asset_order = ".db_escape($j)." WHERE module_asset_id = ".db_escape($mod['module_asset_id']));
					}
				}
				$grandkids[] = $mod;
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

/**
 * exhibit_clone
 * Creates a clone of an exhibit in the database 
 * @author Cole Mendes <mendesc@email.sc.edu>
 * @param type $exhibit_id - ID of exhibit to clone
 * @param type $device - @todo implement versions for target devices - "out tablets", "user tablets", "web", "kiosk"
 */
function exhibit_clone($exhibit_id, $device=NULL){
    $conn = db_connect();
    $exhibit_data = db_query($conn, "SELECT * FROM exhibits WHERE exhibit_id = ".db_escape($exhibit_id));
    $module_data = db_query($conn, "SELECT * FROM modules WHERE exhibit_id = ".db_escape($exhibit_id)." AND module_parent_id != 0");
    $results = db_exec($conn, build_insert_query($conn, 'exhibits', Array( 'exhibit_name'=>$exhibit_data[0]['exhibit_name'],
                                                                           'exhibit_sub_name'=>$exhibit_data[0]['exhibit_sub_name'],
                                                                           'exhibit_date_start'=>$exhibit_data[0]['exhibit_date_start'],
                                                                           'exhibit_date_end'=>$exhibit_data[0]['exhibit_date_end'],
                                                                           //'exhibit_is_kisok'=>$exhibit_data[0]['exhibit_is_kisok'],
                                                                          // 'exhibit_is_tablet'=>$exhibit_data[0]['exhibit_is_tablet'],
                                                                          // 'exhibit_is_public'=>$exhibit_data[0]['exhibit_is_public'],
                                                                           'exhibit_department_id'=>$exhibit_data[0]['exhibit_department_id'],
                                                                           'people_group_id'=>$exhibit_data[0]['people_group_id'],
                                                                           'theme_id'=>$exhibit_data[0]['theme_id'],
                                                                           'exhibit_cover_image_url'=>$exhibit_data[0]['exhibit_cover_image_url'],
                                                                           //'exhibit_about'=>$exhibit_data[0]['exhibit_about']
                       )));
    $new_exhibit_id = $results['last_id'];
    
    //Creates new modules to match new exhibit_id
    
    foreach($module_data as &$module){
            $module_assets_data = db_query($conn, "SELECT * FROM module_assets WHERE module_id = ".db_escape($module['module_id']));
            $module_id = db_exec($conn, build_insert_query($conn, 'modules', Array( 'module_name'=>$module['module_name'],
                                                                                    'module_display_name'=>$module['module_display_name'],
                                                                                    'module_display_child_names'=>$module['module_display_child_names'], 
                                                                                    'module_sub_name'=>$module['module_sub_name'], 
                                                                                    'exhibit_id'=>$new_exhibit_id, 
                                                                                    'module_parent_id'=>$module['module_parent_id'], 
                                                                                    'module_order'=>$module['module_order'],
                                                                                    'module_type'=>$module['module_type'], 
                                                                                    'module_display_date_start'=>$module['module_display_date_start'], 
                                                                                    'module_display_date_end'=>$module['module_display_date_end'],
                                                                                    'thumb_display_columns'=>$module['thumb_display_columns'], 
                                                                                    'thumb_display_rows'=>$module['thumb_display_rows']
                                 )));
            //var_dump($module['module_parent_id']);
            //Creates new module_assets to match new module_ids
            foreach($module_assets_data as &$module_asset){
                    db_exec($conn, build_insert_query($conn, 'module_assets', Array( 'module_id'=>$module_id['last_id'],
                                                                                     'module_asset_order'=>$module_asset['module_asset_order'],
                                                                                     'asset_data_id'=>$module_asset['asset_data_id'],
                                                                                     'asset_specific_thumbnail_url'=>$module_asset['asset_specific_thumbnail_url'],
                                                                                     'module_asset_title'=>$module_asset['module_asset_title'],
                                                                                     'caption'=>$module_asset['caption'],
                                                                                     'description'=>$module_asset['description'],
                                                                                     'module_asset_display_date_start'=>$module_asset['module_asset_display_date_start'],
                                                                                     'module_asset_display_date_end'=>$module_asset['module_asset_display_date_end'],
                                                                                     'original_url'=>$module_asset['original_url'],
                                                                                     'source_repository'=>$module_asset['source_repository'],
                                                                                     'thumb_display_columns'=>$module_asset['thumb_display_columns'],
                                                                                     'thumb_display_rows'=>$module_asset['thumb_display_rows'],
                                                                                     'username'=>$module_asset['username']
                            )));   
            }   
    }
    //Deal with parent id's
    $module_children = db_query($conn, "SELECT * FROM modules WHERE exhibit_id=".db_escape($new_exhibit_id));
    //$parents = Array();
    $new_parent = Array();
    $children = Array();
    
    //find which modules have parents
    foreach($module_children as &$child){ 
       if($child['module_parent_id'] != 0){
           $children['module_id'][] = $child['module_id'];
           $children['parent_id'][] = $child['module_parent_id'];    
       }    
    }
    
    //get multiple children count
    $dup_parent_ids = array_count_values($children['parent_id']);
    $new_parents = Array();
    
    foreach($dup_parent_ids as $new_parent => &$child_cnt){
        $parents = db_query($conn, "SELECT * FROM modules WHERE module_id = ".db_escape($new_parent));
        $parent_id = db_exec($conn, build_insert_query($conn, 'modules', Array( 'module_name'=>$parents[0]['module_name'],
                                                                                        'module_display_name'=>$parents[0]['module_display_name'],
                                                                                        'module_display_child_names'=>$parents[0]['module_display_child_names'], 
                                                                                        'module_sub_name'=>$parents[0]['module_sub_name'], 
                                                                                        'exhibit_id'=>$new_exhibit_id,
                                                                                        'module_parent_id'=>$parents[0]['module_parent_id'], 
                                                                                        'module_order'=>$parents[0]['module_order'],
                                                                                        'module_type'=>$parents[0]['module_type'], 
                                                                                        'module_display_date_start'=>$parents[0]['module_display_date_start'], 
                                                                                        'module_display_date_end'=>$parents[0]['module_display_date_end'],
                                                                                        'thumb_display_columns'=>$parents[0]['thumb_display_columns'], 
                                                                                        'thumb_display_rows'=>$parents[0]['thumb_display_rows']
                                         )));
       
            
        db_exec($conn, "UPDATE modules SET module_parent_id=".db_escape($parent_id['last_id'])." WHERE exhibit_id=".db_escape($new_exhibit_id)." AND module_parent_id=".db_escape($new_parent));
        
        //deal with grandparents; make sure no duplicates
        
         
    } 
    
     
}
/*
function exhibit_dup($exhibit_id){
    $conn = db_connect();
    $query = "CREATE TEMPORARY TABLE tmp SELECT * FROM exhibits WHERE exhibit_id=".db_escape($exhibit_id).";.
             ALTER TABLE tmp DROP PRIMARY KEY;
             INSERT INTO exhibits SELECT 0,tmp.* FROM tmp;
             DROP TABLE tmp;";
    var_dump($query);
    $temp = db_exec($conn, $query);
    var_dump($temp);
}
*/

/**
 * function module_parent_ids
 * builds new parent ids to match new modules 
 * created in exhibit_copy()
 * @param type $parent_id
 
//exhibit_clone(1);
function module_parent_ids($parent_id, $exhibit_id){
    $conn = db_connect();
    $parents = Array();
    $count = 0;
    $ids = Array();
    
    if($parent_id != 0){
        while($parent_id != 0){
            $parents[$count] = db_query($conn, "SELECT * FROM modules WHERE module_id = ".db_escape($parent_id));
            $parent_id = $parents[$count][0]['module_parent_id'];
            $ids[$count] = $parent_id;
            $count++;
        }
        
        for($i = count($parents)-1; $i >= 0; $i--){
                $copy = db_query($conn, "SELECT * FROM modules WHERE exhibit_id = ".db_escape($exhibit_id));
                $module_assets_data = db_query($conn, "SELECT * FROM module_assets WHERE module_id = ".db_escape($parent_id));
                $module_id = db_exec($conn, build_insert_query($conn, 'modules', Array( 'module_name'=>$parents[$i][0]['module_name'],
                                                                                        'module_display_name'=>$parents[$i][0]['module_display_name'],
                                                                                        'module_display_child_names'=>$parents[$i][0]['module_display_child_names'], 
                                                                                        'module_sub_name'=>$parents[$i][0]['module_sub_name'], 
                                                                                        'exhibit_id'=>$exhibit_id,
                                                                                       'module_parent_id'=>$parent_id, 
                                                                                        'module_order'=>$parents[$i][0]['module_order'],
                                                                                        'module_type'=>$parents[$i][0]['module_type'], 
                                                                                        'module_display_date_start'=>$parents[$i][0]['module_display_date_start'], 
                                                                                        'module_display_date_end'=>$parents[$i][0]['module_display_date_end'],
                                                                                        'thumb_display_columns'=>$parents[$i][0]['thumb_display_columns'], 
                                                                                        'thumb_display_rows'=>$parents[$i][0]['thumb_display_rows']
                                         )));
                $parent_id = $module_id['last_id'];
                //Creates new module_assets to match new parent module_ids
                foreach($module_assets_data as &$module_asset){
                    db_exec($conn, build_insert_query($conn, 'module_assets', Array( 'module_id'=>$parent_id,
                                                                                     'module_asset_order'=>$module_asset['module_asset_order'],
                                                                                     'asset_data_id'=>$module_asset['asset_data_id'],
                                                                                     'asset_specific_thumbnail_url'=>$module_asset['asset_specific_thumbnail_url'],
                                                                                     'module_asset_title'=>$module_asset['module_asset_title'],
                                                                                     'caption'=>$module_asset['caption'],
                                                                                     'description'=>$module_asset['description'],
                                                                                     'module_asset_display_date_start'=>$module_asset['module_asset_display_date_start'],
                                                                                     'module_asset_display_date_end'=>$module_asset['module_asset_display_date_end'],
                                                                                     'original_url'=>$module_asset['original_url'],
                                                                                     'source_repository'=>$module_asset['source_repository'],
                                                                                     'thumb_display_columns'=>$module_asset['thumb_display_columns'],
                                                                                     'thumb_display_rows'=>$module_asset['thumb_display_rows'],
                                                                                     'username'=>$module_asset['username']
                            )));   
                }
        }
        return $parent_id;
    }else{
        return 0;
    }
}

 */

?>
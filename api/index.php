<?php


/**

 * The IMREE API
 * =============
 * 
 */
require_once('../../config.php');
$conn = db_connect();

if(isset($cfg_signage_db_username)) {
    $ocfg_db_host = $cfg_db_host;
    $ocfg_db_type = $cfg_db_type;
    $ocfg_db_name = $cfg_db_name;
    $ocfg_db_username = $cfg_db_username;
    $ocfg_db_password = $cfg_db_password;
    
    $cfg_db_host = $cfg_signage_db_host;
    $cfg_db_type = $cfg_signage_db_type;
    $cfg_db_name = $cfg_signage_db_name;
    $cfg_db_username = $cfg_signage_db_username;
    $cfg_db_password = $cfg_signage_db_password;
    $signage_conn = db_connect(false);
    
    $cfg_db_host = $ocfg_db_host;
    $cfg_db_type = $ocfg_db_type;
    $cfg_db_name = $ocfg_db_name;
    $cfg_db_username = $ocfg_db_username;
    $cfg_db_password = $ocfg_db_password;
}
$errors = array();
$results = array();

$msg_permission_denied = "<response><success>false</success><error>Permission Denied.</error></response>";

$str = "<?xml version='1.0' encoding='UTF-8' ?>";

function sort_results($results, $search_query, $results_per_page=10, $page=1)
{
    $conn = db_connect();
    
    $new_table = true;
    $index = db_query($conn, "SELECT * FROM search_tbl_index");
    
    $current_time = time();
   
    //Remove old search tables
    foreach($index as $search)
    {
        $time = strtotime($search['search_table_index_datetime']);
        if(($current_time-$time) > 1800 && $search_query != $search['keyword'])//30 minutes
        {
            db_exec($conn, "DROP TABLE IF EXISTS ".$search['search_table_name']);
        }
    }
    
    //Checks for invalid page number
    if($page < 1)
    {
        $page = 1;
    }
    
    //Checks if a table already esists for this search query
    foreach($index as $query)
    {
        if($query['keyword'] == $search_query)
        {
            $table_name = $query['search_table_name'];
            $new_table = false;
        }
    }
    
    if($new_table)
    {
        $table_num = db_query($conn, "SELECT COUNT(*) FROM search_tbl_index");
        $table_num = $table_num[0]['COUNT(*)'];

        $table_name = "search_tbl_".$table_num;
        
        //create search table
        $test = db_exec($conn, "CREATE TABLE ".$table_name."
                                   (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                    collection VARCHAR(255),
                                    title VARCHAR(255),
                                    metadata VARCHAR(255),
                                    type VARCHAR(255),
                                    repository VARCHAR(255),
                                    thumbnail_url VARCHAR(255),
                                    rank INT,
                                FULLTEXT INDEX idx(title, metadata) )
                                ENGINE = MYISAM
                                "
                       ); 

        //Add the search query to the search table index
        $index_query = build_insert_query($conn, 'search_tbl_index', Array('keyword' => $search_query, 
                                                                           'search_table_name' => $table_name,
                                                                           'search_table_index_datetime' => date("Y-m-d H:i:s")
                                                                           ));
        db_exec($conn, $index_query);

        //Fill search table without children 
        foreach($results as $item)
        {
            $insert = Array();

            $insert['collection'] = $item['collection'];
            $insert['title'] = $item['title'];
            $insert['thumbnail_url'] = $item['thumbnail_url'];
            $insert['repository'] = $item['repository'];
            $insert['type'] = $item['type'];
            $insert['metadata'] = $item['metadata'];

            $query = build_insert_query($conn, $table_name, $insert);
            db_exec($conn, $query);
        }

        //All results
        $query = "SELECT * FROM ".$table_name;
        $all_results = db_query($conn, $query);

        //Fulltext sort
        $sort_query =  "SELECT * FROM ".$table_name." WHERE MATCH (title,metadata) AGAINST (".db_escape($search_query).")";
        $sorted_results = db_query($conn, $sort_query);

        //Adds the remaining results to the sorted results 
        foreach($all_results as $result)
        {
            $add = true;
            foreach($sorted_results as $sort)
            {
                if($result['id'] === $sort['id'])
                {    
                    $add = false;
                }   
            }
            if($add)
            {    
                $sorted_results[] = $result;
            }   
        }

        //Order by rank
        $rank = 1;
        foreach($sorted_results as $result)
        {
            db_exec($conn, "UPDATE ".$table_name." SET rank = ".db_escape($rank)." WHERE id = ".db_escape($result['id']));
            $rank++;
        }

        db_exec($conn, "ALTER TABLE ".$table_name." ORDER BY rank"); //Orders results by relevance
    }//if($new_table)
    
    $page_query = "SELECT * FROM ".$table_name." WHERE rank > ".db_escape($results_per_page*($page-1))." AND rank <= ".db_escape($results_per_page*$page);
    $paged_results = db_query($conn, $page_query);
    
    //Add children back to sorted parent items
    foreach($results as $item)
    {
        foreach($paged_results as &$sres)
        {
            if($sres['title'] === $item['title'])//maybe use something else here
            {
                $sres['children'] = $item['children'];
            }
        }
    }  
    return $paged_results;
}


/**
 * This is a silly function that makes xml elements from an array of children
 * @param array $results a php array of items
 * @return string The xml data
 */
function children($results) {
    $string = "";
    foreach($results as $item) {
        $string .= "<item>";
        foreach($item as $key=>$val) {
            $string .= "<".  htmlspecialchars($key).">";
            if(is_array($val)) {
                $string .= children($val);
            } else {
                $string .= htmlspecialchars($val);
            }
            $string .= "</".htmlspecialchars($key).">";
        }
        $string .= "</item>";
    }
    return $string;
}


function quick_auth() {
	global $username, $password, $str;
	$ulogin = new uLogin();
	$ulogin->Authenticate($username, $password);
	if($ulogin->AuthResult) {
		return true;
	} else {
		$str .= "<response><success>fail</success><error>auth_fail</error></response>";
	}
}
/**
 * This keeps a log of every time a digital signage device makes a query. More of a "is it on" check than something diagnostically useful
 * @global PDO $conn
 * @param string $device_mac_address
 */
function device_chirp($device_mac_address) {
	global $conn;
	db_exec($conn, "UPDATE devices SET device_last_chirp = '".date("Y-m-d H:i:s")."' WHERE device_mac_address = ".  db_escape($device_mac_address));
}

function IMREE_log($ip, $module_type, $module_id) {
	//need to do some logging here for assesment feedback
}

$command = (filter_input(INPUT_POST,'command') !== null) ? filter_input(INPUT_POST, "command") : filter_input(INPUT_GET, "command");
$command_parameter = (filter_input(INPUT_POST, 'command_parameter') !== null) ? filter_input(INPUT_POST, "command_parameter") : filter_input(INPUT_GET, "command_parameter");
$username = filter_input(INPUT_POST, "username");
$password = filter_input(INPUT_POST, "password");
$session_key = filter_input(INPUT_POST, "sessionkey"); 
$device_mac_address = filter_input(INPUT_POST, 'macaddress');

//error_log($session_id);
//error_log("Command: " . $command . " Parameter: " .$command_parameter );

//add command 
if($command) {
    if($command === "mode") {
        $session_key = build_session();
        if($device_mac_address !== null) {
            $results = db_query($conn, "SELECT * FROM devices WHERE device_mac_address = ".db_escape($device_mac_address));
            if(count($results) > 0 ) {
                device_chirp($device_mac_address);
                $str .= "<response><success>true</success>\n<result>\n<key>".htmlspecialchars($session_key)."</key>\n<mode>".$results[0]['device_mode']."</mode>\n</result></response>";
		
            } else {
                $str .= "<response><success>true</success>\n<result>\n<key>".htmlspecialchars($session_key)."</key>\n<mode>normal</mode>\n</result></response>";
            }
        } else {
            $str .= "<response><success>true</success>\n<result>\n<key>".htmlspecialchars($session_key)."</key>\n<mode>normal</mode>\n</result></response>";
        }
        
       
	   
	   
    } 
    
    /** Needs to rethought - especially cause there's no reliable device ips anymore 
    else if($command === "signage_items") {
        $ip = filter_input(INPUT_SERVER,'REMOTE_ADDR');
        device_chirp($ip);
        $results = db_query($conn, "
         SELECT * FROM devices
		LEFT JOIN signage_feed_device_assignments USING (device_id)
		LEFT JOIN signage_feeds USING (signage_feed_id)
		WHERE devices.device_ip = ".db_escape($ip));
        $str .= "<response><success>true</success>\n<result>".children($results)."</result></response>";
    } 
    */
    
    
    
      else if($command === 'signage_properties') {
        device_chirp($device_mac_address);
        $results = db_query($conn, "SELECT * FROM devices WHERE device_mac_address = ".db_escape($device_mac_address));
        if(isset($signage_conn)) {
            $delphi_properties = db_query($signage_conn, "SELECT * FROM news2.signage_devices WHERE foreign_device_id = ".  db_escape($results[0]['device_id'], $signage_conn));
            $results[0] = array_merge($results[0], $delphi_properties[0]);
            $str .= "<response><success>true</success>\n<result>".children($results)."</result></response>";
        } else {
            $str .= "<response><success>true</success>\n<result>".children($results)."</result></response>";
        }
        
        
	   
        
	   
    } else if($command === "search") {
        if(!$command_parameter) {
             $errors[] = "command_parameter not set. The command parameter must be set to the desired search term.";
        } else {
          if(quick_auth()) {
			$user = new imree_person(imree_person_id_from_username($username));
			//$user = new imree_person(1);
			$results = array();
                        $parameters = json_decode($command_parameter);
			foreach($user->sources as $source) {
				set_time_limit(90);
				$results = array_merge($results, $source->search($parameters->search_query, 20, 0));
			}
                        
                        $results = sort_results($results, $parameters->search_query, $results_per_page=10, $parameters->page);
			//Results of all items that match a full-text search
				 /**
					$results = array_merge(db_query($conn, "SELECT assets.* FROM assets
					   LEFT JOIN asset_metadata_assignments USING (asset_id)
					   LEFT JOIN metadata USING (metadata_id)
					   WHERE MATCH(metadata.metadata_value) AGAINST (".db_escape($command_parameter).")
					   GROUP BY assets.asset_id"
					), $CDM_results);
				  * 
				  */
			 $str .= "<response><success>true</success><result>
			 <children>
					 ".children($results)." 
			 </children></result></response>";
			}
        }

	   
	   
	} else if($command === "ingest") {
		if(quick_auth()) {
			$user = new imree_person(imree_person_id_from_username($username));	
			if(!$command_parameter) {
				$errors[] = "command_parameter not set. The command parameter must be a json encoded object with three nodes: asset_repository, asset_id, asset_collection.";
			} else {
				$parameters = json_decode($command_parameter);
				if(!isset($parameters->asset_id, $parameters->asset_repository)) {
					$errors[] = "Invalid command_parameter for command:ingest. The command parameter must be a json encoded object with three nodes: asset_repository, asset_id, asset_collection.";
				} 
			}

			if(count($errors)==0) {
				foreach($user->sources as $source) {
					imree_error_log("Ingested. Source:".$source->id . " repository:" .$parameters->asset_repository);
					if($source->id === $parameters->asset_repository) {
						$target = $source;
					}
				}
				if($target) {
					$asset = $target->get_asset($parameters->asset_id, $parameters->asset_collection);
					$asset_data_id = IMREE_asset_ingest($asset['asset_data'], $asset['asset_title'], $asset['asset_mimetype'], $asset['asset_size'], $username, $target->code, $parameters->asset_id, $parameters->asset_collection);
					$module_asset_id = IMREE_asset_instantiate($asset_data_id, $parameters->module_id,  $asset['asset_title'], "", $asset['asset_metadata'], $target->code, $asset['asset_source'], $username,1,1);
					
					if($module_asset_id) {
						$str.= "<response><success>true</success><result><asset_id>".$module_asset_id."</asset_id></result></response>";
					} else {
						$str.= "<response><success>false</success><error>Asset failed to import.</error></response>";
					}
				} else {
					$str.= "<response><success>false</success><error>No such repository or you do not have access to that repostory.</error></response>";
				}
				
			}
		}
		
		
		
    } else if($command === "exhibits") {
            $device = new imree_device($command_parameter);
            $q = "SELECT * FROM exhibits WHERE exhibit_date_start < NOW() AND exhibit_date_end > NOW() ";
            if($device->device_mode === imree_device::DEVICE_MODE_KIOSK) {
                $q .= " AND exhibit_is_kiosk = '1' ";
            } else if($device->device_mode === imree_device::DEVICE_MODE_IMREE_PAD) {
                $q .= " AND exhibit_is_tablet = '1' ";
            } else {
                $q .= " AND exhibit_is_public = '1' ";
            }
            $results = db_query($conn, $q);
            $str .= "<response><success>true</success>\n<result>".children($results)."</result></response>";
           
	    
	    
    } else if($command === "exhibit_data") {
	    if($command_parameter) {
			require_once('exhibit_data.php');
		     $results = exhibit_data($command_parameter);		
			$str .= "\n<response>\n\t<success>true</success>\n\t<result>\n".array_to_xml($results, true, 2)."\t</result>\n</response>";
	    } else {
		    $str .= "<response><success>false</success>\n<error>command_parameter not set. To list a specific exhibit, we need to know which exhibit you're looking for. If you want to list all exhibits, use command=exhibits</error></response>";
	    }
        
	    
	    
    } else if($command === "login") {
	    $values = json_decode($command_parameter);
	    $ulogin = new uLogin();
            
	    $ulogin->Authenticate($values->username, $values->password);
	    if($ulogin->AuthResult) {
		    $str .= "<response><success>true</success>\n<result><logged_in>true</logged_in>";
		    $id =  $ulogin->Uid($values->username);
		    
		    is_logged_in(true, $session_key);
                    
		    $user = db_query($conn, "SELECT * FROM people WHERE people.ul_user_id = ".db_escape($id));
		    $str .= "<user>".array_to_xml($user[0], true, 2)."</user>";
		    
		    
		    
		    $person = new imree_person(imree_person_id_from_ul_user_id($id));
		    $str .= "<permissions>";
				foreach($person->privileges as $p) {
					$str .= "<item><name>".$p->name."</name><value>".$p->value."</value><scope>".$p->scope."</scope></item>";
				}
		    $str .= "</permissions>
			    </result>
			 </response>";
	    } else {
		    $str .= "<response><success>true</success>\n<result><logged_in>false</logged_in></result></response>";
	    }
        
	    
	    
    } else if($command === "user_rights") {
	    if(quick_auth()) {
		    $user = new imree_person(imree_person_id_from_username($username));
		    $str .= "<response><success>true</success>\n
			    <result>";
		    foreach($user->privileges as $p) {
			     $str .= "<item><right>".$p->name."</right><value>".$p->value."</value><scope>".$p->scope."</scope></item>";
		    }
		    $str.= "
			    </result>
			 </response>";
		}
		
		
		
    } else if($command === "user_can") {
	    if(quick_auth()) {
		    $user = new imree_person(imree_person_id_from_username($username));
		    $values = json_decode($command_parameter);
		    if($user->can($values->name, $values->value, $values->scope)) {
			    $str .= "<response><success>true</success><result>true</result></response>";
		    } else {
			    $str .= "<response><success>true</success><result>false</result></response>";
		    }
	    }
	    
	    
	    
    } else if($command === "edit_user") {
	    if(quick_auth()) {
		    $user = new imree_person(imree_person_id_from_username($username));
		    $values = json_decode($command_parameter);
		    $person = new imree_person($values->person_id);
		    $can = false;
		    foreach($person->groups as $group) {
			    if($user->can("group", "edit", $group['people_group_id'])) {
				    $can = true;
			    }
		    }
		    if($can) {
			    $array = array();
			    if(isset($values->person_name_first)) {
                                $array['person_name_first'] = $values->person_name_first;
                            }
			    if(isset($values->person_name_last)) {
                                $array['person_name_last'] = $values->person_name_last;
                            }
			    if(isset($values->person_title)) {
                                $array['person_title'] = $values->person_title;
                            }
			    if(count($array)) {
				    db_exec($conn, build_update_query($conn, "people", $array, "person_id = ".  db_escape($values->person_id)));
			    }
			    if(isset($values->primary_group) AND $values->primary_group > 0) {
				    $person->add_to_group($values->primary_group);
			    }
			    $str .= "<response><success>true</success><result>true</result></response>";
		    } else {
			    $str .= "<response><success>false</success><error>Permission Denied</error></response>";
		    }
	    }
	    
	    
	    
    } else if($command === "add_user") { 
	    if(quick_auth()) {
		    $user = new imree_person(imree_person_id_from_username($username));
		    $values = json_decode($command_parameter);
		    $group_id = $values->primary_group;
		    $can = false;
		    if($user->can("group","edit",$group_id)) {
			    $new_person = imree_create_user($values->new_username, $values->new_password, $values->person_name_last, $values->person_name_first, $values->person_title, 0);
			    if($new_person) {
					$new_person->add_to_group($group_id);
					$str .= "<response><success>true</success><result>true</result></response>";
			    } else {
					$person = new imree_person(imree_person_id_from_username($values->new_username));
					if($person) {
						   $person->add_to_group($group_id);
					} else {
						$str .= "<response><success>false</success><error>problem creating user from username, maybe a duplicate user.</error></response>";
					}
			    }
		    } else {
			    $str .= "<response><success>false</success><error>Permission Denied</error></response>";
		    }
	    }
	    
	    
	    
    } else if($command === "add_user_privilege") {
	    if(quick_auth()) {
		    $user = new imree_person(imree_person_id_from_username($username));
		    $values = json_decode($command_parameter);
		    $person = new imree_person($values->person_id);
		    $has_rights_over_person = false;
		    foreach($person->groups as $group) {
			    if($user->can("group", "edit", $group['people_group_id'])) {
				    $has_rights_over_person = true;
			    }
		    }
		    
		    if($has_rights_over_person) {
			    $has_equal_or_higher_rights = $user->can($values->people_privilege_name, $values->people_privilege_value, $values->people_privilege_scope);
			    if($has_equal_or_higher_rights) {
				    if($person->add_privilege($values->people_privilege_name, $values->people_privilege_value, $values->people_privilege_scope)) {
					    $str .= "<response><success>true</success><result>true</result></response>";
				    } else {
					    $str .= "<response><success>false</success><error>Failed to add new privilege. You have all the rights to do it, but something's gone wrong.</error></response>";
				    }
			    } else {
				    $str .= "<response><success>false</success><error>Permission Denied. You cannot elevate to this permission.</error></response>";
			    }
		    } else {
			    $str .= "<response><success>false</success><error>Permission Denied. You have no rights to this person.</error></response>";
		    }
	    }
	    
	    
	    
    } else if($command === "remove_user_privilege") {
	     if(quick_auth()) {
		    $user = new imree_person(imree_person_id_from_username($username));
		    $values = json_decode($command_parameter);
		    $person = new imree_person($values->person_id);
		    $has_rights_over_person = false;
		    foreach($person->groups as $group) {
			    if($user->can("group", "EDIT", $group['people_group_id'])) {
				    $has_rights_over_person = true;
			    }
		    }
		    
		    if($has_rights_over_person) {
			    $has_equal_or_higher_rights = $user->can($values->people_privilege_name, $values->people_privilege_value, $values->people_privilege_scope);
			    if($has_equal_or_higher_rights) {
				    if($person->remove_privilege($values->people_privilege_name, $values->people_privilege_value, $values->people_privilege_scope)) {
					    $str .= "<response><success>true</success><result>true</result></response>";
				    } else {
					    $str .= "<response><success>false</success><error>Failed to remove privilege. You have all the rights to do it, but something's gone wrong.</error></response>";
				    }
			    } else {
				    $str .= "<response><success>false</success><error>Permission Denied. You cannot edit this person.</error></response>";
			    }
		    } else {
			    $str .= $msg_permission_denied;
		    }
	    }
	    
	    
	    
    } else if ($command === "new_group") {
	    if(quick_auth()) {
		    $user = new imree_person(imree_person_id_from_username($username));
		    $values = json_decode($command_parameter);
		    if($user->can("group","ADMIN","")) {
			    imree_group_new($values->people_group_name, $values->people_group_description, $user->person_id);
		    } else {
			     $str .= $msg_permission_denied;
		    }
	    }
	    
	    
    
    /** Query Replacements from f_data */
    } else if($command === "query_fdata_DynamicOptions") {
	    if(quick_auth()) {
			$user = new imree_person(imree_person_id_from_username($username));
			$values = json_decode($command_parameter);
			if($user->can("exhibit","USR","")) {
				if(is_alphanumeric($values)) {
					$results = db_query($conn, "SELECT ".$values->label_column.", ".$values->key_column." FROM ".$values->table);
					$str .= "<response><success>true</success>\n<result>".children($results)."</result></response>";
				} else {
					 $str .= "<response><success>false</success><error>Syntax Error, illegal character in values</error></response>";
				}
			} else {
				$str .= $msg_permission_denied;
			}
	    }
	    
	    
	    
    } else if($command === "query_data_get_row") {
	    if(quick_auth()) {
			$user = new imree_person(imree_person_id_from_username($username));
			$values = json_decode($command_parameter);
			if($user->can("exhibit","USR","")) {
				if(is_alphanumeric($values)) {
					$results = db_query($conn, "SELECT * FROM ".$values->table." WHERE ".$values->table_key_column_name." = ".  db_escape($values->row_id).";");
					$str .= "<response><success>true</success>\n<result>".children($results)."</result></response>";
				} else {
					 $str .= "<response><success>false</success><error>Syntax Error, illegal character in values</error></response>";
				}
			} else {
				$str .= $msg_permission_denied;
			}
	    }
	    
	    
	    
    } else if($command === "module_asset_update") {
	    if(quick_auth()) {
		   
			$user = new imree_person(imree_person_id_from_username($username));
			$values = json_decode($command_parameter);
			if(isset($values->module_asset_id)) {
				$exhibit_id = imree_asset_get_exhibit_id($values->module_asset_id);
				if($user->can("exhibit", "EDIT", $exhibit_id)) {
					$array = array(
						'module_asset_title' => $values->module_asset_title,
						'caption' => $values->caption,
						'description' => $values->description,
						'module_asset_display_date_start' => $values->module_asset_display_date_start,
						'module_asset_display_date_end' => $values->module_asset_display_date_end,
						'thumb_display_columns' => $values->thumb_display_columns,
						'thumb_display_rows' => $values->thumb_display_rows,
					);
					
					$result = db_exec($conn, build_update_query($conn, 'module_assets', $array, " module_asset_id = ".  db_escape($values->module_asset_id)));
					 
					if($result !== false) {
						imree_error_log(("here"));
						$str .= "<response><success>true</success>\n<result>1</result></response>";
					} else {
						$str .= "<response><success>false</success><error>Failed to update asset data. You have all the rights to do it, but something's gone wrong.</error></response>";
					}
				} else {
					$str .= $msg_permission_denied;
				}
			} else {
				$str .= "<response><success>false</success><error>module_asset_id must be included in query</error></response>";
			}
			
	    }
	    
	    
    } else if($command === "new_module") {
	    if(quick_auth()) {
		    $user = new imree_person(imree_person_id_from_username($username));
			$values = json_decode($command_parameter);
			if($user->can("exhibit","USR","")) {
				
				$arr = array(
				    'module_order' => $values->module_order,
				    'module_name' => $values->module_name,
				    'module_parent_id' => $values->module_parent_id,
				    'module_type' => $values->module_type,
				);
				if(isset($values->exhibit_id)) {
					$arr['exhibit_id'] = $values->exhibit_id;
				}
					   
				$results = db_exec($conn, build_insert_query($conn, 'modules', $arr));
				$str .= "<response><success>true</success>\n<result></result></response>";
			} else {
				$str .= $msg_permission_denied;
			}
	    }
	    
	    
	    
    } else if($command === "change_module_order") {
	    //requires module_order, module_id
	    if(quick_auth()) {
		     $user = new imree_person(imree_person_id_from_username($username));
			$values = json_decode($command_parameter);
			$exhibit_id = imree_module_get_exhibit_id($values->module_id);
			if($exhibit_id) {
				if($user->can("exhibit", "edit", $exhibit_id)) {
					$result = db_exec($conn, "UPDATE modules SET module_order = ".db_escape($values->module_order)." WHERE module_id = ".db_escape($values->module_id));
					$str .= "<response><success>true</success>\n<result></result></response>";
				} else {
					$str .= $msg_permission_denied;
				}
			} else {
				$str .= "<response><success>false</success><error>Data Error. values['module_id'] does not resolve to an exhibit.</error></response>";
			}
	    }
	    
	    
	    
    } else if($command === "change_module_asset_order") {
	    //requires module_asset_id, module_asset_order
	    if(quick_auth()) {
		     $user = new imree_person(imree_person_id_from_username($username));
			$values = json_decode($command_parameter);
			$exhibit_id = imree_asset_get_exhibit_id($values->module_asset_id);
			if($exhibit_id) {
				if($user->can("exhibit", "edit", $exhibit_id)) {
					$result = db_exec($conn, "UPDATE module_assets SET module_asset_order = ".db_escape($values->module_asset_order)." WHERE module_asset_id = ".db_escape($values->module_asset_id));
					$str .= "<response><success>true</success>\n<result></result></response>";
				} else {
					$str .= $msg_permission_denied;
				}
			} else {
				$str .= "<response><success>false</success><error>Data Error. values['module_asset_id'] (".db_escape($values->module_asset_id).") does not resolve to an exhibit.</error></response>";
			}
	    }
	    
	    
	    
    } else if($command === "remove_module") {
	//requires module_id or module_asset_id 
	if(quick_auth()) {
            $user = new imree_person(imree_person_id_from_username($username));
            $values = json_decode($command_parameter);
            if (isset($values->module_asset_id)) {
                $exhibit_id = imree_asset_get_exhibit_id($values->module_asset_id);
                if ($exhibit_id) {
                    if ($user->can("exhibit", "edit", $exhibit_id)) {
                        $result = db_exec($conn, "DELETE FROM module_assets WHERE module_asset_id = " . db_escape($values->module_asset_id));
                        $str .= "<response><success>true</success>\n<result></result></response>";
                    } else {
                        $str .= $msg_permission_denied;
                    }
                } else {
                    $str .= "<response><success>false</success><error>Data Error. values['module_asset_id'] (" . db_escape($values->module_asset_id) . ") does not resolve to an exhibit.</error></response>";
                }
            } else if (isset($values->module_id)) {
                
                $exhibit_id = imree_module_get_exhibit_id($values->module_id);
                if ($exhibit_id) {
                    if ($user->can("exhibit", "edit", $exhibit_id)) {
                        $result = db_exec($conn, "DELETE FROM modules WHERE module_id = " . db_escape($values->module_id));
                        $str .= "<response><success>true</success>\n<result></result></response>";
                    } else {
                        $str .= $msg_permission_denied;
                    }
                } else {
                    $str .= "<response><success>false</success><error>Data Error. values['module_id'] does not resolve to an exhibit.</error></response>";
                }
            } else {
               
                $str .= "<response><success>false</success><error>Command remove_module requires either module_id or module_asset_id</error></response>";
            }
        }
	    
	    
	    
    } else if ($command === "upload") {
	    if(quick_auth()) {
			$user = new imree_person(imree_person_id_from_username($username));
			$values = json_decode($command_parameter);
			if(isset($values->module_id)) {
				$exhibit_id = imree_module_get_exhibit_id($values->module_id);
				if($user->can("exhibit", "EDIT", $exhibit_id)) {
					if($_FILES['Filedata']['error'] === UPLOAD_ERR_OK && is_uploaded_file($_FILES['Filedata']['tmp_name'])) {
						$asset_id = IMREE_asset_ingest(file_get_contents($_FILES['Filedata']['tmp_name']), "Untitled snapshot", null, $_FILES['Filedata']['size'], $user->username, '0', '0');
						$module_asset_id = IMREE_asset_instantiate($asset_id, $values->module_id, "Untitled snapshot", "", "No description", '0', "", $user->username);
						$str.= "<response><success>true</success><result><asset_id>".$module_asset_id."</asset_id></result></response>";
						imree_error_log("Uploaded new file", $_SERVER['REMOTE_ADDR']);
					} else {
						$str .= "<response><success>false</success><error>Error uploading File: ".$_FILES['Filedata']['error']."</error></response>";
					}
					
				} else {
					$str .= $msg_permission_denied;
				}
			} else {
				$str .= "<response><success>false</success><error>Command upload requires module_id</error></response>";
			}
	    }
    } else if ($command === "upload_bytes") {
	    
	    if(quick_auth()) {
			$user = new imree_person(imree_person_id_from_username($username));
			if(isset($_POST['module_id'])) {
				$exhibit_id = imree_module_get_exhibit_id($_POST['module_id']);
				if($user->can("exhibit", "EDIT", $exhibit_id)) {					
					if($_FILES['Filedata']['error'] === UPLOAD_ERR_OK && is_uploaded_file($_FILES['Filedata']['tmp_name'])) {
                                                $data = file_get_contents($_FILES['Filedata']['tmp_name']);
                                                $finfo = new finfo(FILEINFO_MIME_TYPE);
                                                $mimetype = $finfo->buffer($data);
                                                if($mimetype === "application/zip") {
                                                    $dir = "../temp/zipfolder";
                                                    if(file_exists($dir)) {
                                                        foreach (scandir($dir) as $item) {
                                                            if ($item == '.' || $item == '..') continue;
                                                            unlink($dir.DIRECTORY_SEPARATOR.$item);
                                                        }
                                                        rmdir($dir);
                                                        mkdir($dir);
                                                    } else {
                                                        mkdir($dir);
                                                    }
                                                    
                                                    $zip = new ZipArchive();
                                                    $zip->open($_FILES['Filedata']['tmp_name']);
                                                    $zip->extractTo($dir);
                                                    $str = "<response><success>true</success><result>";
                                                    foreach(scandir($dir) as $item) {
                                                        if ($item == '.' || $item == '..') continue;
                                                        $asset_id = IMREE_asset_ingest(file_get_contents($dir."/".$item), "Untitled", null, filesize($dir."/".$item), $user->username, '0', '0');
                                                        $module_asset_id = IMREE_asset_instantiate($asset_id, $_POST['module_id'], "Untitled", "", "No description", '0', "", $user->username);
                                                        $str .= "<asset_id>$module_asset_id</asset_id>";
                                                    }
                                                    $str .= "</result></response>";
                                                    $zip->close();
                                                } else {
                                                    $asset_id = IMREE_asset_ingest($data, "Untitled", null, $_FILES['Filedata']['size'], $user->username, '0', '0');
                                                    $module_asset_id = IMREE_asset_instantiate($asset_id, $_POST['module_id'], "Untitled snapshot", "", "No description", '0', "", $user->username);
                                                    $str.= "<response><success>true</success><result><asset_id>".$module_asset_id."</asset_id></result></response>";
                                                
                                                    if(isset($_POST['module_asset_id']))
                                                    {   //Build asset relation. Used when adding image to audio asset.
                                                        $mod_asset_A = $_POST['module_asset_id'];
                                                        $has_relation = db_query($conn, "SELECT * FROM module_asset_relations WHERE module_asset_A_id = ".db_escape($mod_asset_A));
                                                        if($has_relation)
                                                        {
                                                            $relation_update_query  = build_update_query($conn, 'module_asset_relations', Array('module_asset_B_id' => $module_asset_id), "module_asset_A_id = ".db_escape($mod_asset_A)." ");
                                                            db_exec($conn, $relation_update_query);
                                                        }
                                                        else
                                                        {
                                                            $relation_insert_query = build_insert_query($conn, 'module_asset_relations', Array('module_asset_A_id' => $mod_asset_A, 'module_asset_B_id' => $module_asset_id));
                                                            db_exec($conn, $relation_insert_query);
                                                        }
                                                        //Change Thumbnail URL
                                                        if($_POST['change_thumbnail'] )
                                                        {
                                                            $thumb_file_number = db_query($conn, "SELECT asset_data_id FROM module_assets WHERE module_asset_id = ".db_escape($module_asset_id));
                                                            $thumb_file_number = $thumb_file_number[0]['asset_data_id'];
                                                            $thumb_values = Array('asset_specific_thumbnail_url' => "http://imree.tcl.sc.edu/imree-php/file/".$thumb_file_number);
                                                            $thumb_where = "module_asset_id = ".db_escape($mod_asset_A)." ";
                                                            $specfic_thumbnail_update_query = build_update_query($conn, 'module_assets', $thumb_values, $thumb_where);

                                                            db_exec($conn, $specfic_thumbnail_update_query);
                                                        }
                                                    } 
                                                }
                                                
						imree_error_log("Uploaded new file", $_SERVER['REMOTE_ADDR']);
					} else {
						$str .= "<response><success>false</success><error>Error uploading File: ".$_FILES['Filedata']['error']."</error></response>";
					}
				} else {
					$str .= $msg_permission_denied;
				}
			} else {
				$str .= "<response><success>false</success><error>Command upload requires module_id</error></response>";
			}
	    }
	    
    } else if ($command === "generate_screen_grab") {
	    if(quick_auth()) {
		    global $imree_absolute_path; //keeps netbeans from whinning. 
			$user = new imree_person(imree_person_id_from_username($username));
			$values = json_decode($command_parameter);
			if(isset($values->module_asset_id)) {
				$exhibit_id = imree_asset_get_exhibit_id($values->module_asset_id);
				if($user->can("exhibit", "EDIT", $exhibit_id) && is_numeric($values->seconds)) {
					$q = "SELECT * FROM module_assets LEFT JOIN asset_data USING (asset_data_id) WHERE module_asset_id = ".db_escape($values->module_asset_id);
					$results = db_query($conn, $q);
					
					$file = fopen("../temp/video.mp4",'w');
					if($file !== false) {
						fwrite($file, $results[0]['asset_data_contents']);
						fclose($file);
						$name = random_string(20);
						exec(" ffmpeg -ss ".$values->seconds." -i ../temp/video.mp4 -frames:v 1 ../temp/$name.jpg");
						sleep(3);
						$new_file_data = file_get_contents("../temp/$name.jpg");
						$asset_id = IMREE_asset_ingest($new_file_data, "Snapshot", "image/jpeg", filesize("../temp/$name.jpg"), $user->username,'0','0');		
						$resultsb = db_exec($conn, build_update_query($conn, 'module_assets', array('asset_specific_thumbnail_url'=>$imree_absolute_path."file/".$asset_id), "module_asset_id = ".db_escape($values->module_asset_id)));
						$str.= "<response><success>true</success><result><asset_id>".$resultsb['last_id']."</asset_id></result></response>";
					} else {
						imree_error_log('command:generate_screen_grab failed to write temp file');
						$str .= "<response><success>false</success><error>Unable to create tmp file</error></response>";
					}
					
					
					
				} else {
					$str .= $msg_permission_denied;
				}
			} else {
				$str .= "<response><success>false</success><error>Command upload requires module_id</error></response>";
			}
	    }
	    
	    
    } else if($command === "module_asset_image_as_background_image") {
	     if(quick_auth()) {
			global $imree_absolute_path;
			$user = new imree_person(imree_person_id_from_username($username));
			$values = json_decode($command_parameter);
			if(isset($values->module_asset_id)) {
				$exhibit_id = imree_asset_get_exhibit_id($values->module_asset_id);
				if($user->can("exhibit", "EDIT", $exhibit_id)) {		
					$asset_query = $results = db_query($conn, "SELECT asset_data_id FROM module_assets WHERE module_asset_id = ".db_escape($values->module_asset_id));
					$asset_id = $asset_query[0]['asset_data_id'];
					db_exec($conn, build_update_query($conn, 'exhibits', array('exhibit_cover_image_url'=>$imree_absolute_path."/file/".  intval($asset_id)), " exhibit_id = ".db_escape($exhibit_id)));
					$str.= "<response><success>true</success><result></result></response>";
				} else {
					$str .= $msg_permission_denied;
				}
			} else {
				$str .= "<response><success>false</success><error>Command upload requires module_id</error></response>";
			}
	    }
	    
	    
	    
    } else if($command === "query") {
	    if(quick_auth()) {
		    $values = json_decode($command_parameter);
		    $columns = "";
		    if(!$values->columns) {
			    foreach($values->columns as $column) {
				    $columns .= $column . ", ";
			    }
			    $columns = substr($columns, 0, -2);
		    } else {
			    $columns = "*";
		    }

		    if(isset($values->where)) {
			    $results = db_query($conn, "SELECT $columns FROM ".$values->table." WHERE ".$values->where);
			   // error_log( "SELECT $columns FROM ".$values->table." WHERE ".$values->where);
		    } else if(isset($values->table_key_column_name, $values->row_id)) {
			    $results = db_query($conn, "SELECT $columns FROM ".$values->table." WHERE ".$values->table_key_column_name." = ".db_escape($values->row_id));
			   // error_log("SELECT $columns FROM ".$values->table." WHERE ".$values->table_key_column_name." = ".db_escape($values->row_id));
		    }
		    $str .= "<response><success>true</success>\n<result>".children($results)."</result></response>";
	    } 
	    
	    
	    
    } else if($command === "update") {
	    if(quick_auth()) {
		    $user = new imree_person(imree_person_id_from_username($username));
		    $clean = true;
		    $values = json_decode($command_parameter);
		    $set = "";
		    foreach($values->columns as $key=>$val) {
			    if(!is_alphanumeric((string) $key)) {
				    $clean = false;			    
				    
			    }
			    $set .= " $key = ".db_escape($val).", ";
		    }
		    if(isset($values->where_key_column, $values->row_id)) {
			    if(is_alphanumeric(array($values->table, $values->where_key_column)) AND $clean) {
					//@todo add checks here to make sure the user->can work on the specific table being updated
					$query = "UPDATE ".$values->table." SET ".substr($set, 0, -2)." WHERE ".$values->where_key_column." = ".  db_escape($values->row_id);
					db_exec($conn, $query);
					$str .= "<response><success>true</success>\n<result></result></response>";
			    } else {
				    $str .= "<response><success>false</success><error>Syntax Error, illegal character in values</error></response>";
			    }
		    } else {
			    $str .= "<response><success>false</success>\n<error>for command=update, we need a 'where_key_column' and 'row_id' value. if you are trying to insert a row of data, try the command=insert method instead.</error></response>";
		    }
	    } 
	    
	    
	    
    } else if($command === "insert") {
	    if(quick_auth()) {
		    $values = json_decode($command_parameter);
		    $set = "";
		    foreach($values->columns as $key=>$val) {
			    $set .= " $key = ".db_escape($val).", ";
		    }
		    if(isset($values->where)) {
			   $str .= "<response><success>false</success>\n<error>for command=insert, the 'where' value should not be set. if you are trying to update a row of data, try the command=update method instead.</error></response>";
		    } else {
			    $query = "INSERT INTO ".$values->table." SET ".substr($set, 0, -2);
			    db_exec($conn, $query);
			    $str .= "<response><success>true</success>\n<result></result></response>";
		    }
	    } 
	
	    
	    
	    
    /*  Device Tracking   */
    } else if ($command === "wifiPingData") {
		$values = json_decode($command_parameter);
		$device = new imree_device();
		if($device->device_mode === imree_device::DEVICE_MODE_IMREE_PAD AND $device->device_id > 0) {
			$device->track_signals($values);
			imree_log_location_calculation($device->device_id, 0, 0, 0, "Ping Received");
		} else {
			imree_log_location_calculation($device->device_id, 0, 0, 0, "Ping Received &amp; Ignored");
		}
		
		
		
    } else if($command === "device_tracking_start") {
	    if(quick_auth()) {
			$user = new imree_person(imree_person_id_from_username($username));
			$values = json_decode($command_parameter);
			if($user->can("devices", "ADMIN", "")) {
				$device = new imree_device();
				$device->start_tracking($values->device_mode, $values->device_name, $user->person_id);
				$str .= "<response><success>true</success>\n<result>1</result></response>";
			} else {
				$str .= $msg_permission_denied;
			}
	    }
	    
	    
	    
    } else if ($command === "device_tracking_stop") {
	    if(quick_auth()) {
			$user = new imree_person(imree_person_id_from_username($username));
			if($user->can("devices", "ADMIN", "")) {
				$device= new imree_device();
				$device->stop_tracking($user->person_id);
				$str .= "<response><success>true</success>\n<result>1</result></response>";
			} else {
				$str .= $msg_permission_denied;
			}
	    }
	    
	    
	    
    } else if ($command === "device_mark_location") {
	    if(quick_auth()) {
			$user = new imree_person(imree_person_id_from_username($username));
			$values = json_decode($command_parameter);
			$exhibit_id = imree_module_get_exhibit_id($values->module_id);
			if($exhibit_id) {
				if($user->can("exhibit", "EDIT", $exhibit_id)) {
					$device= new imree_device();
					$location_response = $device->mark_location($values->module_id, $values->location_name);
					if($location_response) {
						$str .= "<response><success>true</success>\n<result>$location_response</result></response>";
					} else {
						$str .= "<response><success>false</success>\n<error>Too few wifi signals. Is your wifi on? Are you running wifiReporter on this device?</error></response>";
					}

				} else {
					$str .= $msg_permission_denied;
				}
			} else {
				$str .= "<response><success>false</success>\n<error>module_id does not resolve to an exhibit_id</error></response>";
			}
	    }
	    
	    
	    
    } else if ($command === "device_unmark_location") {
	    if(quick_auth()) {
			$user = new imree_person(imree_person_id_from_username($username));
			$values = json_decode($command_parameter);
			$exhibit_id = imree_module_get_exhibit_id($values->module_id);
			if($exhibit_id) {
				if($user->can("exhibit", "EDIT", $exhibit_id)) {
					$device= new imree_device();
					imree_device_clean_locations($values->module_id);
				} else {
					$str .= $msg_permission_denied;
				}
			} else {
				$str .= "<response><success>false</success>\n<error>module_id does not resolve to an exhibit_id</error></response>";
			}
	    }
	    
	    
	    
    }else if($command === "module_location_id") {
		$location_id = imree_module_location_id($command_parameter);
		$str .= "<response><success>true</success>\n<result>$location_id</result></response>";
		
	    
    } else if ($command === "device_is_at_location") {	   
	    $device = new imree_device();
	    
	    if($device->is_tracking()) {
		    $module_id = $device->get_location();
		    if($module_id) {
			    $exhibit_id = imree_module_get_exhibit_id($module_id);
			    $top_modules = imree_module_get_top_two_modules($module_id);
			    $str .= "<response><success>true</success>\n<result><exhibit_id>$exhibit_id</exhibit_id><start_at>".$top_modules[0]."</start_at><sub_module>".$top_modules[1]."</sub_module></result></response>";
		    } else {
			    $str .= "<response><success>false</success>\n<error>Device is tracking but is not close a tracked location</error></response>";
		    }
		    
	    } else {
		    $str .= "<response><success>false</success>\n<error>tracking on this device is unavailable</error></response>";
	    }
	    
	    
	    
    } else if($command === "device_is_tracking") {
	    $device= new imree_device();
	    if($device->is_tracking()) {
		    $str .= "<response><success>true</success>\n<result>1</result></response>";
	    } else {
		    $str .= "<response><success>true</success>\n<result>0</result></response>";
	    }
	    
	
    } else if($command === "proximity_seonsor") {
            $string = $command_parameter;
            $data = array();
            for($i=0; $i<strlen($string); $i++) {
                if($string[$i] == "R") {
                    $data[] = intval(substr($string, $i+1, 3));
                    $i += 3;
                }
            }
            
            imree_error_log("prox-sensor-data: ".print_r($data,1));
	    $str .= "<response><success>true</success>\n<result>1</result></response>";
	    
    
    } else if($command === "error_log") {
	    imree_error_log($command_parameter, filter_input(INPUT_POST, "REMOTE_ADDR"));
	    $str .= "<response><success>true</success>\n<result>1</result></response>";
	    
    } else {
        die("That command does not exist");
    }
    header('Content-Type: application/xml; charset=utf-8');
} else {
    $str .= "<h1>IMREE API</h1><p>This API gets user command and command parameter to perform login or interaction with database.</p>";
    $str .= "<br><hr><h2>Command description and command parameters required:</h2><hr>";
    $str .= "<h3>Command: group</h3><p>Command parameters:</p>";
    $str .= "<ul><li>Group ID</li></ul><br>";
    $str .= "<hr><h3>Command: module(This command doesn't exist yet)</h3><br>";
    $str .= "<hr><h3>Command: signage_mode</h3><p>Command parameters:</p><p>signage device ID</p><br>";
    $str .= "<hr><h3>Command: signage_items</h3>";
    $str .= "<p>Command parameters:</p>";
    $str .= "<p>signage device ID</p><br>";
    $str .= "<hr><h3>Command: search</h3>";
    $str .= "<p>Command parameters:</p>";
    $str .= "<ul><li>Search string pattern</li></ul><br>";
    $str .= "<hr><h3>Command: ingest</h3>";
    $str .= "<p>Command parameters:</p>";
    $str .= "<ul><li>Asset ID</li><li>Asset repository</li></ul><br>";
    $str .= "<hr><h3>Command: exhibits</h3>";
    $str .= "<p>Command parameters:</p>";
    $str .= "<p>None</p><br>";
    $str .= "<hr><h3>Command: login</h3>";
    $str .= "<p>Command parameters:</p>";
    $str .= "<ul><li>Username</li><li>Password</li></ul><br>";
    $str .= "<hr><h3>Command: user_rights</h3>";
    $str .= "<p>Command parameters:</p>";
    $str .= "<p>None</p><br>";
    $str .= "<hr><h3>Command: query</h3>";
    $str .= "<p>Command parameters:</p>";
    $str .= "<ul><li>filter condition OR {column, value}</li></ul><br>";
    $str .= "<hr><h3>Command: update</h3><p>Command parameters:</p>";
    $str .= "<ul><li>all columns needed to be updated</li><li>all new values for updated columns</li></ul><br>";
    $str .= "<hr><h3>Command: insert</h3>";
    $str .= "<p>Command parameters:</p>";
    $str .= "<ul><li>all columns needed to be inserted</li><li>all new values for inserted columns</li></ul><br>";

 
  
    
}

echo $str;
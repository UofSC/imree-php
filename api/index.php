<?php


/**

 * The IMREE API
 * =============
 * 
 */
require_once('../../config.php');
$conn = db_connect();
$errors = array();
$results = array();

$msg_permission_denied = "<response><success>false</success><error>Permission Denied.</error></response>";

$str = "<?xml version='1.0' encoding='UTF-8' ?>";

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
 * @global type $conn
 * @param type $ip
 */
function DS_chirp($ip) {
	global $conn;
	db_exec($conn, "UPDATE signage_devices SET signage_device_last_chirp = '".date("Y-m-d H:i:s")."' WHERE signage_device_IP = ".  db_escape($ip));
}

function IMREE_log($ip, $module_type, $module_id) {
	//need to do some logging here for assesment feedback
}

$command = isset($_POST["command"]) ? filter_input(INPUT_POST, "command") : filter_input(INPUT_GET, "command");
$command_parameter = isset($_POST["command_parameter"]) ? filter_input(INPUT_POST, "command_parameter") : filter_input(INPUT_GET, "command_parameter");
$username = filter_input(INPUT_POST, "username");
$password = filter_input(INPUT_POST, "password");
$session_key = filter_input(INPUT_POST, "session_key"); 
//error_log($session_id);
//error_log("Command: " . $command . " Parameter: " .$command_parameter );

//add command 
if($command) {
    if($command === "group") {
        if(!$command_parameter) {
            $errors[] = "command_parameter not set. The command parameter must be set to the desired group_id.";
        } else {
            $results = db_query($conn, "
                SELECT * 
                FROM groups
                LEFT JOIN asset_group_assignments USING (group_id)
                LEFT JOIN assets ON (asset_group_assignments.asset_id = assets.asset_id)
                WHERE groups.group_id = ".  db_escape($command_parameter));
            if(count($results)) {
                $str .= "<response><success>true</success>"
                ."<result>"
                        . "<group_id>".$results[0]['group_id']."</group_id>"
                        . "<group_name>".$results[0]['group_name']."</group_name>"
                        . "<group_type>".$results[0]['group_type']."</group_type>"
                        . "<children>";
                            children($results);
                        $str .= "</children></result></response>";
                
            } else {
                $str .= "<response><success>false</success><error>no_results</error></response>";
            }
        }
        
        
    } else if($command === "module") { //previously "item"
        die("The item command doesn't exist yet. sry - management");
       
        
    } else if($command === "signage_mode") {
        $ip = $_SERVER['REMOTE_ADDR'];
        $results = db_query($conn, "SELECT * FROM signage_devices WHERE signage_device_ip = ".db_escape($ip));
        $session_key = build_session();
        if(count($results) > 0 ) { 
            $str .= "<response><success>true</success>\n<result>\n<key>".htmlspecialchars($session_key)."</key>\n<signage_mode>signage</signage_mode>\n</result></response>";
		  DS_chirp($ip);
        } else {
            $str .= "<response><success>true</success>\n<result>\n<key>".htmlspecialchars($session_key)."</key>\n<signage_mode>imree</signage_mode>\n</result></response>";
        }
        
          
    } else if($command === "signage_items") {
        $ip = $_SERVER['REMOTE_ADDR'];
        DS_chirp($ip);
        $results = db_query($conn, "
         SELECT * FROM signage_devices
        LEFT JOIN signage_feed_device_assignments USING (signage_device_id)
        LEFT JOIN signage_feeds USING (signage_feed_id)
        WHERE signage_devices.signage_device_IP = ".db_escape($ip));
        $str .= "<response><success>true</success>\n<result>".children($results)."</result></response>";

    } else if($command === "search") {
        if(!$command_parameter) {
             $errors[] = "command_parameter not set. The command parameter must be set to the desired search term.";
        } else {
          
		require_once 'contentDM_ingest.php';
		require_once 'razuna_ingest.php';
		set_time_limit(90);
		$CDM_results = CDM_INGEST_query($command_parameter);
		$raz_results = razuna_query($command_parameter);
		array_splice($raz_results, 20);
		$results = array_merge($CDM_results, $raz_results);

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
	} else if($command === "ingest") {
		if(quick_auth()) {
			require_once 'exhibit_data.php';
			if(!$command_parameter) {
				$errors[] = "command_parameter not set. The command parameter must be a json encoded object with three nodes: asset_repository, asset_id, asset_collection.";
			} else {
				$parameters = json_decode($command_parameter);
				if(!isset($parameters->asset_id, $parameters->asset_repository)) {
					$errors[] = "Invalid command_parameter for command:ingest. The command parameter must be a json encoded object with three nodes: asset_repository, asset_id, asset_collection.";
				} 
			}

			if(count($errors)==0) {
				$result = IMREE_asset_ingest_API_handler($parameters->asset_repository, $parameters->asset_id, $parameters->asset_collection, $parameters->module_id, $username);
				if($result) {
					$str.= "<response><success>true</success><result><asset_id>".$result."</asset_id></result></response>";
				} else {
					$str.= "<response><success>false</success><error>Asset failed to import.</error></response>";
				}
			}
		} 
	} else if($command === "exhibits") {
	    //@todo limit results by user
	    $results = db_query($conn, "SELECT * FROM exhibits");
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
			    if(isset($values->person_name_first))	$array['person_name_first'] = $values->person_name_first;
			    if(isset($values->person_name_last))	$array['person_name_last'] = $values->person_name_last;
			    if(isset($values->person_title))		$array['person_title'] = $values->person_title;
			    if(isset($values->person_department_id))	$array['person_department_id'] = $values->person_department_id;
			    if(count($array)) {
				    db_exec($conn, build_update_query($conn, "people", $array, "person_id = ".  db_escape($values->person_id)));
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
			    $new_person = imree_create_user($values->new_username, $values->new_password, $values->person_name_last, $values->person_name_first, $values->person_title, $values->person_department_id);
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
    } else if ($command === "exhibit_modules_order_update") {
	    /**
	     * @todo. command_paramater = exhitbit id. get the current result query for exhibit_data and explicitly SET the module_order to an incremental value based on the current order of the modules
	     * for that exhibit. This is designed to resolve problems where two modules have the same "order"
	     */
	} else if ($command === "exhibit_module_assets_order_update") {
		/**
		 * @todo. same as exhibit_modules_order_update, but for assets instead
		 */
	} else if ($command === "wifiPingData") {
		$values = json_decode($command_parameter);
		$signals = imree_location_process_json_to_signals($values);
		imree_location_process_signals($signals);
<<<<<<< HEAD

    }  else {

=======
		
    }  else {
	    
>>>>>>> 9aad2b90455d33f5ee3db161bbc919f44acbf807
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
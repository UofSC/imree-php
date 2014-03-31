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
        if(count($results) > 0 ) {
            $str .= "<response><success>true</success>\n<result>\n<signage_mode>signage</signage_mode>\n</result></response>";
		  DS_chirp($ip);
        } else {
            $str .= "<response><success>true</success>\n<result>\n<signage_mode>imree</signage_mode>\n</result></response>";
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
		set_time_limit(90);
		$CDM_results = CDM_INGEST_query($command_parameter);
		
		
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
				 ".children($CDM_results)." 
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
				$result = IMREE_asset_ingest($parameters->asset_repository, $parameters->asset_id, $parameters->asset_collection);
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
		    
		    $user = $permissions = db_query($conn, "SELECT * FROM people WHERE people.ul_user_id = ".db_escape($id));
		    $str .= "<user>".array_to_xml($user[0], true, 2)."</user>";
		    
		    $permissions = db_query($conn, "SELECT people_privileges.* FROM people LEFT JOIN people_privileges USING (person_id) WHERE people.ul_user_id = ".db_escape($id));
		    $str .= "<permissions>".array_to_xml($permissions, true, 2)."</permissions>";
		    
		    $str .= "</result></response>";
		    
	    } else {
		    $str .= "<response><success>true</success>\n<result><logged_in>false</logged_in></result></response>";
	    }
        
    } else if($command === "user_rights") {
	    if(quick_auth()) {
		    $str .= "<response><success>true</success>\n
			    <result>
				   <item><right>system_admin</right></item>
			    </result>
			 </response>";
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
		    $values = json_decode($command_parameter);
		    $set = "";
		    foreach($values->columns as $key=>$val) {
			    $set .= " $key = ".db_escape($val).", ";
		    }
		    if(isset($values->where)) {
			    $query = "UPDATE ".$values->table." SET ".substr($set, 0, -2)." WHERE ".$values->where;
			    db_exec($conn, $query);
			    $str .= "<response><success>true</success>\n<result></result></response>";
		    } else {
			    $str .= "<response><success>false</success>\n<error>for command=update, we need a 'where' value. if you are trying to insert a row of data, try the command=insert method instead.</error></response>";
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
	    
    }  else {
        die("That command does not exist");
    }
    
} else {
    $str .= "<h1>IMREE API</h1><p>This API gets user command and command parameter to perform login or interaction with database.</p>";
    $str .= "<br><hr><h2>Command description and command parameters required:</h2><hr>";
    $str .= "<h3>Command: group</h3><p>Command parameters:</p>";
    $str .= "<ul><li>Group ID</li></ul><br>";
    $str .= "<hr><h3>Command: item(This command doesn't exist yet)</h3><br>";
    $str .= "<hr><h3>Command: signage_mode</h3><p>Command parameters:</p><p>None</p><br>";
    $str .= "<hr><h3>Command: signage_items</h3>";
    $str .= "<p>Command parameters:</p>";
    $str .= "<p>None</p><br>";
    $str .= "<hr><h3>Command: search</h3>";
    $str .= "<p>Command parameters:</p>";
    $str .= "<ul><li>Item Name</li></ul><br>";
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
    $str .= "<ul><li>Column names</li><li>Table name</li><li>Table key column name</li><li>Row ID</li></ul><br><hr>";  
}

echo $str;

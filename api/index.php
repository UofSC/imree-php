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


function children($results) {
    $string = "";
	foreach($results as $item) {
        $string .= "<item>";
        foreach($item as $key=>$val) {
            $string .= "<".  htmlspecialchars($key).">".htmlspecialchars($val)."</".htmlspecialchars($key).">";
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

function DS_chirp($ip) {
	global $conn;
	db_exec($conn, "UPDATE signage_devices SET signage_device_last_chirp = '".date("Y-m-d H:i:s")."' WHERE signage_device_IP = ".  db_escape($ip));
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
                $str .= "<response><success>false</success></response>";
            }
        }
        
        
    } else if($command === "item") {
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
            //@todo: call contentDM and razuna search items once those gizmos are written
             $results = db_query($conn, "SELECT assets.* FROM assets
                LEFT JOIN asset_metadata_assignments USING (asset_id)
                LEFT JOIN metadata USING (metadata_id)
                WHERE MATCH(metadata.metadata_value) AGAINST (".db_escape($command_parameter).")
                GROUP BY assets.asset_id"
             );
              $str .= "<response><success>true</success><result>
              <children>
                        ".children($results)." 
              </children></result></response>";
        }
    
    } else if($command === "exhibits") {
	    $results = db_query($conn, "SELECT * FROM exhibits");
	    $str .= "<response><success>true</success>\n<result>".children($results)."</result></response>";
        
    } else if($command === "login") {
	    $values = json_decode($command_parameter);
	    $ulogin = new uLogin();
	    
	    $ulogin->Authenticate($values->username, $values->password);
	    $str .= "<response><success>true</success>\n<result>".($ulogin->AuthResult ? $ulogin->AuthResult : 'false')."</result></response>";
        
    } else if($command === "user_rights") {
	    if(quick_aut()) {
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
		    $results = db_query($conn, "SELECT $columns FROM ".$values->table." WHERE ".$values->table_key_column_name." = ".db_escape($values->row_id));
		    $str .= "<response><success>true</success>\n<result>".children($results)."</result></response>";
	    }
    } else {
        die("That command does not exist");
    }
    
} else {
    $str .= "<h1>IMREE API</h1><p>This API is not yet documented.</p>";
}

echo $str;
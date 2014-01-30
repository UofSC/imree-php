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

echo "<?xml version='1.0' encoding='UTF-8' ?>";


function children($results) {
    foreach($results as $item) {
        echo "<item>";
        foreach($item as $key=>$val) {
            echo "<".  htmlspecialchars($key).">".htmlspecialchars($val)."</".htmlspecialchars($key).">";
        }
        echo "</item>";
    }
}

$command = isset($_POST["command"]) ? filter_input(INPUT_POST, "command") : filter_input(INPUT_GET, "command");
$command_parameter = isset($_POST["command_parameter"]) ? filter_input(INPUT_POST, "command_parameter") : filter_input(INPUT_GET, "command_parameter");
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
                echo "<response><success>true</success>"
                ."<result>"
                        . "<group_id>".$results[0]['group_id']."</group_id>"
                        . "<group_name>".$results[0]['group_name']."</group_name>"
                        . "<group_type>".$results[0]['group_type']."</group_type>"
                        . "<children>";
                            children($results);
                        echo "</children></result></response>";
                
            } else {
                echo "<response><success>false</success></response>";
            }
        }
    } else if($command === "item") {
        die("The item command doesn't exist yet. sry - management");
    } else if($command === "signage_mode") {
        $ip = $_SERVER['REMOTE_ADDR'];
        $results = db_query($conn, "SELECT * FROM signage_devices WHERE signage_device_ip = ".db_escape($ip));
        error_log(print_r($results, 1));
        if(count($results) > 0 ) {
            echo "<response><success>true</success>\n<result>\n<signage_mode>signage</signage_mode>\n</result></response>";
        } else {
            echo "<response><success>true</success>\n<result>\n<signage_mode>imree</signage_mode>\n</result></response>";
        }
    } else if($command === "signage_items") {
        $results = db_query($conn, "
            SELECT * FROM signage_feeds
            LEFT JOIN signage_feed_device_assignments USING (signage_device_id)
            LEFT JOIN signage_feeds USING (signage_feed_id)
            WHERE signage_feed_id.signage_device_IP = ".db_escape($_SERVER['REMOTE_ADDR']));
        echo "<response><success>true</success>\n<result>".children($results)."</result></response>";
    } else {
        die("That command does not exist");
    }
    
} else {
    echo "<h1>IMREE API</h1><p>This API is not yet documented.</p>";
}
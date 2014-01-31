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

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";

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
                echo "<response>true</response>
                    <result>
                        <group_id>".$results[0]['group_id']."</group_id>
                        <group_name>".$results[0]['group_name']."</group_name>
                        <group_type>".$results[0]['group_type']."</group_type>
                        <children>
                            ";
                            foreach($results as $item) {
                                echo "\n\t\t<item>";
                                foreach($item as $key=>$val) {
                                    echo "\n\t\t\t<".  htmlspecialchars($key).">".htmlspecialchars($val)."</".htmlspecialchars($key).">";
                                }
                                echo "\n\t\t</item>";
                            }
                        echo " 
                        </children>
                </result>"
                ;
            } else {
                echo "<response>false</response>";
            }
        }
    } else if($command === "item") {
        die("The item command doesn't exist yet. sry - management");
    } else if($command === "signage_mode") {
        $ip = $_SERVER['REMOTE_ADDR'];
        $results = db_query($conn, "SELECT * FROM signage_devices WHERE signage_device_ip = ".db_escape($ip));
        if(count($results) > 0 ) {
            echo "<response>true</response><result><signage_mode>signage</signage_mode></result>";
        } else {
            echo "<response>true</response><result><signage_mode>imree</signage_mode></result>";
        }
    } else {
        die("That command does not exist");
    }
    
} else {
    echo "<h1>IMREE API</h1><p>This API is not yet documented.</p>";
}

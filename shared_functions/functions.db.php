<?php
/**
 * This file contains the functions used for working with the database
 * @author Craig Spurrier <craig@hawaiihosting.com>
 * @package shared_functions
 * @copyright Craig Spurrier 2010
 * @license The MIT License http://opensource.org/comment/935
 */
     
/**
* This function establishes a connection to a database. If unable to connect it will die with a fatal error.
* @author Craig Spurrier
* @version 1.9 Nov 22 2010 11:01EDT 
* @param bool $reuse (TRUE|FALSE) Should we try to reuse an already open connection (named $db) or always make a new one
* @global string $cfg_db_type
* @global string $cfg_db_host
* @global string $cfg_db_name
* @global string $cfg_db_username
* @global string $cfg_db_password
* @return object
*/
function db_connect($reuse=TRUE) {
    if($reuse AND isset($GLOBALS['db'])){
        return $GLOBALS['db'];
    }else{
        global $cfg_db_type,$cfg_db_host,$cfg_db_name,$cfg_db_username,$cfg_db_password; //These variables should be defined before this function is called, generally in the config.php file or similar
        try{
            $pdo = new PDO($cfg_db_type.':host='.$cfg_db_host.';dbname='.$cfg_db_name.';', $cfg_db_username, $cfg_db_password); //Establishes a new connection to the server
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        }catch(PDOException $error){ //If any errors occurred while attempting to connect, they will be displayed 
            echo "<div id='mysql_error' class='error_msg'>Error connecting to Server: ".$error->getMessage(),"</div>";
            exit();
        }
    }
}

/**
* This function runs a query against a database and return results as an array.
* @author Craig Spurrier
* @version 1.6 Nov 24 2010 17:15EDT
* @param object $conn The object that the database connection is stored in
* @param string $query The query that is to be run. Queries MUST be escaped before this function is called!
* @param bool $show_query (FALSE|TRUE) Should the query be shown on the screen. Useful for debugging
* @return array Returns the result of the query
*/
function db_query($conn,$query,$show_query=FALSE) {
    if($show_query){// If $show_query is true, display the query on the screen
        echo "<br/>Query:$query<br/>";
    } 
    try{
        $results = Array(); // Create an array to store the results
        foreach ($conn->query($query) as $row) {
            $new_row = Array();
            foreach ($row as $key=>$value) {
                if (!is_numeric($key)){ //Store only named results
                    $new_row[$key] = $value;
                }
            }
            $results[] = $new_row; //Copy the results to the array
        }
        return $results;
    }catch(PDOException $error){ //If any errors occurred while running the query, they will be displayed 
        echo "<div id='mysql_error' class='error_msg'>Database error: ".$error->getMessage(),"</div>";
        exit();
    }
}

/**
* This function runs a query against database when changes will be made 
* @author Craig Spurrier
* @version 1.6 Nov 1 2010 18:15EDT
* @param object $conn The object that the database connection is stored in
* @param string $query The query that is to be run. Queries MUST be escaped before this function is called!
* @param bool $show_query (FALSE|TRUE) Should the query be shown on the screen. Useful for debugging
* @return array Returns the id of the last row changed (last_id) and the number of rows changed (rows_changed)
*/

function db_exec($conn,$query,$show_query=FALSE) {
    global $cfg_db_type;

    if($show_query){// If $show_query is true, display the query on the screen
        echo "<br/>Query:$query<br/>";
    } 
    try{
        $return = Array(); // Create an array to store the results 
        $return['rows_changed'] = $conn->exec($query); // Execute the query. This also returns the number of rows changed so we save this to the array 
        if($cfg_db_type =='mysql'){
        $return['last_id'] = $conn->lastInsertId();  // Add the id of the last row inserted to the array also
        }
        return $return;
    }catch(PDOException $error){ //If any errors occurred while running the query, they will be displayed 
        echo "<div id='mysql_error' class='error_msg'>Database error: ".$error->getMessage(),"</div>";
        exit();
    }
}


/**
* This function escapes data for the database.
* @author Craig Spurrier
* @version 1.6 Nov 1 2010 18:15EDT
* @param string $data The data to be escaped
* @param object $conn OPTIONAL: The object that the database connection is stored in 
* @return string Returns escaped data
*/
function db_escape($data,$conn = NULL) {
    global $db;
    if(is_null($conn) AND !is_null($db)){ //If no connection is supplied try $db 
        $conn = $db;
    }elseif(is_null($conn)){ //If no connection is supplied and $db does not exist, create a new connection
        $conn = db_connect();
    }
    return $conn->quote($data); // Escape and return the data
}


/**
* This function closes the connection to the database. It is rarely required as PHP should destroy the object when it is done anyways.
* @author Craig Spurrier
* @version 1.3 Nov 1 2010 18:15EDT
* @param object $conn The object that the database connection is stored in 
*/
function db_close($conn) {
    $conn = NULL;
}

/**
* This function generates an update query based upon the data supplied
* @author Craig Spurrier
* @version 1.4 Nov 1 2010 18:15EDT
* @param object $conn The object that the database connection is stored in 
* @param string $table The table to be effected by this query
* @param array $values The values the make up the query in an array with the name of the field as the key and the value a the value
* @param string $where_clause The WHERE clause that limits what is updated. MUST BE ESCAPED BEFORE SENT TO THIS FUNCTION
* @param string $output_format (return|echo) What this function should do with the query it generates, (echo) display it on the screen, or (return) return it for further use
* @return string Returns a ready to use update query
*/
function build_update_query($conn, $table, $values, $where_clause, $output_format='return'){
    $query = "UPDATE $table SET";
    $fields_to_update = Array();
    foreach ($values AS $name => $value){
        $fields_to_update[] = " $name = ".db_escape($value,$conn); 
    }
    $query .= join($fields_to_update, ', ');
    $query .= " WHERE $where_clause";
    
    if($output_format == 'echo'){
        echo $query;
    }else{
        return $query;
    }
}

/**
* This function generates an insert query based upon the data supplied
* @author Craig Spurrier
* @version 1.4 Nov 1 2010 18:15EDT
* @param object $conn The object that the database connection is stored in 
* @param string $table The table to be effected by this query
* @param array $values The values the make up the query in an array with the name of the field as the key and the value a the value
* @param string $output_format (return|echo) What this function should do with the query it generates, (echo) display it on the screen, or (return) return it for further use
* @return string Returns a ready to use insert query
*/

function build_insert_query($conn, $table, $values, $output_format='return'){
    $query = "INSERT INTO $table (";
    foreach ($values AS $name => $value){
        $field_names[] = $name; 
        if(is_array($value)){
            $value = $value[0];
        }
        $field_values[] = db_escape($value,$conn); 
    }
    $query .= join($field_names, ', ');
    $query .= ") VALUES (";
    $query .= join($field_values, ', ');
    $query .= ");";
    
    if($output_format == 'echo'){
        echo $query;
    }else{
        return $query;
    }
}

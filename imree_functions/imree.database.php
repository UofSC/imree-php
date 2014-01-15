<?php

/* 
 * MySQL database functions
 * This file handles connections, queries, executions, and steralization 
 *  
 */


/**
 * Connect to the mysql database and return the connection object. The dsn, 
 * username, and password MUST BE defined before calling this function. 
 * 
 * @global string $imree_database_dsn i.e. mysql:host=localhost;dbname=testdb
 * @global string $imree_database_password
 * @global string $imree_database_username
 * @return PDO an active pdo connection
 */
function imree_db_connect() {
    global $imree_database_dsn, $imree_database_password, $imree_database_username, $conn;
    if(!isset($conn)){ //checks for existsing connection
        try{

            $conn = new PDO($imree_database_dsn, $imree_database_password, $imree_database_username);

            return $conn;
        }catch (PDOException $e) { //displays error message
           echo 'Connection Failed: ' . $e->getMessage(); 
        } 
    }else{
        return $conn;
    }
}
/**
 * Runs a query string and returns the results in an Array
 * 
 * @param type $conn
 * @param type $query_string
 * @return type $result - An array of the results of the query
 *                        or false, if an exception is caught
 */
function imree_db_query($conn, $query_string, $show_error=false) {
    $conn = imree_db_connect();
    $result = Array();
    try{ //attempts to run the query on the database
        foreach($conn->query($query_string) as $row_num){
            $row = Array();
                foreach($row_num as $key => $data){ 
                    $row[$key] = $data;
                }
            $result[] = $row; 
        }
        return $result;
    }catch (PDOException $e){ //displays error message and/or returns false
        if($show_error){
            echo 'Error: ' . $e->getMessage();
            return false;
        }else{
            return false;    
        }    
    }
}




function imree_db_exec($conn, $exec_string) {
    
    //@todo run exec_string against conn, return result
    
}

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
            $conn = PDO($imree_database_dsn, $imree_database_password, $imree_database_username)
            return $conn;
        }catch (PDOException $e) { //catches exception and returns error message
           echo 'Connection Failed: ' . $e->getMessage(); 
        } 
    }else{
        return $conn;
    }
}




function imree_db_query($conn, $query_string) {
    
    //@todo runn query_string against conn. return an array of fetch_all as the result
    
}




function imree_db_exec($conn, $exec_string) {
    
    //@todo run exec_string against conn, return result
    
}

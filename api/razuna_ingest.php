<?php

/** RAZUNA_INGEST
 *  This file searches the RAZUNA repository for CURATORS and pulls back data related to their SEARCH QUERY.
 *  CURATORS select the desired components they would like to bring to the IMREE DB and then they submit to 
 *  INGEST this information into the IMREE MYSQL DB.
 */

//require functions and other NICE STOOF
#require_once('../../config.php');
#$conn = db_connect();
#$errors = array();
#$results = array();
        
#NOTE TO SELF: Get DB stuff working again locally
// put your code here
        
//0. Function Display Form
echo "<form action='razuna_ingest.php' method='get'>";
echo "<p>Your name: <input type='text' name='name' /></p>";
echo "<p>Your age: <input type='text' name='age' /></p>";
echo "<p><input type='submit' /></p>";
echo "</form>";

//1.Function Search Razuna

//2.Function Display Results

//3.Function Submit Selected info to IMREE DB


?>
    
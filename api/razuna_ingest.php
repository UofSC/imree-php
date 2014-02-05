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
#IN THE MEAN TIME USE THE CODE BELOW
#
require_once('/../shared_functions/functions.api.php');
require_once('/../shared_functions/functions.core.php');
require_once('/../shared_functions/functions.db.php');
#require_once('/../shared_functions/functions.catalog.php');
require_once('/../shared_functions/functions.form.php');

// put your code here
 global $search;       
//0. Function Display Form

    if(form_submitted())
    {//form has been submitted perform the appropriate checks 
    
        
    } 
     else 
    {//form has not been submitted
      //Create an option to show an advanced search
        if ($search == "ADV") //not sure if I will implement advanced and simple search ... talk to JSON about this
        {

        }
        else 
        //Default show the user the simple search
        {

        }
    }
    
    
//1.Function Search Razuna

//2.Function Display Results

//3.Function Submit Selected info to IMREE DB

    //Check the DB to see if the records exist if not add them

    //return success or any errors

?>
    
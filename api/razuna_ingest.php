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

 $target = htmlspecialchars($_SERVER["PHP_SELF"]);
 $search_limit = array("img" => "image only", "vid" => "video only","doc" => "document only","aud" => "audio only");
 #$query_string="";
 
    if(form_submitted())
    {//form has been submitted perform the appropriate checks 
        //build the file url from the search query
        $base="http://imree.tcl.sc.edu:8080/razuna/global/api2/search.cfc?method=searchassets";
        $api="&api_key=822756B3669444D59D2C2333E449FFBA";
        $q_string= "&searchfor=".$_POST["query_string"];       
        
        if (!isset($_POST["show_ass"])) //check to see if any search limiters have been set and add them to the file url
        {
            $show_ass="";
        }
        else
        {
            $show_ass="&show=".$_POST["show_ass"];
        }
        
        $url =  $base.$api.$q_string.$show_ass; //create the url to pass to the "function"
        
        echo $_POST["query_string"]; //testing ps
        print "\n<br>";
        
        if (isset($_POST["show_ass"])) //testing ps
        {   echo $_POST["show_ass"];
        }
        else {
            echo "O show_ass is a no show.";
        }
        
        print "\n<br>";
        echo $url; //testing ps
        
        $contents = file_get_contents($url); //get the contents of the search from razuna
        $contents = utf8_encode($contents);  //encode them
        $results = json_decode($contents,true); //pass the results to json for nifty array handling
        print "\n<br>";
        print_r ($results); //testing ps
        
        //to do
        //print records in a human readable form
        //select records
        //ingest records to IMREE DB
    } 
     else 
    {   //form has not been submitted
        //create a input field to type the search query
        echo "<form method='post' action='$target'>";
        echo "Simple Search";
        print "\n";
        f_input("query_string","text","");
        print "\n<br>";
        echo "Limit Search";
        print "\n";
        f_input("show_ass","radio", $search_limit);
        print "\n(Default search is all types)";
        print "\n<br>";
        f_input("SUMBIT","submit");
        echo "</form>";
      
//Create an option to show an advanced search
      /*  if ($search == "ADV") //not sure if I will implement advanced and simple search ... talk to JSON about this
        {

        }
        else 
        //Default show the user the simple search
        {

        }*/
    }
    
    
//1.Function Search Razuna

//2.Function Display Results

//3.Function Submit Selected info to IMREE DB

    //Check the DB to see if the records exist if not add them

    //return success or any errors

?>
    
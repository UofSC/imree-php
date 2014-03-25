<?php
/** RAZUNA_INGEST
 *  This file searches the RAZUNA repository for CURATORS and pulls back data related to their SEARCH QUERY.
 *  CURATORS select the desired components they would like to bring to the IMREE DB and then they submit to 
 *  INGEST this information into the IMREE MYSQL DB.
 */

#require the shared functions and other useful content for the api
#NOTE TO SELF: Get DB stuff working again locally
#IN THE MEAN TIME USE THE CODE BELOW
#
require_once('/../shared_functions/functions.api.php');
require_once('/../shared_functions/functions.core.php');
require_once('/../shared_functions/functions.db.php');
require_once('/../shared_functions/functions.form.php');
#require_once('/../shared_functions/functions.catalog.php');
#require_once('../../config.php');

// put your code here
 #global $search;       

 $target = htmlspecialchars($_SERVER["PHP_SELF"]);
 $search_limit = array("img" => "image only", "vid" => "video only","doc" => "document only","aud" => "audio only");
 
    if(form_submitted())
    {
        //if the form has been submitted (see if statement above) perform the functions below 
        //For each step see the functions below
        //1. Replace white space in the search query with %20
        //2. Prepare the QUERY STRING portion of the url 
        //3. Build the file url using the BASE var, the API Key, the QUERY STRING AND any search limiters ($show_ass)   
        //4. Pass the url to JSON AND CREATE THE RAZUNA array
        //5. Convert the RAZUNA ARRAY to the data model of the new IMREE array, return the final array to AIR
       
        return_array(filter_input(INPUT_POST, 'query_string'));
        
    } 
     else 
    {   //form has not been submitted
        //create an input field to type the search query
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
      
    }
    /* START WITH YOUR FUNCTIONS DOWN HERE */
    
    //this function adds spaces to the RAZUNA QUERY SO THAT THE RAZUNA API WILL NOT RETURN AN ERROR WHEN IT PERFORMS A SEARCH
    //it accepts a string argument and returns it
    function add_spaces($str)
    {
        $str = str_replace(' ', '%20', $str);
        return $str;
    }
    
    //this function creates a QUERY STRING TO SUBMIT TO RAZUNA
    //it accepts string arguments
    function create_qstring($str)
    {
        $str ="&searchfor=".add_spaces($str); //this func call replaces white space with %20 and builds search string for passing to RAZUNA
        return $str;
    }

    //this function creates the URL by adding in the BASE_URL STRING, RAZUNA API KEY STRING, the QUERY STRING, AND ANY RAZUNA SEARCH PARAMETERS TO SUBMIT TO RAZUNA
    //it accepts string arguments and returns the url variable
    function create_url($str)
    {
        //first check to see if air passed any search limiters        
        if (!isset($_POST["show_ass"])) //check to see if any search limiters have been set and add them to the file url
        {
            $show_ass=""; //if POST show_asset variable is blank set it to blank here so it will add nothing to the URL below
        }
        else
        {
            $show_ass="&show=".filter_input(INPUT_POST, 'show_ass'); //if POST show_asset variable is not blank then assign it to the $show ass variable along with syntax to retrieve assets of a specific type from RAZUNA
        }
        
        $base="http://imree.tcl.sc.edu:8080/razuna/global/api2/search.cfc?method=searchassets";
        $api="&api_key=822756B3669444D59D2C2333E449FFBA";
        $url =  $base.$api.create_qstring($str).$show_ass; //this func call 1. replaces white space with %20, 2. builds the query and three 3. adds the query string to the url
        return $url;
    }
    
    function pass_url($str)
    {
        $contents = file_get_contents(create_url($str)); //this func call performs functions 1-3 above and gets the contents of the search from razuna
        $encoded = utf8_encode($contents);  //encode them
        $results = json_decode($encoded,true); //pass the results to json for nifty array handling
        
        #print_r ($results);
        return $results; //returns a JSON array of search results from RAZUNA
    }
    
    function return_array ($results)
    {
        $array = pass_url($results);
        
        $curator_array=array(); //create a blank array to put the results into a form for the curator interface
        
        foreach ($array["DATA"] as $item)
            {
            $item_array= array();
            
            $item_array['id'] = $item[0];
            $item_array['title'] = $item[1];
            $item_array['thumbnail_url'] = $item[20];
            $item_array['repository'] = "Razuna";
            $item_array['format'] = $item[7]."/".$item[4];
            if (!$item[17]=="" AND !$item[16]=="")
            {$item_array['data']= "KEYWORDS: ". $item[17]." DESCRIPTION: ".$item[16];}
            elseif (!$item[17]=="" AND $item[16]=="")
            {$item_array['data']= "KEYWORDS: ". $item[17];}
            elseif ($item[17]=="" AND !$item[16]=="")
            {$item_array['data']= "DESCRIPTION: ".$item[16];}
            else 
            {$item_array['data']= "";}    
            
            
            $curator_array[]=$item_array;
            }
            
        print_r ($curator_array);
        return $curator_array;
    }
    
?>
    
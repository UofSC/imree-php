<?php
/** RAZUNA_INGEST
 *  This file searches the RAZUNA repository for CURATORS and pulls back data related to their SEARCH QUERY.
 *  CURATORS select the desired components they would like to bring to the IMREE DB and then they submit to 
 *  INGEST this information into the IMREE MYSQL DB.
 * @author Ron Brown 
 */

#require the shared functions and other useful content for the api
#NOTE TO SELF: Get DB stuff working again locally
#IN THE MEAN TIME USE THE CODE BELOW
#



 function razuna_query($query) {
	 /**
	  * The following two vars where found between functions, but it doesn't appear they're used. 
	  */
	  $target = htmlspecialchars($_SERVER["PHP_SELF"]);
	  $search_limit = array("img" => "image only", "vid" => "video only","doc" => "document only","aud" => "audio only");
 
        //if the form has been submitted (see if statement above) perform the functions below 
        //For each step see the functions below
        //1. Replace white space in the search query with %20
        //2. Prepare the QUERY STRING portion of the url 
        //3. Build the file url using the BASE var, the API Key, the QUERY STRING AND any search limiters ($show_ass)   
        //4. Pass the url to JSON AND CREATE THE RAZUNA array
        //5. Convert the RAZUNA ARRAY to the data model of the new IMREE array, return the final array to AIR
       
        return return_array($query);
        
    } 
    /* START WITH YOUR FUNCTIONS DOWN HERE */
    
    //this function adds spaces to the RAZUNA QUERY SO THAT THE RAZUNA API WILL NOT RETURN AN ERROR WHEN IT PERFORMS A SEARCH
    //it accepts a string argument and returns it
    function razuna_add_spaces($str)
    {
        $str = str_replace(' ', '%20', $str);
        return $str;
    }
    
    //this function creates a QUERY STRING TO SUBMIT TO RAZUNA
    //it accepts string arguments
    function razuna_create_qstring($str)
    {
        $str ="&searchfor=".razuna_add_spaces($str); //this func call replaces white space with %20 and builds search string for passing to RAZUNA
        return $str;
    }

    //this function creates the URL by adding in the BASE_URL STRING, RAZUNA API KEY STRING, the QUERY STRING, AND ANY RAZUNA SEARCH PARAMETERS TO SUBMIT TO RAZUNA
    //it accepts string arguments and returns the url variable
    function razuna_create_url($str)
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
        $url =  $base.$api.razuna_create_qstring($str).$show_ass; //this func call 1. replaces white space with %20, 2. builds the query and three 3. adds the query string to the url
        return $url;
    }
    
    function razuna_pass_url($str)
    {
        $contents = file_get_contents(razuna_create_url($str)); //this func call performs functions 1-3 above and gets the contents of the search from razuna
        $encoded = utf8_encode($contents);  //encode them
        $results = json_decode($encoded,true); //pass the results to json for nifty array handling
        
        //print_r ($results);
        return $results; //returns a JSON array of search results from RAZUNA
    }
    
    function return_array ($results)
    {
        $array = razuna_pass_url($results);
        
        $curator_array=array(); //create a blank array to put the results into a form for the curator interface
        
        foreach ($array["DATA"] as $item)
            {
            $item_array= array();
            
            $item_array['id'] = $item[0];
            $item_array['collection'] = "";
            $item_array['title'] = $item[1];
            $item_array['thumbnail_url'] = $item[20];
            $item_array['repository'] = "Razuna";
		  $mimepart1 = $item[7];
		  if(strtolower($mimepart1) === "img") {
			  $mimepart1 = "image";
		  }
            $item_array['type'] = $mimepart1."/".$item[4];
            if (!$item[17]=="" AND !$item[16]=="")
            {$item_array['metadata']= "KEYWORDS: ". $item[17]." DESCRIPTION: ".$item[16];}
            elseif (!$item[17]=="" AND $item[16]=="")
            {$item_array['metadata']= "KEYWORDS: ". $item[17];}
            elseif ($item[17]=="" AND !$item[16]=="")
            {$item_array['metadata']= "DESCRIPTION: ".$item[16];}
            else 
            {$item_array['metadata']= "";}    
            $item_array['children']="";
            
            
            $curator_array[]=$item_array;
            }
            
        //print_r ($curator_array); print the array for testing
            
        return $curator_array;
    }
    
    function razuna_ingest ($asset_id)  
    {
        //Retrieving a razuna asset requires two inputs
        //1. Razuna API key 
        //2. Razuna asset id
        
        //Note:: Razuna asset type -- Note the asset type field may not be accurate. Check this.
        //create the url this function uses search assets because get assets requires the asset type and this is assumed to not be known by the function
        
        $base="http://imree.tcl.sc.edu:8080/razuna/global/api2/search.cfc?method=searchassets";
        $api="&api_key=822756B3669444D59D2C2333E449FFBA";
        $url =  $base.$api."&searchfor=labels:(assetid),(".$asset_id.")";
                
        //pass the url to Razuna
        $contents = file_get_contents($url); //this line passes the ingest parameters to retrieve the specific asset
        $encoded = utf8_encode($contents);  //encode them
        $results = json_decode($encoded,true); //pass the results to json for nifty array handling
       
        //Parse through the URL 
	   $item = $results['DATA'][0];     
		$item_array= array();

		//get the asset_data information by combining the keyword and description information present from Razuna
		if (!$item[16]=="" AND !$item[15]=="")
		    {$item_array['asset_metadata']= "KEYWORDS: ". $item[16]." DESCRIPTION: ".$item[15];}
		elseif (!$item[16]=="" AND $item[15]=="")
		    {$item_array['asset_metadata']= "KEYWORDS: ". $item[16];}
		elseif ($item[16]=="" AND !$item[15]=="")
		    {$item_array['asset_metadata']= "DESCRIPTION: ".$item[15];}
		else 
		    {$item_array['asset_metadata']= "";}    

		$item_array['asset_title'] = razuna_get_metadata($asset_id, $item[7]); //need to run another query to get the title information?
		$item_array['asset_source'] = ""; 
		$item_array['asset_mimetype']=$item[7]; 
		$item_array['asset_size']=$item[12]; 
		$item_array['asset_data']=  file_get_contents($item[19]);
        
        return $item_array;
    }
    
    function razuna_get_metadata ($asset_id, $asset_type)
    {
        $base="http://imree.tcl.sc.edu:8080/razuna/global/api2/asset.cfc?method=getmetadata";
        $api="&api_key=822756B3669444D59D2C2333E449FFBA";
        $a_id="&assetid=".$asset_id;
        $a_type="&assettype=".$asset_type;
        $a_metadata="&assetmetadata=title";
        $url =  $base.$api.$a_id.$a_type.$a_metadata;
                
        //pass the url to Razuna
        $contents = file_get_contents($url); //this line passes the ingest parameters to retrieve the specific asset
        $encoded = utf8_encode($contents);  //encode them
        $results = json_decode($encoded,true); //pass the results to json for nifty array handling
        
        foreach ($results["DATA"] as $item)
            {
            $razuna_metadata= array(); 
            $razuna_metadata = $item[0]; //need to run another query to get the title information?
            }
        return $razuna_metadata;
    }

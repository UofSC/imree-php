<?php
/** RAZUNA_INGEST
 *  This file searches the RAZUNA repository for CURATORS and pulls back data related to their SEARCH QUERY.
 *  CURATORS select the desired components they would like to bring to the IMREE DB and then they submit to 
 *  INGEST this information into the IMREE MYSQL DB.
 * @author Ron Brown 
 */

 function razuna_query($query, $api_url, $api_url_supplemental, $api_key, $limit=50, $start=0, $limit_by_asset_type=false) {
	 
	//For each step see the functions below
	//1. Replace white space in the search query with %20
	//2. Prepare the QUERY STRING portion of the url 
	//3. Build the file url using the BASE var, the API Key, the QUERY STRING AND any search limiters ($show_ass)   
	//4. Pass the url to JSON AND CREATE THE RAZUNA array
	//5. Convert the RAZUNA ARRAY to the data model of the new IMREE array, return the final array to AIR


	$url = $api_url . "/search.cfc?method=searchassets&api_key=" . $api_key . "&searchfor=" . urlencode($query);
	if($limit_by_asset_type) {
	    $url .= "&show=" . $limit_by_asset_type; 
	}
	$contents = file_get_contents($url); //this func call performs functions 1-3 above and gets the contents of the search from razuna
	$encoded = utf8_encode($contents);  //encode them
	$array = json_decode($encoded,true); //pass the results to json for nifty array handling
	
	$target = $array['DATA'];
	if(count($target) < $start) {
		return array();
	}
	if(count($target) > $limit) {
		$target = array_slice($target, $start, $limit);
	}
	$curator_array=array(); //create a blank array to put the results into a form for the curator interface
	
	foreach ($target as $item) {
	    $item_array= array();

	    $item_array['id'] = $item[0];
	    $item_array['collection'] = "";
	    $item_array['title'] = $item[1];
	    $item_array['thumbnail_url'] = $item[20];
	    $item_array['repository'] = "Razuna"; //overriding this with the source id once the array is passed back
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

	return $curator_array;
 } 
    
function razuna_ingest($asset_id, $handle, $api_url, $api_url_supplemental, $api_key) {
        $handle = $handle; //Razuna does not use a handle aka alias aka collection_id
	   
	    //Retrieving a razuna asset requires two inputs
        //1. Razuna API key 
        //2. Razuna asset id
        
	   //Note:: Razuna asset type -- Note the asset type field may not be accurate. Check this.
        //create the url this function uses search assets because get assets requires the asset type and this is assumed to not be known by the function
        
        $url = $api_url . "/search.cfc?method=searchassets&api_key=" . $api_key . "&searchfor=labels:(assetid),(".$asset_id.")";
        
        //pass the url to Razuna
        $contents = file_get_contents($url); //this line passes the ingest parameters to retrieve the specific asset
        $encoded = utf8_encode($contents);  //encode them
        $results = json_decode($encoded,true); //pass the results to json for nifty array handling
       
        //Parse through the URL 
	   $item = $results['DATA'][0];     
		$item_array= array();

		//get the asset_data information by combining the keyword and description information present from Razuna
		$item_array['asset_metadata'] = "";
		if($item[16] !== "") {
			$item_array['asset_metadata'] .= "KEYWORDS: ". $item[16];
		}
		if($item[15] !== "") {
			$item_array['asset_metadata'] .= "DESCRIPTION: ".$item[15];
		}

		$item_array['asset_title'] = razuna_get_item_title($asset_id, $item[7], $api_url, $api_key); //need to run another query to get the title information!
		$item_array['asset_source'] = ""; 
		$item_array['asset_mimetype']=$item[7]; 
		$item_array['asset_size']=$item[12]; 
		$item_array['asset_data']=  file_get_contents($item[19]);
        
        return $item_array;
    }
    
    function razuna_get_item_title($asset_id, $asset_type, $api_url, $api_key) {
        $url = $api_url . "/asset.cfc?method=getmetadata&api_key=" . $api_key . "&assetid=" . $asset_id . "&assettype=".$asset_type . "&assetmetadata=title";

        //pass the url to Razuna
        $contents = file_get_contents($url); //this line passes the ingest parameters to retrieve the specific asset
        $encoded = utf8_encode($contents);  //encode them
        $results = json_decode($encoded,true); //pass the results to json for nifty array handling
        
        return $results['DATA'][0][0];
    }

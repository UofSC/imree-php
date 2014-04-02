<?php

function IMREE_asset_ingest($data, $title, $mimetype, $size, $username, $source_repo='', $source_id='', $source_collection='') {
	if($mimetype === null OR $mimetype === "" OR $mimetype instanceof SimpleXMLElement OR strpos($mimetype, "/") === false) {
		$finfo = new finfo(FILEINFO_MIME_TYPE);
		$mimetype = $finfo->buffer($data);
	}
	$conn = db_connect();
	$result = db_exec($conn, build_insert_query($conn, "asset_data", array(
	    'asset_data_title' => $title,
	    'asset_data_type' => $mimetype,
	    'asset_data_contents' => $data,
	    'asset_data_contents_date' => date("Y-m-d H:i:s"),
	    'asset_data_date_added' => date("Y-m-d H:i:s"),
	    'asset_data_size' => $size,
	    'asset_data_username' => $username,
	    'asset_data_source_repository' => $source_repo,
	    'asset_data_source_asset_id' => $source_id, 
	    'asset_data_source_collection_handle' => $source_collection,
	)));
	if($result) {
		return $result['last_id'];
	} else {
		return false;
	}
}

function IMREE_asset_instantiate($asset_data_id, $module_id, $title, $caption, $description, $source_repository, $original_url, $username, $thumb_display_columns = 1, $thumb_display_rows = 1) {
	$conn = db_connect();
	$result = db_exec($conn, build_insert_query($conn, 'module_assets', array(
	    'module_id' => $module_id, 
	    'asset_data_id' => $asset_data_id,
	    'module_asset_title' => $title,
	    'caption' => $caption,
	    'description' => $description,
	    'original_url' => $original_url,
	    'source_repository' => $source_repository,
	    'username' => $username,
	    'thumb_display_columns' => $thumb_display_columns,
	    'thumb_display_rows' => $thumb_display_rows,
	)));
	if($result) {
		return $result['last_id'];
	} else {
		return false;
	}
	
}

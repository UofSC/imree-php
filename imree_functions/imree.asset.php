<?php

/* 
 * @package imree-php
 * This file contains the functions needed to import and export assets into the 
 * DAM
 */

/**
 * This class should be used when manipulating or importing assets. It sacrafices
 * memory and speed for completeness and features
 */
class imree_asset {
    
    public $data_column1;
    public $data_column2;
    //etc...
    
    public function __construct($id = null) {
        if ($id) {
            load($id);
        } 
    }   
    
    public function load($id) {
        //query database for data
        //then, manually set $data_column1 = $result['data_column1'];
    }
    
    public function create() {
        //save all metadata back to mysql
        //save media to $imree_assets_directory/$asset_id/
        //save xml version of metadata to media directory 
        //generate thumbnail for media and save to media directory
        //generate openzoom image and save to media directory
    }
    
    
    public function update_metadata() {
        //update mysql data for asset_id
    }
    
    public function update_media() {
        //regenerate thumbnail, openzoom data, etc... & save to media directory
    }
    
    public function destroy() {
        //delete metadata entry and media data
    }
    
}

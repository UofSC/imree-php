<?php


class imree_group {
    public $group_name;
    public $children;
    public $type;
    
    public function __construct($id = null) {
        if($id) {
            $this->load($id);
        }
    }
    
    public function load($id) {
        //get group data from database where row_id = $id
    }
    
    public function add_child($child) {
        //update stack and save()
    }
    
    public function remove_child($child) {
        //update stack and save()
    }
    
    public function save() {
        //save groupdata to database
        //save each child's update data (if the order changed, etc...
    }
    
}
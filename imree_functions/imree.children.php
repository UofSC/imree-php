<?php

/**
 * This class holds the properties of assets when they're added to group.
 * When in a group, assets are called children and can have properties that 
 * determine how they interact with the other children in the group
 *
 * Other groupings can also be treated as a child to create different navigational
 * pathways.
 * 
 * @author Jason Steelman
 */

class imree_children {
    
    public $child_type;
    public $child_order;
    public $child_block_size;
    public $child_asset_id;
    public $child_on_select;
    //many many more properties will need to be added over time
    
    public function __construct($id = null) {
        if($id) {
            load($id);
        }
    }
    
    public function load($id) {
        
    }
    
    public function save() {
        
    }
    
    public function destroy() {
        
    }
    
}

<?php

/**
 * @file Handles People and Permissions
 * 
 */

class imree_person {
	public $person_id;
	public $person_name_last;
	public $person_name_first;
	public $person_title;
	public $person_depearment_id;
	public $ul_user_id;
	public $username;
	public $groups;
	public $privileges;
	public $super_admin;
	private $conn;
	public function __construct($id) {
		$this->conn = db_connect();
		$results = db_query($this->conn, "
			SELECT * FROM people WHERE person_id = ".db_escape($id));
		if(count($results)) {
			$person = $results[0];
			$this->person_id = $id;
			$this->person_name_last = $person['person_name_last'];
			$this->person_name_first = $person['person_name_first'];
			$this->person_title = $person['person_title'];
			$this->person_depearment_id = $person['person_department_id'];
			$this->ul_user_id = $person['ul_user_id'];
			
			$this->get_username();
			$this->update_groups();
			$this->update_privileges();
		} 
	}
	
	private function get_username() {
		if($this->ul_user_id>0) {
			$ulogin=new uLogin();
			$this->username = $ulogin->Username($this->ul_user_id);
		}
	}
	private function update_groups() {
		$groups_arr = db_query($this->conn, "SELECT * FROM people
			LEFT JOIN people_group_assignments USING (person_id)
			LEFT JOIN people_group USING (people_group_id) WHERE people.person_id = ".  db_escape(($this->person_id)));
		$this->groups = array();
		foreach($groups_arr as $grp) {
			$this->groups[$grp['people_group_id']] = $grp;
		}
	}
	public function is_in_group($people_group_id) {
		return isset($this->groups[$people_group_id]);
	}
	public function add_to_group($people_group_id) {
		if(!$this->is_in_group($people_group_id)) {
			db_exec($this->conn, build_insert_query($this->conn, "people_group_assignments", array(
			    'people_group_id'=>$people_group_id,
			    'person_id'=>$this->person_id,
			)));
			$this->update_groups();
		}
	}
	public function remove_from_group($people_group_id) {
		if($this->is_in_group($people_group_id)) {
			db_exec($this->conn, "DELETE FROM people_group_assignments WHERE people_group_id = ".db_escape($people_group_id)." AND person_id = ".db_escape($this->person_id));
		}
		$this->update_groups();
	}
	
	public function can($privilege_name, $privilege_value, $privilege_scope) {
		if($this->super_admin) {
			return true;
		}
		foreach($this->privileges as $p) {
			if($p->allow($privilege_name, $privilege_value, $privilege_scope)) {
				return true;
			}
		}
		return false;
	}
	
	public function update_privileges() {
		$results = db_query($this->conn, "SELECT * FROM people_privileges WHERE person_id = ".db_escape($this->person_id));
		$this->privileges = array();
		$this->super_admin = false;
		foreach($results as $item) {
			$this->privileges[] = new imree_privilege($item['people_privilege_name'], $item['people_privilege_value'], $item['people_privilege_scope']);
			if($item['people_privilege_name'] === 'super_admin') {
				$this->super_admin = true;
			}
		}
	}
	public function add_privilege($name, $value, $scope) {
		if($this->can($name, $value, $scope)) {
			return true;
		} else {
			$results = db_exec($this->conn, build_insert_query($this->conn, "people_privileges", array(
			    'people_privilege_name'=>$name,
			    'people_privilege_value'=>$value, 
			    'people_privilege_scope'=>$scope,
			)));
			return $results !== false;
		}
	}
	public function remove_privilege($name, $value, $scope) {
		if($this->can($name, $value, $scope)) {
			db_exec($this->conn, "DELETE FROM people_privileges WHERE people_privilege_name = ".db_escape($name)." AND people_privilege_value = ".db_escape($value)." AND people_privilege_scope = ".db_escape($scope));
		}
		return true;
	}
}

class imree_privilege {
	public $name;
	public $value;
	public $scope;
	public function __construct($name, $value, $scope) {
		$this->name = $name;
		$this->value = $value;
		$this->scope = $scope;
	}
	public function allow($name, $value, $scope) {
		if(strlen($this->name) > strlen($name)) {
			return true;
		}
		if($this->name === $name) {
			if(strlen($this->value) > strlen($value)) {
				return true;
			}
			if($this->value === $value AND $this->scope === $scope) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}		
	}
}


function imree_person_id_from_username($username) {
	$conn = db_connect();
	$ulogin = new uLogin();
	$id = $ulogin->Uid($username);
	$results = db_query($conn, "SELECT * FROM people WHERE ul_user_id = ".db_escape($id));
	if(count($results)) {
		return $results[0]['person_id'];
	} else {
		return false;
	}
}
function imree_person_id_from_ul_user_id($ul_user_id) {
	$conn = db_connect();
	$results = db_query($conn, "SELECT * FROM people WHERE ul_user_id = ".db_escape($ul_user_id));
	if(count($results)) {
		return $results[0]['person_id'];
	} else {
		return false;
	}
}




function imree_create_user($username, $password, $person_name_last, $person_name_first, $person_title, $person_department_id) {
	$ulogin = new uLogin();
	if($ulogin->CreateUser($username, $password)) {
		$uid = $ulogin->Uid($username);
		$conn = db_connect();
		$results = db_exec($conn, build_insert_query($conn, "people", array(
			'person_name_last'=>$person_name_last,
			'person_name_first' => $person_name_first,
			'person_title' => $person_title,
			'person_department_id' => $person_department_id,
			'ul_user_id' => $uid,
		)));
		if(isset($results['last_id'])) {
			return new imree_person($results['last_id']);
		} else {
			return false;
		}
	} else {
		return false; //username already exits
	}
	
}
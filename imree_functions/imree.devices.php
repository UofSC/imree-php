<?php




class imree_device {
	public $device_id;
	
	public $device_mode;
	const DEVICE_MODE_IMREE_PAD = 0;
	const DEVICE_MODE_KIOSK = 1;
	const DEVICE_MODE_NORMAL = 4;
	const DEVICE_MODE_PUBLIC_PAD = 3;
	const DEVICE_MODE_SIGNAGE = 2;
	const DEVICE_MODE_WEB = 5; 
	
	public $device_name;
	public $device_ip;
	public $device_last_chirp;
	
	public $array_of_signal_objects;
	public function __construct($device_id_or_ip = false) {
		if($device_id_or_ip === false) {
			$results =  db_query(db_connect(), "SELECT * FROM devices WHERE device_ip = ".db_escape(filter_input(INPUT_SERVER, "REMOTE_ADDR")));
		} else if(strpos($device_id_or_ip, ".")) {
			$results = db_query(db_connect(), "SELECT * FROM devices WHERE device_ip = ".db_escape($device_id_or_ip));
		} else {
		    $results = db_query(db_connect(), "SELECT * FROM devices WHERE device_id = ".db_escape($device_id_or_ip));
		}
		
		if(count($results)) {
		    $this->device_id = $results[0]['device_id'];
		    $this->device_ip = $results[0]['device_ip'];
		    $this->device_name = $results[0]['device_name'];
		    $this->device_last_chirp = $results[0]['device_last_chirp'];
		    $this->device_mode = $this->device_mode_from_sql($results[0]['device_mode']);
		    
		} else {
		    $this->device_mode = self::DEVICE_MODE_NORMAL;
		    $this->device_ip = filter_input(INPUT_SERVER, "REMOTE_ADDR");
		    $this->device_id = 0;
		    $this->device_last_chirp = date("Y-m-d H:i:s");
		    $this->device_name = "unnamed";
		}
	}
	
	private function device_mode_from_sql($device_mode) {
		if($device_mode === "kisok") {
			$result = self::DEVICE_MODE_KIOSK;
		} else if ($device_mode === 'tablet') {
			$result = self::DEVICE_MODE_IMREE_PAD;
		} else if($device_mode === 'signage') {
			$result = self::DEVICE_MODE_SIGNAGE;
		} else if($device_mode === "normal") {
			$result = self::DEVICE_MODE_NORMAL;
		} else {
			$result = self::DEVICE_MODE_WEB;
		}
		return $result;
	}
	
	public function is_tracking() {
		$results = db_query(db_connect(), "
			SELECT DISTINCT(device_signal_date_time) 
			FROM device_signals 
			WHERE 
				device_id = ".db_escape($this->device_id)." 
				AND device_signal_date_time > '".date("Y-m-d H:i:s", strtotime("-30 seconds"))."' 
			LIMIT 10");
		return count($results) > 4;
	}
	
	public function track_signals($json_str_of_signal_data_or_json_object) {
		imree_device_clean_untracked_signals();
		imree_device_clean_tracked_signals();
		if(is_object($json_str_of_signal_data_or_json_object)) {
			 $json_object = $json_str_of_signal_data_or_json_object;
		} else {
			$json_test_object = json_decode($json_str_of_signal_data_or_json_object, true);
			if(json_last_error() === JSON_ERROR_NONE) {
				 $json_object = $json_test_object;
				 unset($json_test_object);
			}
		}
		$this->array_of_signal_objects = array();
		foreach($json_object as $val) {
			$signal = new imree_device_signal($val->id, $val->level, $val->SSID, $val->timestamp);
			$signal->get_device_signal_id();
			if($signal->device_access_point_is_tracked) {
				$this->array_of_signal_objects[] = $signal;
				$signal->log($this->device_id);
			} else {
				$signal->log_as_noise($this->device_ip);
			}
		}
		db_exec(db_connect(), "UPDATE devices SET device_last_signals_chirp = '".date("Y-m-d H:i:s")."' WHERE device_id = ".db_escape($this->device_id));
	}
	
	public function start_tracking($device_mode, $device_name= "unnamed", $person_id=0) {
		$conn = db_connect();
		$data = array(
			'device_ip' => $this->device_ip,
			'device_last_chirp' => date("Y-m-d H:i:s"),
			'device_last_added_by_person_id' => $person_id,
		     'device_mode' => $device_mode,
		);
		
		if($device_name !== "unnamed") {
			$this->device_name = $device_name;
			$data['device_name'] = $device_name;
		}  else {
			$data['device_name'] = $this->device_name;
		}
		
		if($this->device_id > 0) {
			$results = db_exec($conn, build_update_query($conn, 'devices', $data, " device_id = ".db_escape($this->device_id)));
		} else {
			$results = db_exec($conn, build_insert_query($conn, 'devices', $data));
			if($results) {
				$this->device_id = $results['last_id'];
			}
		}
		return $results == true;
	}
	
	public function mark_location($location_module_id, $location_name="") {
		$signature = $this->build_new_location_signature(20);
		if(count($signature) > 2) {
			$location = new imree_device_location();
			$location->location_module_id = $location_module_id;
			$location->location_name = $location_name;
			$location->put();
			$location->add_signatures($signature);
			return $location->location_id;
		} else {
			return false;
		}
	}
	
	public function build_new_location_signature($duration_in_seconds=15) {
		$conn = db_connect();
		$untracked_query = "
				SELECT 
					AVG(device_signals_untracked_strength) AS strength, 
					device_signals_untracked_mac_address AS mac_address,
					device_signals_untracked_SSID AS SSID,
					'0' AS device_access_point_id,
					device_signals_untracked_date_time AS date_time
				FROM device_signals_untracked 
				WHERE 
					device_signals_untracked_from_ip = ".db_escape($this->device_ip)." 
					AND device_signals_untracked_date_time > '".date("Y-m-d H:i:s", strtotime("-$duration_in_seconds second"))."' 
				GROUP BY device_signals_untracked_mac_address
				ORDER BY strength DESC 
				LIMIT 20;
			";
		$untracked_signals = db_query($conn, $untracked_query);
		
		$tracked_query = "
				SELECT 
					AVG(device_signals.device_signal_strength) AS strength, 
					device_access_points.device_access_point_mac_address AS mac_address,
					device_access_points.device_access_point_SSID AS SSID,
					device_access_points.device_access_point_id AS device_access_point_id,
					device_signals.device_signal_date_time AS date_time
				FROM 
					device_signals 
					LEFT JOIN device_access_points USING (device_access_point_id)
				WHERE 
					device_id = ".db_escape($this->device_id)." 
					AND device_signal_date_time > '".date("Y-m-d H:i:s", strtotime("-$duration_in_seconds second"))."' 
				GROUP BY device_access_point_id
				ORDER BY strength DESC 
				LIMIT 20;
			";
		$tracked_signals = db_query($conn, $tracked_query);
		
		$results = array_merge($untracked_signals, $tracked_signals);
		usort($results, "imree_device_compare_strengths");
		
		
		if(count($results) > 25) {
			array_splice($results, 25);
		}
		
		$signature = array();
		foreach($results as $signal) {
			$this->access_point_tracking_start($signal['mac_address'],$signal['SSID']);
			$new_signal = new imree_device_signal($signal['mac_address'], $signal['strength'], $signal['SSID'], $signal['date_time']);
			if($new_signal->get_device_signal_id()) {
				$signature[] = $new_signal;
			}
		}
		return $signature;
		
	}
	
	public function get_location($duration=8) {
		imree_device_clean_tracked_signals();

		$results = db_query(db_connect(), "
				SELECT 
					AVG(device_signals.device_signal_strength) AS strength, 
					device_access_points.device_access_point_mac_address AS mac_address,
					device_access_points.device_access_point_SSID AS SSID,
					device_access_points.device_access_point_id AS device_access_point_id,
					device_signals.device_signal_date_time AS date_time
				FROM 
					device_signals 
					LEFT JOIN device_access_points USING (device_access_point_id)
				WHERE 
					device_id = ".db_escape($this->device_id)." 
					AND device_signal_date_time > '".date("Y-m-d H:i:s", strtotime("-$duration second"))."' 
				GROUP BY device_access_point_id
				ORDER BY strength DESC 
				LIMIT 20;
			");
		if(count($results) > 2) {
			$device_signals = array();
			foreach($results as $item) {
				$device_signals[] = new imree_device_signal($item['mac_address'], $item['strength'], $item['SSID'], $item['date_time']);
			}
			$locations = imree_device_all_locations();
			$valid_locations = array();
			foreach($locations as $local) {
				$score = imree_device_score_signatures($device_signals, $local->get_signatures());
				if($score > 5) {
					$local->scored_value = $score;
					$valid_locations[] = $local;
				}
			}
			if(count($valid_locations) === 1) {
				return $valid_locations[0]->location_module_id;
			} else if(count($valid_locations) > 1) {
				usort($valid_locations, "imree_device_locations_sort_by_score");
				return $valid_locations[0]->location_module_id;
			} else {
				return false;
			}
		} else {
			return false;
		}
		
		
		
		
	}


	public function access_point_tracking_start($mac_address, $SSID) {
		$conn = db_connect();
		$results = db_query($conn, "SELECT * FROM device_access_points WHERE device_access_point_mac_address = ".db_escape($mac_address));
		if(count($results)) {
			return $results[0]['device_access_point_id'];
		} else {
			$insert_result = db_exec($conn, build_insert_query($conn, 'device_access_points', array(
			    'device_access_point_mac_address' => $mac_address,
			    'device_access_point_SSID' => $SSID,
			    'device_access_point_last_added' => date("Y-m-d H:i:s"),
			    'device_access_point_is_tracked' => '1'
			)));
			return $insert_result['last_id'];
		}
	}
	
	public function stop_tracking($person_id=0) {
		$conn = db_connect();
		
		if($this->device_id > 0) {
			$results = db_exec($conn, build_update_query($conn, 'devices', 
				   array(
					  'device_mode' => 'normal',
					  'device_last_added_by_person_id' => $person_id,
				   ), 
				   " device_id = ".db_escape($this->device_id) ));
			return $results == true;
		} else {
			return true;
		}
	}
	
}

function imree_device_score_signatures($device_signals, $location_signals) {
	$score = 0;
	foreach($device_signals as $device_signal) {
		foreach($location_signals as $location_signal) {
			if($location_signal->mac_id == $device_signal->mac_id) {
				$tolerance = $location_signal->level / 10 + 10;
				$result = $tolerance - abs($location_signal->level - $device_signal->level);
				$score += max(array($result, -1));
			}
		}
	}
	return $score;
}

function imree_device_locations_sort_by_score($location_a, $location_b) {
	if($location_a->scored_value == $location_b->scored_value) {
		return 0;
	}
	return ($location_a->scored_value < $location_b->scored_value) ? -1 : 1;
}

function imree_device_compare_strengths($arr_a, $arr_b) {
	if($arr_a['strength'] == $arr_b['strength']) {
		return 0;
	}
	return ($arr_a['strength'] < $arr_b['strength']) ? -1 : 1;
}

function imree_device_clean_untracked_signals() {
	$conn = db_connect();
	$q = "DELETE FROM device_signals_untracked WHERE device_signals_untracked_date_time < '".date("Y-m-d H:i:s",strtotime("-2 minute"))."'";
	db_exec($conn, $q);
}

function imree_device_clean_tracked_signals() {
	$conn = db_connect();
	$q = "DELETE FROM device_signals WHERE device_signal_date_time < '".date("Y-m-d H:i:s",strtotime("-2 minute"))."'";
	db_exec($conn, $q);
}

function imree_device_clean_locations($module_id) {
	$conn = db_connect();
	$existing = db_query($conn, "SELECT * FROM device_locations WHERE device_location_module_id = ".  db_escape($module_id));
	if(count($existing)) {
		foreach($existing as $location) {
			db_exec($conn, "DELETE FROM device_location_signature WHERE device_location_id = ".  db_escape($location['device_location_id']));
		}
		db_exec($conn, "DELETE FROM device_locations WHERE device_location_module_id = ".  db_escape($module_id));
	}
}

function imree_device_all_locations() {
	$conn = db_connect();
	$results = db_query($conn, "SELECT * FROM device_locations");
	$locations = array();
	foreach($results as $item) {
		$local = new imree_device_location($item['device_location_id']);
		$local->location_module_id = $item['device_location_module_id'];
		$local->location_name = $item['device_location_name'];
		$locations[] = $local;
	}
	return $locations;
}

class imree_device_location {
	public $location_id;
	public $location_name;
	public $location_module_id;
	
	public $scored_value;
	public function __construct($id = 0) {
		if($id > 0) {
			$this->location_id = $id;
			$this->pull();
		}
	}
	public function pull() {
		$results = db_query(db_connect(), "SELECT * FROM device_locations WHERE device_location_id = ".db_escape($this->location_id));
		if(count($results)) {
			$this->location_module_id = $results[0]['device_location_module_id'];
			$this->location_name = $results[0]['device_location_name'];
			return true;
		} else {
			return false;
		}
	}
	public function put() {
		$conn = db_connect();
		if(isset($this->location_module_id)) {
			imree_device_clean_locations($this->location_module_id);
			$data = array('device_location_name' => $this->location_name, 'device_location_module_id'=>$this->location_module_id);
			if(isset($this->location_id) AND $this->location_id > 0) {
				db_exec($conn, build_update_query($conn, 'device_locations', $data, " device_location_id = ".db_escape($this->location_id)));
				return $this->location_id;
			} else {
				$results = db_exec($conn, build_insert_query($conn, 'device_locations', $data));
				$this->location_id = $results['last_id'];
				return $this->location_id;
			}
		} else {
			return false;
		}
	}
	public function add_signatures($signals) {
		$conn = db_connect();
		foreach($signals as $signal) {
			db_exec($conn, build_insert_query($conn, 'device_location_signature', array(
			    'device_location_id' => $this->location_id,
			    'device_access_point_id' => $signal->device_access_point_id,
			    'device_signal_strength' => $signal->level
			)));		
		}
	}
	public function get_signatures() {
		$conn = db_connect();
		$results = db_query($conn, "SELECT * FROM device_location_signature LEFT JOIN device_access_points USING (device_access_point_id) WHERE device_location_id = ".$this->location_id. " ORDER BY device_signal_strength DESC");
		$signals = array();
		foreach($results as $item) {
			$signals[] = new imree_device_signal($item['device_access_point_mac_address'], $item['device_signal_strength'], $item['device_access_point_SSID'], $item['device_access_point_last_added']);
		}
		return $signals;
	}
}

class imree_device_signal {
	public $mac_id;
	public $level;
	public $timestamp;
	public $SSID;
	
	public $device_access_point_id;
	public $device_access_point_is_tracked;
	public function __construct($mac_id, $level, $SSID, $timestamp) {
		$this->mac_id = $mac_id;
		$this->level = $level;
		$this->SSID = $SSID;
		$this->timestamp = $timestamp;
	}
	public function get_device_signal_id() {
		$results = db_query(db_connect(), "SELECT * FROM device_access_points WHERE device_access_point_mac_address = ".db_escape($this->mac_id));
		if(count($results)) {
			$this->device_access_point_id = $results[0]['device_access_point_id'];
			$this->device_access_point_is_tracked = $results[0]['device_access_point_is_tracked'] == 1;
			return $this->device_access_point_id;
		} else {
			return false;
		}
	}
	public function log($device_id) {
		$conn = db_connect();
		$results = db_exec($conn, build_insert_query($conn, 'device_signals', array(
		    'device_id' => $device_id,
		    'device_access_point_id' => $this->device_access_point_id,
		    'device_signal_strength' => $this->level,
		    'device_signal_date_time' => date("Y-m-d H:i:s"),
		)));
		return $results;
	}
	public function log_as_noise($ip) {
		$conn = db_connect();
		$results = db_exec($conn, build_insert_query($conn, 'device_signals_untracked', array(
		    'device_signals_untracked_SSID' => $this->SSID,
		    'device_signals_untracked_strength' => $this->level,
		    'device_signals_untracked_mac_address' => $this->mac_id,
		    'device_signals_untracked_date_time' => date("Y-m-d H:i:s"),
		    'device_signals_untracked_from_ip' => $ip,
		)));
		return $results;
	}
	public function __toString() {
		return "Signal " . $this->id . " " .$this->level . " " . $this->SSID . " " . $this->timestamp;
	}
}
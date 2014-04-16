<?php


$imree_location_APs_tracked = array(
    "ac:22:0b:d0:ae:a0",
    "00:23:eb:81:cf:50",
    "00:23:eb:80:94:71",
    "00:23:eb:81:98:12",
);

function imree_location_process_signals($signals) {
	global $imree_location_APs_tracked;
	error_log("");
	error_log("New Signals");
	foreach($signals as $signal) {
		if(in_array($signal->id, $imree_location_APs_tracked)) {
			$id = array_search($signal->id, $imree_location_APs_tracked);
			error_log($id . " @ " . $signal->level);
		}
	}
}

function imree_location_process_json_to_signals($json) {
	$arr = array();
	foreach($json as $val) {
		$arr[] = new imree_location_signal($val->id, $val->level, $val->frequency, $val->SSID, $val->timestamp);
	}
	return $arr;
}

class imree_location_signal {
	public $id;
	public $frequency;
	public $level;
	public $timestamp;
	public $SSID;
	public function __construct($id, $level, $frequency, $SSID, $timestamp) {
		$this->id = $id;
		$this->level = $level;
		$this->frequency = $frequency;
		$this->SSID = $SSID;
		$this->timestamp = $timestamp;
	}

	public function __toString() {
		return "Signal " . $this->id . " " .$this->level . " ".$this->frequency . " " . $this->SSID . " " . $this->timestamp;
	}
}
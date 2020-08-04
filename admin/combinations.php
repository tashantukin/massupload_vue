<?php

function get_combinations($arrays) {
	$result = array(array());
	foreach ($arrays as $property => $property_values) {
		$tmp = array();
		foreach ($result as $result_item) {
			foreach ($property_values as $property_value) {
				$tmp[] = array_merge($result_item, array($property => $property_value));
			}
		}
		$result = $tmp;
	}
	return $result;
}

$combinations = get_combinations(
	array(
		'item1' => array('Blue', 'Green', 'Red'),
		'item2' => array('M', 'S','L'),
		'item3' => array('A', 'B'),
	)
);

print_r($combinations);

?>
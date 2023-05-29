<?php

include_once ('../functions.php');
include_once ('../readings.php');

$date = bg_currentDate();

list($y, $m, $d) = explode('-', $date);
$y = (int)$y; 
$wd = date("N",strtotime($date));
$tone = bg_getTone($date);
$easter = bg_get_easter($y);

$dd = ($y-$y%100)/100 - ($y-$y%400)/400 - 2;
$old = date("Y-m-d",strtotime ($date.' - '.$dd.' days')) ;
list($old_y,$old_m,$old_d) = explode ('-', $old);

$data = array();
$data = bg_getData($old_y);

$desc_json = '../descriptions.json';
$descriptions = array();
if (file_exists($desc_json)) {
	$json = file_get_contents($desc_json);
	$descriptions = json_decode($json, true);
}

$today = $data[$date];
foreach($today['events'] as $key => $event) {
	$id_list = $event['id_list'];
	$today['events'][$key]['description'] = array();
	if (array_key_exists($id_list, $descriptions)) {
		$today['events'][$key]['description'] = $descriptions[$id_list];
	}
}
// Тропари и кондаки дня
$today['tropary_day'] = bg_tropary_days ($date);


$json = json_encode($today, JSON_UNESCAPED_UNICODE);

echo $json;

exit();
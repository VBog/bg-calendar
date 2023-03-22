<!-- Заголовок страницы -->
<html>
<head>
	<style>
		input[type="button"],
		summary {
			cursor: pointer;
		}
	</style>
</head>
<body>

<?php
include_once ('functions.php');
include_once ('readings.php');

/***
	Исполняемый код
***/

$weekday = ['Понедельник','Вторник','Среда','Четверг','Пятница','Суббота','<span>Воскресенье</span>'];
$monthes = ['января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря'];

$date = bg_currentDate();

list($y, $m, $d) = explode('-', $date);
$y = (int)$y; 
$wd = date("N",strtotime($date));

$dd = ($y-$y%100)/100 - ($y-$y%400)/400 - 2;
$old = date("Y-m-d",strtotime ($date.' - '.$dd.' days')) ;
list($old_y,$old_m,$old_d) = explode ('-', $old);

$data = array();
$data = bg_getData($old_y);

?>
<!-- Выбор даты -->	
	<div class="day-settings-today">
		<input id="bg_yesterdayButton" type="button" value="< Вчера">
		<input id="bg_setDay" class="bg_setDay" type="date" value="<?php echo  $date; ?>" title="Выбрать дату"> 
		<input id="bg_todayButton" type="button" value="Сегодня">
		<input id="bg_tommorowButton" type="button" value="Завтра >">
	</div>
	
	<details><summary>Данные дня</summary>
		<pre>
			<?php print_r($data[$date]); ?>
		</pre>
	</details>
	<div style="width: 400px; text-align: center;">
		<hr>	
		<img height="250" src="https://azbyka.ru/days/storage/images/<?php echo $data[$date]['icon']; ?>" title="<?php echo $data[$date]['icon_title']; ?>" />
		<h3<?php echo (($wd==7)?' style=" color:red"':""); ?>><?php echo $weekday[$wd-1].',<br>'. (int) $d .' '. $monthes[$m-1] .' '. $y .'г.'; ?><br>
		<?php echo '('.(int) $old_d .' '. $monthes[$old_m-1] .' ст.ст.)'; ?></h3>

		<h4<?php echo (($wd==7)?' style=" color:red"':""); ?>><?php echo $data[$date]['sedmica']; ?></h4>
		<p>Глас <?php echo $data[$date]['tone']; ?>, <?php echo $data[$date]['food']; ?></p>
	
<?php
// Внимание, данные с приоритетом 0 на экран не выводим
for ($i=1; $i<6; $i++) {
	$text = '';
	foreach ($data[$date]['events'] as $event) {
		$title = (in_array($event['level'], [1,8]))?('<b>'.$event['title'].'</b>'):$event['title'];
		$title = '<span'.(($event['level'] < 3)?' style=" color:red"':"").'>'.$title.'</span>';
		if ($event['priority'] == $i) $text .= '<img src="'.(($event['level'] < 7)?('symbols/S'.$event['level'].'.gif'):'').'" /> '. $title.'. ';
	}
	if ($text) echo '<p>'.$text.'</p>';
}
?>
		<hr>
		<div style="text-align:left;">
<?php

// Список чтений дня
	// Праздники
	foreach ($data[$date]['events'] as $event) {
		if ($data[$date]['day_subtype'] != 'universal_saturday') {
			if ($wd == 6 || ($event['priority'] && $event['level'] < 3 && $wd < 7)) { // Суббота или Бдение и выше
				bg_printReadings ($event['readings']);
			}
		}
	}
	// Рядовые
	foreach ($data[$date]['ordinary_readings'] as $readings) {
		bg_printReadings ($readings);
	}
	// Праздники
	foreach ($data[$date]['events'] as $event) {
		if ($data[$date]['day_subtype'] != 'universal_saturday') {
			if ($wd != 6 && $event['priority'] && !($event['level'] < 3 && $wd < 7)) { // Не суббота и Полиелей и ниже
				bg_printReadings ($event['readings']);
			}
		} else {
			if ($event['subtype'] == 'universal_saturday') { 
				bg_printReadings ($event['readings']);
			}
		}
	}
?>
		<hr>
		<h3>Тропари, кондаки, молитвы и величания</h3>
<?php
	foreach ($data[$date]['events'] as $event) {
		if (!empty($event['taks']) && !empty($event['taks'][0])) {
			$title = $event['taks'][0]['title'];
			$title = count(explode(' ',$title,2))>1?explode(' ',$title,2)[1]:'';
			echo '<details><summary>'.$title.'</summary>'.PHP_EOL;
			foreach ($event['taks'] as $tak) {
				echo '<h4>'.$tak['title'].($tak['voice']?(', глас '.$tak['voice']):'').'</h4>'.PHP_EOL;
				echo '<p>'.$tak['text'].'</p>'.PHP_EOL;
			}
			echo '</details>'.PHP_EOL;
		}
	}
?>		
		</div>
	</div>
<!-- Завершение страницы -->	
<script>
	// Получить дату из input типа date и добавить ее в параметр адресной строки
	var bg_setDay = document.getElementById("bg_setDay");
	if (bg_setDay) bg_setDay.addEventListener('change', () => setParam(true), false);

	// Установить текущую дату
	var bg_todayButton = document.getElementById("bg_todayButton");
	if (bg_todayButton) bg_todayButton.addEventListener('click',  () => setParam(false), false);

	// Установить вчерашнюю дату
	var bg_yesterdayButton = document.getElementById("bg_yesterdayButton");
	if (bg_yesterdayButton) bg_yesterdayButton.addEventListener('click',  function() {
		var date = new Date(document.getElementById("bg_setDay").value);
		date.setDate(date.getDate() - 1);
		document.getElementById("bg_setDay").value = date.getFullYear()+"-"+(("0" + (date.getMonth() + 1)).slice(-2))+"-"+(("0" + date.getDate()).slice(-2));
		setParam(true);
	}, false);

	// Установить завтрешнюю дату
	var bg_tommorowButton = document.getElementById("bg_tommorowButton");
	if (bg_tommorowButton) bg_tommorowButton.addEventListener('click',  function() {
		var date = new Date(document.getElementById("bg_setDay").value);
		date.setDate(date.getDate() + 1);
		document.getElementById("bg_setDay").value = date.getFullYear()+"-"+(("0" + (date.getMonth() + 1)).slice(-2))+"-"+(("0" + date.getDate()).slice(-2));
		setParam(true);
	}, false);

	function setParam (param=true) {
		var url=location.href;
		url=url.substring(0, url.indexOf('?')); 
		
		if (param) {
			var d = document.getElementById("bg_setDay");
			url = url+'?date='+d.value;
		}
		location.href=url;
	}
</script>

</body>
</html>
<?php
function bg_printReadings ($readings) {
	if (empty($readings)) return;
	echo '<p>'.($readings['title']?('<i>'.$readings['title'].':</i> '):'').
		($readings['morning']?('<i>Утр.:</i> '.$readings['morning'].' '):'').
		($readings['hour1']?('<i>1-й час:</i> '.$readings['hour1'].' '):'').
		($readings['hour3']?('<i>3-й час:</i> '.$readings['hour3'].' '):'').
		($readings['hour6']?('<i>6-й час:</i> '.$readings['hour6'].' '):'').
		($readings['hour9']?('<i>9-й час:</i> '.$readings['hour9'].' '):'').
		($readings['apostle']?('<i>Лит.: Ап.-</i> '.$readings['apostle'].' '):'').
		($readings['gospel']?('<i>Ев.-</i> '.$readings['gospel'].' '):'').
		($readings['evening']?('<i>Веч.:</i> '.$readings['evening'].' '):'').'</p>';
}

<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="description" content="The orthodox calendar with liturgical readings and troparion.">
<link rel="icon" type="image/gif" href="./symbols/S0.gif">

<title>Православный календарь</title>

<style>
	body {
		font-size: large;
	}
	.container {
		display: block;
		width: 420px;
		margin: 0;
		padding: 0;
		font-size: 100%;
	}
	.day-settings-today {
		width: 100%; 
		text-align: center;
		font-size: 100%;
	}
	.calendar {
		width: 100%; 
		text-align: center;
		font-size: 100%;
	}
	.readings {
		width: 100%; 
		text-align:left;
		font-size: 100%;
	}
	.tropary {
		width: 100%; 
		text-align:left;
		font-size: 100%;
	}
	details {
		width: 100%;
	}
	input[type="button"],
	summary {
		cursor: pointer;
	}
	.bg_bibleRef {
		cursor: help;
		text-decoration: underline;
	}

	@media screen and (max-width: 1280px) {
		.container {
			width: 100%;
		}
		details.data {
			display: none;
		}
	}
	
</style>
</head>
<body>
<div class="container">
<?php
/*
// Устанавливаем английский язык
putenv('LC_ALL=en_US');
setlocale(LC_ALL, 'en_US');

// Указываем путь к таблицам переводов
bindtextdomain("calendar", "./locale");

// Выбираем домен
textdomain("calendar");

// Теперь поиск переводов будет идти в ./locale/en_US/LC_MESSAGES/calendar.mo
*/

include_once ('functions.php');
include_once ('readings.php');

/***
	Исполняемый код
***/

$weekday = [_("Понедельник"),_("Вторник"),_("Среда"),_("Четверг"),_("Пятница"),_("Суббота"),_("Воскресенье")];
$monthes = [_("января"),_("февраля"),_("марта"),_("апреля"),_("мая"),_("июня"),_("июля"),_("августа"),_("сентября"),_("октября"),_("ноября"),_("декабря")];

$date = bg_currentDate();

list($y, $m, $d) = explode('-', $date);
$y = (int)$y; 
$wd = date("N",strtotime($date));
$tone = bg_getTone($date);
$easter = bg_get_easter($y);

$dd = ($y-$y%100)/100 - ($y-$y%400)/400 - 2;
$old = date("Y-m-d",strtotime ($date.' - '.$dd.' days')) ;
list($old_y,$old_m,$old_d) = explode ('-', $old);

$tomorrow = date ('Y-m-d', strtotime($date.'+ 1 days'));

$data = array();
$data = bg_getData($old_y);

?>
<!-- Выбор даты -->	
	<div class="day-settings-today">
		<input id="bg_yesterdayButton" type="button" value="< <?php echo _("Вчера"); ?>">
		<input id="bg_setDay" class="bg_setDay" type="date" value="<?php echo  $date; ?>" title="<?php echo _("Выбрать дату"); ?>"> 
		<input id="bg_todayButton" type="button" value="<?php echo _("Сегодня"); ?>">
		<input id="bg_tommorowButton" type="button" value="<?php echo _("Завтра"); ?> >">
	</div>
	
	<details class="data"><summary><?php echo _("Данные дня"); ?></summary>
		<pre>
			<?php print_r($data[$date]); ?>
		</pre>
	</details>
	<hr>	
	<div class="calendar">
	<!-- Икона дня -->
		<img height="250" src="https://azbyka.ru/days/storage/images/<?php echo $data[$date]['icon']; ?>" title="<?php echo $data[$date]['icon_title']; ?>" alt="<?php echo $data[$date]['icon_title']; ?>" />
	<!-- Дата по новому стилю -->
		<h3<?php echo (($wd==7)?' style=" color:red"':""); ?>><?php echo $weekday[$wd-1].',<br>'. sprintf (_('%1$d %2$s %3$d г.'), (int)$d , $monthes[$m-1] , (int)$y); ?><br>
	<!-- и по старому стилю -->
		<?php echo '('.sprintf (_('%1$d %2$s ст.ст.'), (int)$old_d, $monthes[$old_m-1]).')'; ?></h3>
	<!-- Название седмицы/Недели -->
		<h4<?php echo (($wd==7)?' style=" color:red"':""); ?>><?php echo $data[$date]['sedmica']; ?></h4>
	<!-- Глас, пост, пища -->
		<p><?php echo _("Глас").' '.$data[$date]['tone']; ?>, <?php echo $data[$date]['food']; ?></p>
	
<?php
$level_name = [_('Двунадесятый'), _('Великий'), _('Бденный'), _('Полиелейный'), _('Славословный'), _('Шестеричный'), _('Вседневный'), _('Особый')];
/*******************************************************
	Выводим названия событий пятью абзацами.
		1. Есть служба в Минее/Триоди
		2. Память общих святых
		3. Память новомучеников
		4. Почитание икон Богородицы
		5. Прочие
********************************************************/
// Внимание, данные с приоритетом 0 на экран не выводим (только чтения)
for ($i=1; $i<6; $i++) {
	$text = '';
	foreach ($data[$date]['events'] as $event) {
		$title = (in_array($event['level'], [1,8]))?('<b>'.$event['title'].'</b>'):$event['title'];
		$title = '<span'.(($event['level'] < 3)?' style=" color:red"':"").'>'.$title.'</span>';
		if ($event['priority'] == $i) $text .= (($event['level'] < 7)?('<img src="symbols/S'.$event['level'].'.gif" title="'.$level_name[$event['level']].'" alt="'.$level_name[$event['level']].'" /> '):''). $title.'. ';
	}
	if ($text) echo '<p>'.$text.'</p>';
}
?>
	</div>
	<hr>
	<div class='readings'>
<?php
/*******************************************************
	Выводим чтения суточного круга
********************************************************/
// Тип литургии 
	$liturgy = [_("Нет литургии.") ,_("Литургия свт. Иоанна Златоуста."), _("Литургия свт. Василия Великого."), _("Литургия Преждеосвященных Даров.")];
	echo '<p>'.$liturgy[$data[$date]['liturgy']].'</p>';

// Список чтений дня
	// Праздники
	foreach ($data[$date]['events'] as $event) {
		if (!in_array($data[$date]['day_subtype'], ['universal_saturday', 'eve'])) {
			if ($wd == 6 || (is_numeric($event['priority']) && $event['level'] < 3 && $wd < 7)) { // Суббота или Бдение и выше
				bg_printReadings ($event['readings'], false);
			}
		}
	}
	// Рядовые
	foreach ($data[$date]['ordinary_readings'] as $readings) {
		bg_printReadings ($readings);
	}
	// Праздники
	foreach ($data[$date]['events'] as $event) {
		if (!in_array($data[$date]['day_subtype'], ['universal_saturday', 'eve'])) {
			if ($wd != 6 && is_numeric($event['priority']) && !($event['level'] < 3 && $wd < 7)) { // Не суббота и Полиелей и ниже
				bg_printReadings ($event['readings'], false);
			}
		} else {
			if (in_array($event['subtype'], ['universal_saturday', 'eve'])) {
				bg_printReadings ($event['readings'], false);
			}
		}
		
	}
	foreach ($data[$tomorrow]['events'] as $event) {
		bg_printEvReadings ($event['readings']);
	}
?>
	</div>
	<div id="bible">
	<?php 
		$ref = $_POST["ref"];
		if (!empty($ref)) {
			$text = bg_get_bible ($ref);
			if (!$text) $text = bg_get_paremiaes ($ref);
			if (!$text) $text = blink ($ref,'az_hlink');
	?>
		<hr>
		<div style="width: 100%; text-align: right;">
			<input id="bg_clear" type="button" value="<?php echo _("Очистить"); ?>">
		</div>
	<?php
			echo $text; 
		}
	?>
	</div>
	<hr>
	<div class='tropary'>
	<h3><?php echo _("Тропари, кондаки, молитвы и величания"); ?></h3>
<?php 
/*******************************************************
	Выводим тропари, кондаки, молитвы и величания
********************************************************/
	// Тропари и кондаки дня
	$event = bg_tropary_days ($date);
	if ($date != bg_get_easter($y, 0) && !empty($event['taks']) && !empty($event['taks'][0])) {
		echo '<details><summary>'._("Тропари и кондаки дня").'</summary>'.PHP_EOL;
		foreach ($event['taks'] as $tak) {
			echo '<h4>'.$tak['title'].($tak['voice']?(', '._("глас").' '.$tak['voice']):'').'</h4>'.PHP_EOL;
			echo '<p>'.$tak['text'].'</p>'.PHP_EOL;
		}
		echo '</details>'.PHP_EOL;
	}
 
	// Тропари и кондаки событий календаря
	foreach ($data[$date]['events'] as $event) {
		if (!empty($event['taks']) && !empty($event['taks'][0])) {
			$title = $event['taks'][0]['title'];	// В заголовок выносим название первой записи без первого слова (Тропарь)
			$title = count(explode(' ',$title,2))>1?explode(' ',$title,2)[1]:'';
			echo '<details><summary>'.$title.'</summary>'.PHP_EOL;
			foreach ($event['taks'] as $tak) {
				echo '<h4>'.$tak['title'].($tak['voice']?(', '._("глас").' '.$tak['voice']):'').'</h4>'.PHP_EOL;
				echo '<p>'.$tak['text'].'</p>'.PHP_EOL;
			}
			echo '</details>'.PHP_EOL;
		}
	}
?>		
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

	// Очистить div с текстом Библии
	var bg_clear = document.getElementById("bg_clear");
	if (bg_clear) bg_clear.addEventListener('click', function() {
		document.getElementById("bible").innerHTML='';
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
	// Отправляем POST запрос с ссылкой на Библию 
	var els = document.getElementsByClassName("bg_bibleRef");
	Array.prototype.forEach.call(els, function(el) {
		el.addEventListener("click",
			function() {
				var url=location.href;
				var xhr = new XMLHttpRequest();
				xhr.open("POST", url, false);
				xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
				xhr.onreadystatechange = function() {
					if (this.readyState != 4) return;
//					location.reload();
					document.body.innerHTML = '';
					document.write(xhr.responseText);				
					return false;
				}
				var ref = el.innerText;
				xhr.send("ref="+ref);
			},
			false
		);
	});
	
</script>
</div>
</body>
</html>
<?php
/*************************************************************************************

	Пользовательские функции выводят ссылки на чтения Св.Писания
		
**************************************************************************************/
// Всего дня 
function bg_printReadings ($readings, $evening=true) {
	if (empty($readings)) return;
	$text =
		(!empty($readings['morning'])?('<i>'._("Утр.").':</i> '.blink ($readings['morning'],'hlink').' '):'').
		(!empty($readings['hour1'])?('<i>'._("1-й час").':</i> '.blink ($readings['hour1'],'hlink').' '):'').
		(!empty($readings['hour3'])?('<i>'._("3-й час").':</i> '.blink ($readings['hour3'],'hlink').' '):'').
		(!empty($readings['hour6'])?('<i>'._("6-й час").':</i> '.blink ($readings['hour6'],'hlink').' '):'').
		(!empty($readings['hour9'])?('<i>'._("9-й час").':</i> '.blink ($readings['hour9'],'hlink').' '):'').
		(!empty($readings['apostle'])?('<i>'._("Лит.").': '._("Ап.").'-</i> '.blink ($readings['apostle'],'hlink').' '):'').
		(!empty($readings['gospel'])?('<i>'._("Ев.").'-</i> '.blink ($readings['gospel'],'hlink').' '):'').
		($evening && !empty($readings['evening'])?('<i>'._("Веч.").':</i> '.blink ($readings['evening'],'hlink').' '):'');
	echo $text?('<p>'.(!empty($readings['title'])?('<i>'.$readings['title'].':</i> '):'').$text.'</p>'):'';
}
// Вечера
function bg_printEvReadings ($readings) {
	if (empty($readings)) return;
	$text = (!empty($readings['evening'])?('<i>'._("Веч.").':</i> '.blink ($readings['evening'],'hlink').' '):'');
	echo $text?('<p>'.(!empty($readings['title'])?('<i>'.$readings['title'].':</i> '):'').$text.'</p>'):'';
}


/*************************************************************************************
	Пользовательская функция, которая формирует ссылку на Св.Писание 
	на сайте пользователя
	Получает параметры:
		$abbr - обозначение книги на английском языке
		$book - обозначение книги на языке локали
		$ch - номера глав и стихов
		
	Возвращает ссылку на отрывок Св.Писания
		
**************************************************************************************/
function az_hlink ($abbr, $book, $ch) {
// Ссылка на Библию
	return '<a target="_blank" href="https://azbyka.ru/biblia/?'.$abbr.'.'.$ch.'">'.$book.'.'.$ch.'</a>';
} 
function hlink ($abbr, $book, $ch) {

	return '<span class="bg_bibleRef" title="'._("Текст Библии").'">'.$book.'.'.$ch.'</span>';
} 
		
/*******************************************************************************

	Функция возвращает текст служебной Библии в соответствии с запросом 
		$ref - ссылка на отрывок в Библии
	
*******************************************************************************/
function bg_get_bible ($ref) {
	
	$json = file_get_contents( dirname(__FILE__).'/liturgical_bible/bible.json' );
	$obj = json_decode ($json, true);
	if (empty($obj)) return '';
	$ref = preg_replace ('/\s+/u', '', $ref);

	$txt = "";
	$content = $obj['content'] ?? '';
	if ($content) {
		foreach (array_column($content, 'ref') as $key => $value) {
			$value = preg_replace ('/[АБ]/u', '', $value);
//			if ($value == $ref) break;
			if (mb_strpos($value, $ref) !== false) break;
			else $key = false;
		}
		if ($key === false) return '';
		$txt = $content[$key]['excerpt'];
		if (function_exists('bg_bibrefs')) $value = bg_bibrefs($value);
		$title = '<p><strong>'.$content[$key]['title'].' '.$content[$key]['desc'].' ('.$value.')</strong></p>';
	}
	if ($txt) $txt = '<div class="bg_bibrefs_service">'.$title.$txt.'</div>';
	return $txt;
}
/*******************************************************************************

	Функция возвращает текст служебного Паримийника в соответствии с запросом 
		$ref - ссылка на отрывок в Библии
	
*******************************************************************************/
function bg_get_paremiaes ($ref) {
	
	$json = file_get_contents( dirname(__FILE__).'/liturgical_bible/paremiaes.json' );
	$obj = json_decode ($json, true);
	if (empty($obj)) return '';
	$ref = preg_replace ('/\s+/u', '', $ref);

	$txt = "";
	$content = $obj['content'] ?? '';
	if ($content) {
		foreach (array_column($content, 'ref') as $key => $value) {
//			if ($value == $ref) break;
			if (mb_strpos($value, $ref) !== false) break;
			else $key = false;
		}
		if ($key === false) return '';
		$txt = $content[$key]['excerpt'];
		if (function_exists('bg_bibrefs')) $value = bg_bibrefs($value);
		$title = '<p><strong>'.$content[$key]['title'].' '.$content[$key]['desc'].' ('.$value.')</strong></p>';
	}
	if ($txt) $txt = '<div class="bg_bibrefs_service">'.$title.$txt.'</div>';
	return $txt;
}

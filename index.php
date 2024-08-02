<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="description" content="The orthodox calendar with liturgical readings and troparion.">
<link rel="apple-touch-icon" sizes="180x180" href="https://azbyka.ru/worships/calendar/icons/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="192x192" href="https://azbyka.ru/worships/calendar/icons/favicon-192x192.png">
<link rel="icon" type="image/png" sizes="96x96" href="https://azbyka.ru/worships/calendar/icons/favicon-96x96.png">
<link rel="icon" type="image/png" sizes="32x32" href="https://azbyka.ru/worships/calendar/icons/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="https://azbyka.ru/worships/calendar/icons/favicon-16x16.png">
<link rel="manifest" href="https://azbyka.ru/worships/calendar/icons/site.webmanifest">
<link rel="mask-icon" href="https://azbyka.ru/worships/calendar/icons/safari-pinned-tab.svg" color="#5bbad5">
<meta name="msapplication-TileColor" content="#da532c">
<meta name="theme-color" content="#ffffff">
<title>Православный календарь</title>

<style>
	body {
		font-family: Geneva, Arial, Helvetica, sans-serif;
		font-size: 1.2em;
	}
	.container {
	  display: flex;
	  justify-content: center;
	}
	.main {
		display: block;
		width: 34%;
		margin: 0;
		padding: 0;
		font-size: 100%;
	}
	.day-settings-today {
		width: 100%; 
		text-align: center;
		font-size: 100%;
		margin-bottom: 0.5em;
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
	.footer {
		width: 100%; 
		text-align:left;
		font-size: 80%;
		color: #333;
	}
	details {
		width: 100%;
	}
	summary {
		cursor: pointer;
		font-size: 0.9em;
		margin-bottom: 0.4em;
	}
	input[type="button"] {
		cursor: pointer;
		font-size: 0.9em;
		height: 1.8em;
		border-radius: 0.75em;
		border: 1px solid #aaa;
		margin-bottom: 3px;
	}
	input#bg_setDay {
		border-radius: 5px;
		border: 1px solid #aaa;
		margin-bottom: 3px;
	}
	hr {
		margin:0;
	}
	.bg_content {
		background-color: #eee;
		padding: 0 0.5em;
		margin: 0;
	}
	div.bg_hide_block {
		width: 100%; 
		text-align: right;
	}
	.bg_hide_block input {
		margin-right: 1em;
	}
	.bg_descriptions,
	.bg_bibleRef {
		cursor: help;
		text-decoration: underline;
	}

	details.data {
		display: none;
	}
	
	div.slider {
		width: 100%;
		margin: auto;	
	}
	
	#scroll-left,
	#scroll-right {
		display: inline-block;
		height: 270px;  
		text-align: center;
		font-weight: bold;
		color: darkred;
		opacity: 0.2;
		cursor: pointer;
		user-select: none;
	}
	#scroll-left div,
	#scroll-right div {
		width: 20px;
		text-align: center;
		line-height: 270px;
	}
	#scroll-left:hover,
	#scroll-right:hover {
		opacity: 0.9;
		background-color: #fafafa;
	}
	
	#icon-pics{
		display: inline-block;
		width: 250px;
		height: 250px;        
		overflow: hidden;
		white-space:nowrap;
		vertical-align: top;
		scroll-behavior: smooth;
	}
	
	.icon {
		border-radius: 5px;
		display: inline-block;
		vertical-align: top;
		margin: 0 10px;
		width: 230px;
		height:210px;
	}
	.icon img {
		filter: drop-shadow(0px 3px 3px #400a);
	}
	.icon figcaption {
		width:100%; 
		font-size: 60%; 
		text-align: center;
		height: 3em;
		white-space: normal;
	}
	
	@media screen and (max-width: 960px) {
		.main {
			width: 100%;
		}
		input {
			font-size:100%;
			color: darkred;
		}
	}
	@media screen and (max-width: 480px) {
		input#bg_setDay {
			min-width: calc(100% - 10px);
			float: left;
		}
	}
	
</style>
</head>
<body>
<div class="container">
<section class="main">
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

$desc_json = 'descriptions.json';
if (file_exists($desc_json)) {
	$json = file_get_contents($desc_json);
	$descriptions = json_decode($json, true);
} else $descriptions = array();

?>
<!-- Выбор даты -->	
	<div class="day-settings-today">
		<input id="bg_yesterdayButton" type="button" value="&#9666; <?php echo _("Вчера"); ?>">
		<input id="bg_setDay" class="bg_setDay" type="date" value="<?php echo  $date; ?>" title="<?php echo _("Выбрать дату"); ?>"> 
		<input id="bg_todayButton" type="button" value="<?php echo _("Сегодня"); ?>">
		<input id="bg_tommorowButton" type="button" value="<?php echo _("Завтра"); ?> &#9656;">
	</div>
	
	<details class="data"><summary><?php echo _("Данные дня"); ?></summary>
		<pre>
			<?php print_r($data[$date]); ?>
		</pre>
	</details>
	
	<div class="calendar">
	<!-- Икона дня -->
		<div class="slider">
		<div id="scroll-left"><div> < </div></div>
		<div id="icon-pics">
		<?php 
		foreach ($data[$date]['events'] as $event) { 
			if (!empty($event['imgs']))  {
				$ev ='';
				foreach($event['imgs'] as $image) {
					if ($ev == dirname($image)) continue;	// Только 1 икона для святого.
					$ev = dirname($image);
					$src = 'https://azbyka.ru/worships/calendar/images/'.$image;
					$icon_title = $event['title'];
		?>
			<figure class="icon"><img height="210" src="<?php echo $src; ?>" title="<?php echo $icon_title; ?>" alt="<?php echo $icon_title; ?>" /><figcaption><?php echo $icon_title; ?></figcaption></figure>
		<?php 
				}
			}
		}
		?>
		</div>
		<div id="scroll-right"><div> > </div></div>
		</div>
	<!-- Дата по новому стилю -->
		<h3<?php echo (($wd==7)?' style=" color:red"':""); ?>><?php echo $weekday[$wd-1].', '. sprintf (_('%1$d %2$s %3$d г.'), (int)$d , $monthes[$m-1] , (int)$y); ?><br>
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
		if ($event['priority'] == $i) {
			$symbol = ($event['level'] < 7)?('<img src="symbols/S'.$event['level'].'.gif" title="'.$level_name[$event['level']].'" alt="'.$level_name[$event['level']].'" />&nbsp;'):'';
			$desc_img = '';
			if (!empty($descriptions) && !empty($event['id_list'])) {
				$id_list = explode(',', $event['id_list']);
				foreach($id_list as $id) {
					if (array_key_exists($id, $descriptions)) { 
						$desc_img .= '&nbsp;<a href="#bg_desc_text"><span class="bg_descriptions" data-desc="'.$id.'"><img src="symbols/L.gif" title="Житие" alt="Житие" /></span></a>';
					} 
				}
			}
			$text .= $symbol. $title.$desc_img.'. ';
		}
	}
	if ($text) echo '<p>'.$text.'</p>';
}
?>
	</div>
<!-- Текст Жития -->
	<div id="bg_desc_text" class="bg_content">
	<?php 
		$desc = $_POST["desc"];
		$text = '';
		if (!empty($desc)) {
			foreach ($descriptions[$desc] as $description) {
				$text .= '<h4>'.$description['title'].'</h4>';			// Заголовок Жития
				$text .= '<p>'.$description['text'].'</p>';				// Текст Жития
			}
	?>
		<hr>
		<div class="bg_hide_block">
			<input id="bg_hide_block1" type="button" value="<?php echo _("Скрыть"); ?>">
		</div>
	<?php
			echo $text.'<br>'; 
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
	$liturgy = [_("Нет литургии.") ,_("Литургия свт. Иоанна Златоуста."), _("Литургия свт. Василия Великого."), _("Литургия Преждеосвященных Даров."), _("Литургия Преждеосвященных Даров возможна только ради полиелея.")];
	echo '<h5>'.$liturgy[$data[$date]['liturgy']].'</h5>';

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
<!-- Текст Библии -->
	<div id="bg_bible_text" class="bg_content">
	<?php 
		$ref = $_POST["ref"];
		if (!empty($ref)) {
			$text = bg_get_bible ($ref);					// Служебные Апостол и Евангелие
			if (!$text) $text = bg_get_paremiaes ($ref);	// Служебные Паримии
			if (!$text) $text = blink ($ref,'az_hlink');	// Ссылка на сайт Библии
	?>
		<hr>
		<div class="bg_hide_block">
			<input id="bg_hide_block2" type="button" value="<?php echo _("Скрыть"); ?>">
		</div>
	<?php
			echo $text.'<br>'; 
		}
	?>
	</div>
<!-- Тропари, кондаки, молитвы и величания -->
	<div class='tropary'>
	<hr>
	<h3><?php echo _("Тропари, кондаки, молитвы и величания"); ?></h3>
<?php 
/*******************************************************
	Выводим тропари, кондаки, молитвы и величания
********************************************************/
	// Тропари и кондаки дня
	$event = bg_tropary_days ($date);
	if (!empty($event['taks']) && !empty($event['taks'][0])) {
		echo '<details><summary>'._("Тропари и кондаки дня").'</summary>'.PHP_EOL;
		echo '<div class="bg_content"><hr>'.PHP_EOL;
		foreach ($event['taks'] as $tak) {
			echo '<h4>'.$tak['title'].($tak['voice']?(', '._("глас").' '.$tak['voice']):'').'</h4>'.PHP_EOL;
			echo '<p>'.$tak['text'].'</p>'.PHP_EOL;
		}
		echo '<hr></div></details>'.PHP_EOL;
	}
 
	// Тропари и кондаки событий календаря
	foreach ($data[$date]['events'] as $event) {
		if (!empty($event['taks']) && !empty($event['taks'][0])) {
			$title = $event['taks'][0]['title'];	// В заголовок выносим название первой записи без первого слова (Тропарь)
			$title = count(explode(' ',$title,2))>1?explode(' ',$title,2)[1]:'';
			echo '<details><summary>'.$title.'</summary>'.PHP_EOL;
			echo '<div class="bg_content"><hr>'.PHP_EOL;
			foreach ($event['taks'] as $tak) {
				echo '<h4>'.$tak['title'].($tak['voice']?(', '._("глас").' '.$tak['voice']):'').'</h4>'.PHP_EOL;
				echo '<p>'.$tak['text'].'</p>'.PHP_EOL;
			}
		echo '<hr></div></details>'.PHP_EOL;
		}
	}
?>		
	</div>
<!-- Завершение страницы -->	
<div class="footer">
	<hr>
	<p>Версия 3.13 от 01.08.2024</p>
</div>	
</section>
</div>
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

	// Очистить div с текстом Жития
	var bg_hide_block1 = document.getElementById("bg_hide_block1");
	if (bg_hide_block1) bg_hide_block1.addEventListener('click', function() {
		document.getElementById("bg_desc_text").innerHTML='';
	}, false);

	// Очистить div с текстом Библии
	var bg_hide_block2 = document.getElementById("bg_hide_block2");
	if (bg_hide_block2) bg_hide_block2.addEventListener('click', function() {
		document.getElementById("bg_bible_text").innerHTML='';
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

	// Отправляем POST запрос с ссылкой на Жития 
	var els1 = document.getElementsByClassName("bg_descriptions");
	Array.prototype.forEach.call(els1, function(el) {
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
				var desc = el.getAttribute('data-desc');
				xhr.send("desc="+desc);
			},
			false
		);
	});

	// Отправляем POST запрос с ссылкой на Библию 
	var els2 = document.getElementsByClassName("bg_bibleRef");
	Array.prototype.forEach.call(els2, function(el) {
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
	
	var button_left = document.getElementById("scroll-left");
	var button_right = document.getElementById("scroll-right");

	button_left.onclick = () => {
	  document.getElementById("icon-pics").scrollLeft += 256;
	};
	button_right.onclick = () => {
	  document.getElementById("icon-pics").scrollLeft -= 256;
	};
</script>
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

	return '<span class="bg_bibleRef" title="'._("Показать текст").'">'.$book.'.'.$ch.'</span>';
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
			elseif (mb_strpos($ref, $value) !== false) break;
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

<?php
/***

	Редактор файла данных Православного Календаря

echo 'Максимальный размер данных: ' . ini_get('post_max_size') . '<br>';      
echo 'Максимальный размер файлов: ' . ini_get('upload_max_filesize') . '<br>'; 
echo 'Максимальное количество переменных: ' . ini_get('max_input_vars') . '<br>';     
echo 'Максимальное время выполнения скрипта: ' . ini_get('max_execution_time') . '<br>'; 
echo 'Максимальное время обработки данных: ' . ini_get('max_input_time') . '<br>';     
echo 'Память для скрипта: ' . ini_get('memory_limit') . '<br>';   
***/

include_once ('functions.php');
include_once ('readings.php');

/***
	Исполняемый код
***/

if (isset($_GET["file"])){
	$filename = $_GET["file"];
	if (!file_exists($filename)) exit("Файл ".$filename." остутствует.");
} else {
	exit();
}

$weekday = [_("Понедельник"),_("Вторник"),_("Среда"),_("Четверг"),_("Пятница"),_("Суббота"),_("Воскресенье")];
$monthes = [_("января"),_("февраля"),_("марта"),_("апреля"),_("мая"),_("июня"),_("июля"),_("августа"),_("сентября"),_("октября"),_("ноября"),_("декабря")];
$level_name = [_('Двунадесятый'), _('Великий'), _('Бденный'), _('Полиелейный'), _('Славословный'), _('Шестеричный'), _('Вседневный'), _('Особый')];

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
$data = bg_getData($old_y, $filename);

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<link href="css/codemirror.css" rel="stylesheet">
	<link href="css/edit.css" rel="stylesheet">
	<script src="js/codemirror.js"></script>
	<script src="js/mode/javascript.js"></script>
	<script src="js/edit.js"></script>
	<title>Редактор событий Православного Календаря</title>
</head>
<body>
	<div class="container">
	<section class="tasks">
        <h1 class="tasks__title">События Православного Календаря</h1>
		<details id="by_date" open><summary>События по датам</summary>
		<div class="date_calendar">
		<!-- Выбор даты -->	
		<div class="day-settings-today">
			<input id="bg_yesterdayButton" type="button" value="< <?php echo _("Вчера"); ?>">
			<input id="bg_setDay" class="bg_setDay" type="date" value="<?php echo  $date; ?>" title="<?php echo _("Выбрать дату"); ?>"> 
			<input id="bg_todayButton" type="button" value="<?php echo _("Сегодня"); ?>">
			<input id="bg_tommorowButton" type="button" value="<?php echo _("Завтра"); ?> >">
		</div>
		
		<hr>	
		<div class="calendar">
		<!-- Дата по новому стилю -->
			<h3<?php echo (($wd==7)?' style=" color:red"':""); ?>><?php echo $weekday[$wd-1].', '. sprintf (_('%1$d %2$s %3$d г.'), (int)$d , $monthes[$m-1] , (int)$y); ?>
		<!-- и по старому стилю -->
			<?php echo '('.sprintf (_('%1$d %2$s ст.ст.'), (int)$old_d, $monthes[$old_m-1]).')'; ?></h3>
		<!-- Название седмицы/Недели -->
			<h4<?php echo (($wd==7)?' style=" color:red"':""); ?>><?php echo $data[$date]['sedmica']; ?></h4>
	
<?php
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
				$title = '<a class="the_event" href="#d'.$event['id'].'"><span'.(($event['level'] < 3)?' style=" color:red"':"").'>'.$title.'</span></a>';
				if ($event['priority'] == $i) $text .= (($event['level'] < 7)?('<img src="symbols/S'.$event['level'].'.gif" title="'.$level_name[$event['level']].'" alt="'.$level_name[$event['level']].'" /> '):''). $title.'. ';
			}
			if ($text) echo '<p>'.$text.'</p>';
		}
?>
		</div>
	</div></details>

	<form method="post">

        <h3>Список событий <input type="submit" id="submit" value="Сохранить"></h3>
        <ul class="tasks__list">
<?php 
		if(isset($_POST["events"])){
			 
			$errors = array();
			$error_names = [
				JSON_ERROR_NONE => 'Ошибок нет',	 
				JSON_ERROR_DEPTH => 'Достигнута максимальная глубина стека',
				JSON_ERROR_STATE_MISMATCH => 'Неверный или некорректный JSON',
				JSON_ERROR_CTRL_CHAR => 'Ошибка управляющего символа, возможно неверная кодировка',
				JSON_ERROR_SYNTAX => 'Синтаксическая ошибка',
				JSON_ERROR_UTF8 => 'Некорректные символы UTF-8, возможно неверная кодировка',
				JSON_ERROR_RECURSION => 'Одна или несколько зацикленных ссылок в кодируемом значении',
				JSON_ERROR_INF_OR_NAN => 'Одно или несколько значений NAN или INF в кодируемом значении',
				JSON_ERROR_UNSUPPORTED_TYPE => 'Передано значение с неподдерживаемым типом',
				JSON_ERROR_INVALID_PROPERTY_NAME => 'Имя свойства не может быть закодировано',
				JSON_ERROR_UTF16 => 'Некорректный символ UTF-16, возможно некорректно закодирован'
			];

			$jsons = $_POST["events"];
			$j = 0;
			foreach($jsons as $json) {
				$event = json_decode($json, true);
				if (!$event) {
					$errors[$j] = 'Ошибка: '.$error_names[json_last_error()];
				}
				$events[] = $event;
				$j++;
			}
			if(!empty($errors)) {
				foreach ($errors as $key => $error) echo '<p>'.'Строка '.$key.'. '.$error.'</p>';
			} elseif ($_POST["count"] < count($jsons)) {
				$message = 'Потеря данных: было '.$_POST["count"].', получено '.count($jsons).', загружено '.$j;
				echo "<script type='text/javascript'>alert('".$message."');</script>";
			} else {
				$new_json = json_encode($events, JSON_UNESCAPED_UNICODE);
				$new_filename = date('Y_m_d_H_i_').$filename;
				if (copy ($filename, $new_filename)) {
					file_put_contents($filename, $new_json);
//					header("Location: ".$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']); 
					$message = "Файл ".$filename." сохранен на сервере";
					echo "<script type='text/javascript'>history.back(); alert('".$message."');</script>";
				}
			}

		} else {
			$json = file_get_contents($filename);
			$events = json_decode($json, true);
?>
			<input type="hidden" id="bg_countEvents" name="count" value="<?php echo count($events); ?>">
<?php
			
		}
		
		$data = array();
		$j = 0;
		foreach ($events as $event) {
			if ($event) {
				$summary = str_pad($event['id'], 5, "0", STR_PAD_LEFT).' '.mb_strimwidth($event['title'], 0, 50, "...").' <b>['.(is_array($event['rule'])?implode("][", $event['rule']):$event['rule']).']</b>';
				$event_json = json_encode($event, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
			} else {
				$summary = '<span class="red">'.$errors[$j].'</span>';
				$event_json = $jsons[$j];
			}
?>
			<li class="tasks__item">
				<details id="d<?php echo  $event['id']; ?>"><summary><?php echo $summary; ?></summary>
					<textarea class="editor" name="events[]"><?php echo $event_json; ?></textarea>
					<input type="button" class="add" value="Добавить"> <input type="button" class="remove" value="Удалить"> <input type="button" class="validate" value="Проверить">
				</details>
			</li>
<?php
			$j++;
		}
?>	
		</ul>
	</form>
	</section>
	</div>
</body>
</html>

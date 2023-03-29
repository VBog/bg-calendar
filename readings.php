<?php
/*
	Формируем чтения на день
*/
include_once ('bg_ordered_readings.php');
include_once ('sedmica.php');

/*******************************************************************************
	
	Функция получает базу данных календаря из файла json
	и возвращает массив событий для каждого календарного дня указанного года
	
	$year - год в формате YYYY по старому стилю
	
*******************************************************************************/  
function bg_getData($year) {
	
	$filename = 'data/'.$year.'.json';
/*
	if (file_exists($filename)) {
		$json = file_get_contents($filename);
		$data = json_decode($json, true);
		return $data;
	}	
*/
	$json = file_get_contents('calendar.json');
	$events = json_decode($json, true);
	
	// Период триодей в текущем году
	$triod_period = bg_get_date_by_rule ('0--56,0-50;0-56;', $year);
	// Светлая седмица и Вселенские родительские субботы
	$easterweek = bg_get_date_by_rule ('0--57;0-0,0-6;0-48', $year);

	$data = array();
	// Формируем массив по дням года
	foreach ($events as $event) {
		$dates = bg_get_date_by_rule ($event['rule'], $year);
		if (!empty($dates)) {
			foreach ($dates as $date) {
				
				// На Светлой седмице и в Вселенские родительские субботы нет праздников святых
				if (in_array($date, $easterweek) && $event['level'] > 1 && $event['level'] != 8) {
					$event['readings'] = array();

				// В период триодей чтения только на полиейные праздники
				} elseif (in_array($date, $triod_period) && $event['level'] > 3 && $event['level'] != 8) {
					$event['readings'] = array();
				}
				$data[$date]['events'][] = $event;
			}
		}
	}

	// Дополним массив данных по дням дополнительной информацией
	foreach ($data as $date => $value) {
		// Особый день
		$day_type = '';
		$day_subtype = '';
		// Найдем главный праздник и икону дня
		$ev = $value['events'][0];
		$main_level = $ev['level'];
		$main_type = $ev['type'];
		$main_subtype = $ev['subtype'];
		$main_feast_type = $ev['feast_type'];
		$icon = (!empty($ev['imgs']))?$ev['imgs'][0]:'';
		$icon_title = $ev['title'];
		foreach ($value['events'] as $event) {
			// Если особая Неделя или родительская суббота
			if (in_array($event['type'], ['weekend', 'memorial'] )) {
				$day_type = $event['type'];
				$day_subtype = $event['subtype'];
				// Если вселенская родительская суббота, то это главный праздник
				if (in_array($day_subtype, ['universal_saturday'] )) {
					$main_level = $event['level'];
					$main_type = $event['type'];
					$main_subtype = $event['subtype'];
					$main_feast_type = $event['feast_type'];
					if (!empty($event['imgs'])) {
						$icon_title = $event['title'];
						$icon = $event['imgs'][0];
					}
					break;
				}
			}
			if ($main_level > $event['level']) {
				$main_level = $event['level'];
				$main_type = $event['type'];
				$main_subtype = $event['subtype'];
				$main_feast_type = $event['feast_type'];
				if (!empty($event['imgs'])) {
					$icon_title = $event['title'];
					$icon = $event['imgs'][0];
				}
			}
		}
		
		// Если у главного праздника нет иконы, то найдем первую в списке
		if (!$icon) {
			foreach ($value['events'] as $event) {
				if (!empty($event['imgs'])) {
					$icon_title = $event['title'];
					$icon = $event['imgs'][0];
					break;
				}
			}
		}
		
		// Добавляем в БД основные параметры дня
		$data[$date]['day_type'] = $day_type;				// Тип особого дня
		$data[$date]['day_subtype'] = $day_subtype;			// Подтип особого дня
		$data[$date]['main_level'] = $main_level;			// Уровень главного события дня
		$data[$date]['main_type'] = $main_type;				// Тип главного события дня
		$data[$date]['main_subtype'] = $main_subtype;		// Подтип главного события дня
		$data[$date]['main_feast_type'] = $main_feast_type;	// Тип принадлежности главного события дня
		$data[$date]['icon'] = $icon;						// Икона дня
		$data[$date]['icon_title'] = $icon_title;			// Название иконы дня
	
		$data[$date]['sedmica'] = bg_sedmica ($date);		// Название седмицы/Недели
		$data[$date]['tone'] = bg_getTone ($date);			// Глас Октоиха
		$data[$date]['food'] = bg_getFood ($date);			// Рекомендации пищи
		
	}
	ksort($data);		// Сортируем по датам
	
	// Рядовые чтения	
	foreach ($data as $date => $value) {
		
		$readings = array();
		
		list($y, $m, $d) = explode('-', $date);
		$wd = date("N",strtotime($date));
		
		$tomorrow = date ('Y-m-d', strtotime($date.'+ 1 days'));
		$wd_t = date("N",strtotime($tomorrow));
		$yesterday = date ('Y-m-d', strtotime($date.'- 1 days'));
		$wd_y = date("N",strtotime($yesterday));
		$before_yesterday = date ('Y-m-d', strtotime($date.'- 2 days'));
		$wd_by = date("N",strtotime($before_yesterday));
		
		$or = new OrderedReadings();
		
		if ($date < bg_get_new_date ('0--48', $y) || bg_get_new_date ('0-0', $y) < $date ) { // Не Великим постом

																									// Если сегодня:
			if (!($data[$date]['main_level'] <= 3 && $data[$date]['main_feast_type'] == 'our_lord') && 	// НЕ господский,
				!($data[$date]['main_level'] <= 2 && $wd < 7) &&										// НЕ Великий и Бденный в будни
				$data[$date]['main_type'] != 'eve') {													// и НЕ Навечерие

			// Проверяем переносы рядовых чтений на сегодня
				$wd_name = ['за понедельник','за вторник','за среду','за четверг','за пятницу','за субботу','за Неделю'];

				// Вчера Великий или бденный праздник и сегодня вторник
				// или со вторника по субботу и позавчера Великий или бденный праздник
				if (!empty($data[$yesterday]) && 																	// Вчера: 
					(($data[$yesterday]['main_level'] <= 3 && $data[$yesterday]['main_feast_type'] == 'our_lord') || 	// Господский,
						($data[$yesterday]['main_level'] <= 2 && $wd_y < 7) ||											// или Великий и Бденный в будни
						$data[$yesterday]['main_type'] == 'eve') &&														// или Навечерие
					($wd == 2 || 																					// и сегодня Вторник
					($wd > 1 && $wd < 7 && 																			// или сегодня Вт,Ср,Чт,Пт или Сб
					!empty($data[$before_yesterday]) && 																	// и позавчера: 
					(($data[$before_yesterday]['main_level'] <= 3 && $data[$before_yesterday]['main_feast_type'] == 'our_lord') || 	// Господский,
						($data[$before_yesterday]['main_level'] <= 2 && $wd_by < 7) ||												// или Великий или Бденный в будни
						$data[$before_yesterday]['main_type'] == 'eve')))) {														// или Навечерие
				
					$readings[] = (array) $or->bg_day_readings ($yesterday, $wd_name[$wd_y-1]);
				}

				// Рядовые чтений на сегодня
				$ordinary = (array) $or->bg_day_readings ($date, 'Ряд.');
				if (in_array($data[$date]['day_subtype'], ['sunday_before', 'sunday_after'])) { 
					$ordinary['apostle'] = '';
					$ordinary['gospel'] = '';
				}
				$ordinary = array_diff($ordinary, array('', false, null));
				if (count($ordinary) <= 1) $ordinary = array();
				$readings[] = $ordinary;

				// Будни и завтра Великий или бденный праздник
				if ($wd < 6 && !empty($data[$tomorrow]) && 															// Завтра:
					(($data[$tomorrow]['main_level'] <= 3 && $data[$tomorrow]['main_feast_type'] == 'our_lord') || 		// Господский,
						($data[$tomorrow]['main_level'] <= 2 && $wd_t < 7) ||											// или Великий или Бденный в будни
						$data[$tomorrow]['main_type'] == 'eve')) {														// или Навечерие
					
					$readings[] = (array) $or->bg_day_readings ($tomorrow, $wd_name[$wd_t-1]);
				} 
			} elseif ($data[$date]['main_level'] <= 2 && $wd == 1 && !empty($data[$tomorrow]) && 					// Сегодня Великий или Бденный, Пн, и Завтра:
					(($data[$tomorrow]['main_level'] <= 3 && $data[$tomorrow]['main_feast_type'] == 'our_lord') || 		// Господский,
						($data[$tomorrow]['main_level'] <= 2 && $wd_t < 7) ||											// или Великий и Бденный в будни
						$data[$tomorrow]['main_type'] == 'eve') ) {														// или Навечерие
				$readings[] = (array) $or->bg_day_readings ($date, 'Ряд.');					
			}
		} else $readings[] = (array) $or->bg_day_readings ($date, '');

		$data[$date]['ordinary_readings'] = $readings;		// Рядовые чтения
	}

	$json = json_encode($data, JSON_UNESCAPED_UNICODE);
	file_put_contents($filename, $json);
	
	return $data;
}

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
	$triod_period = bg_get_date_by_rule ('0--56,0-56', $year);
	// Светлая седмица, Вселенские и Димитриевская  родительские субботы, первые 4 дня Великого поста
	$easterweek =  bg_get_date_by_rule ('0-0,0-6', $year);
	$universal_saturday = bg_get_date_by_rule ('0--57;0-48', $year);
	$dimitry_saturday = bg_get_date_by_rule ('6:10-15;10-19,10-21;10-23,10-25', $year);
	$lent_start = bg_get_date_by_rule ('0--48,0--45', $year);
	$data = array();
	// Формируем массив по дням года
	foreach ($events as $event) {
		$dates = bg_get_date_by_rule ($event['rule'], $year);
		if (!empty($dates)) {
			foreach ($dates as $date) {
				
			// Отменяем чтения
				// В период триодей чтения только на полиейные праздники
				if (in_array($date, $triod_period) && $event['level'] > 3 && $event['level'] != 8) {
					$event['readings'] = array();

				// На Светлой седмице нет праздников святых
				} elseif (in_array($date, $easterweek) && $event['level'] > 1 && $event['level'] != 8) {
					$event['readings'] = array();
				}
				
			// Переносим праздники
				if (!empty($event['readings']) && $event['level'] > 1 && $event['level'] != 8) {
					
				// Во Вселенские родительские субботы праздники переносим на предыдущий Чт
					if (in_array($date, $universal_saturday)) {
						$event['title'] .= ' (перенос с '. date("j/m", strtotime($date)).')';
						$newdate = date ('Y-m-d', strtotime($date.'- 2 days'));
						$data[$newdate]['events'][] = $event;

				// В Димитриевскую родительскую субботу праздники переносим на предыдущую Пт
					} elseif (in_array($date, $dimitry_saturday)) {
						$event['title'] .= ' (перенос с '. date("j/m", strtotime($date)).')';
						$newdate = date ('Y-m-d', strtotime($date.'- 1 days'));
						$data[$newdate]['events'][] = $event;
					
				// Первые 4 дня Великого поста праздники переносим на следующую Сб
					} elseif (in_array($date, bg_get_date_by_rule ('0--48,0--45', $year))) {
						$event['title'] .= ' (перенос с '. date("j/m", strtotime($date)).')';
						$newdate = bg_get_new_date ('0--43', $year);
						$data[$newdate]['events'][] = $event;

					} else {
						$data[$date]['events'][] = $event;
					}
				} else {
					$data[$date]['events'][] = $event;
				}
			}
		}
	}

	// Дополним массив данных по дням дополнительной информацией
	foreach ($data as $date => $value) {
		// Особый день
		$day_type = '';
		$day_subtype = '';
		$afterfeast = '';
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
			if (in_array($event['type'], ['weekend', 'memorial', 'eve'] )) {
				$day_type = $event['type'];
				$day_subtype = $event['subtype'];
				// Если вселенская родительская суббота или навечерие, то это главный праздник
				if (in_array($day_subtype, ['universal_saturday', 'eve'] )) {
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
			} elseif (in_array($event['type'], ['', 'feastend'] )) {
				$afterfeast = $event['type'];
				break;
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
		
		// Тип литургии
		if (is_ioann_zlatoust ($date)) $liturgy = 'Литургия свт. Иоанна Златоуста.';
		elseif (is_vasiliy_velikiy($date)) $liturgy = 'Литургия свт. Василия Великого.';
		elseif (is_grigoriy_dvoeslov ($date, $main_level <= 3)) $liturgy = 'Литургия Преждеосвященных Даров.';
		else $liturgy = 'Нет литургии.';

		// Добавляем в БД основные параметры дня
		$data[$date]['afterfeast'] = $afterfeast;			// Попразднство
		$data[$date]['day_type'] = $day_type;				// Тип особого дня
		$data[$date]['day_subtype'] = $day_subtype;			// Подтип особого дня
		$data[$date]['main_level'] = $main_level;			// Уровень главного события дня
		$data[$date]['main_type'] = $main_type;				// Тип главного события дня
		$data[$date]['main_subtype'] = $main_subtype;		// Подтип главного события дня
		$data[$date]['main_feast_type'] = $main_feast_type;	// Тип принадлежности главного события дня
		$data[$date]['icon'] = $icon;						// Икона дня
		$data[$date]['icon_title'] = $icon_title;			// Название иконы дня
	
		$data[$date]['liturgy'] = $liturgy;					// Тип литургии
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
		if (empty($data[$date]['afterfeast']) &&												// НЕ попразднство
			$date < bg_get_new_date ('0--48', $y) || bg_get_new_date ('0-49', $y) < $date ) { 	// Только в период Октоиха 
																									// Если сегодня:
			if (!($data[$date]['main_level'] <= 2 && $data[$date]['main_feast_type'] == 'our_lord') && 	// НЕ господский,
				!($data[$date]['main_level'] <= 2 && $wd < 7) &&										// и НЕ Великий и Бденный в будни
				$date != bg_get_new_date ('09-01', $y) &&												// и НЕ Новолетие
				$data[$date]['main_type'] != 'eve') {													// и НЕ Навечерие

			// Проверяем переносы рядовых чтений на сегодня
				$wd_name = ['за понедельник','за вторник','за среду','за четверг','за пятницу','за субботу','за Неделю'];

				// Вчера Великий или бденный праздник и сегодня вторник
				// или со вторника по субботу и позавчера Великий или бденный праздник
				if (!empty($data[$yesterday]) && 																	// Вчера: 
					(($data[$yesterday]['main_level'] <= 2 && $data[$yesterday]['main_feast_type'] == 'our_lord') || 	// Господский,
						($data[$yesterday]['main_level'] <= 2 && $wd_y < 7) ||											// или Великий и Бденный в будни
						$data[$yesterday]['main_type'] == 'eve') &&														// или Навечерие
					($wd == 2 || 																					// и сегодня Вторник
					($wd > 1 && $wd < 7 && 																			// или сегодня Вт,Ср,Чт,Пт или Сб
					!empty($data[$before_yesterday]) && 																	// и позавчера: 
					(($data[$before_yesterday]['main_level'] <= 2 && $data[$before_yesterday]['main_feast_type'] == 'our_lord') || 	// Господский,
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
					(($data[$tomorrow]['main_level'] <= 2 && $data[$tomorrow]['main_feast_type'] == 'our_lord') || 		// Господский,
						($data[$tomorrow]['main_level'] <= 2 && $wd_t < 7) ||											// или Великий или Бденный в будни
						$tomorrow == bg_get_new_date ('09-01', $y) ||													// или Новолетие
						$data[$tomorrow]['main_type'] == 'eve')) {														// или Навечерие
					
					$readings[] = (array) $or->bg_day_readings ($tomorrow, $wd_name[$wd_t-1]);
				} 
			} elseif ($data[$date]['main_level'] <= 2 && $wd == 1 && !empty($data[$tomorrow]) && 					// Сегодня Великий или Бденный, Пн, и Завтра:
					(($data[$tomorrow]['main_level'] <= 2 && $data[$tomorrow]['main_feast_type'] == 'our_lord') || 		// Господский,
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
/*******************************************************************************

	Функция определяет совершается ли в этот день Литургия Василия Великого

*******************************************************************************/  
function is_vasiliy_velikiy($date) {
	
	list ($year, $m, $d) = explode ('-', $date);
								
	$date_array = array_merge (	bg_get_date_by_rule ('01-01', $year),							// день памяти Василия Великого 1 (14) января;
								bg_get_date_by_rule ('0--42;0--35;0--28;0--21;0--14', $year),	// 1, 2, 3, 4 и 5-е воскресенье Великого поста;
								bg_get_date_by_rule ('0--3;0--1', $year),						// Великий четверг и Великая суббота на Страстной седмице.
								bg_get_date_by_rule ('1,2,3,4,5:12-24;01-05', $year),			// навечерия праздников Рождества Христова и Крещения 
								bg_get_date_by_rule ('7,1:12-25;01-06', $year) );				// или в самый день этих праздников, 
																								// если их навечерия выпадают в субботу или воскресенье
	
	if (in_array($date, $date_array))  return true;
	else return false;
}
/*******************************************************************************

	Функция определяет совершается ли в этот день Литургия Преждеосвященных Даров

*******************************************************************************/  
function is_grigoriy_dvoeslov ($date, $polyeles=false) {
	
	list ($year, $m, $d) = explode ('-', $date);
	
	$date_array = array_merge (	bg_get_date_by_rule('3,5:0--48,0--9', $year),	// Ср и Пт Четыредесятницы
								bg_get_date_by_rule('0--6,0--4', $year) );		// с Пн по Ср Страстной седмицы
	
	if (in_array($date, bg_get_date_by_rule('03-25', $year)))  return false;							// Благовещение 
	elseif (in_array($date, $date_array)) return true;													// Ср и Пт Четыредесятницы и с Пн по Ср Страстной седмицы
	elseif ($polyeles && in_array($date, bg_get_date_by_rule('1,2,4:0--48,0--9', $year))) return true;	// Полиелей
	else return false;
}

/*******************************************************************************

	Функция определяет совершается ли в этот день Литургия Иоанна Златоуста

*******************************************************************************/  
function is_ioann_zlatoust ($date) {
	
	list ($year, $m, $d) = explode ('-', $date);
	
	$date_array = bg_get_date_by_rule('1,2,3,4,5:0--53;0--51;0--48,0--4;0--2', $year);	// Ср и Пт Сырной седмицы и будни Великого поста, 
																						// кроме Великого Четверга							
	if (!is_vasiliy_velikiy($date) &&								// НЕ Литургия Василия Великого				
		(in_array($date, bg_get_date_by_rule('03-25', $year)) || 	// и Благовещение 
		!in_array($date, $date_array)) ) return true;				// или НЕ Великий Пост
	else return false;
}	
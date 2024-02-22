<?php
/*
	Формируем БД на год
*/
include_once ('bg_ordered_readings.php');
include_once ('sedmica.php');
	
/*******************************************************************************
	
	Функция получает базу данных календаря из файла json
	
	$year - год в формате YYYY по старому стилю
	$file - имя файла календаря
	
*******************************************************************************/  
function bg_getData($year, $file='calendar.json') {
	
	$filename = dirname(__FILE__).'/data/'.$year.'.json';

	if (file_exists($filename)) {
		$json = file_get_contents($filename);
		$data = json_decode($json, true);
		return $data;
	}	

	$locale = setlocale(LC_ALL, 0);
	$calendar_json = dirname(__FILE__).'/locale/'.$locale.'/DATA/'.$file;
	if (!file_exists($calendar_json)) $calendar_json = dirname(__FILE__).'/'.$file;
		
	$json = file_get_contents($calendar_json);
	$events = json_decode($json, true);
	
	$data = bg_getDayEvents ($year, $events);

	$json = json_encode($data, JSON_UNESCAPED_UNICODE);
	file_put_contents($filename, $json);
	
	return $data;
}

/*******************************************************************************
	
	Функция возвращает массив событий для каждого календарного дня указанного года
	
	$year - год в формате YYYY по старому стилю
	$events - события календаря
	
*******************************************************************************/  
function bg_getDayEvents ($year, $events) {
	
	$wd_name = [_("за понедельник"),_("за вторник"),_("за среду"),_("за четверг"),_("за пятницу"),_("за субботу"),_("за Неделю")];
	
	// Период триодей в текущем году
	$triod_period = bg_get_date_by_rule ('0--56,0-56', $year);
	// Светлая седмица
	$easterweek =  bg_get_date_by_rule ('0-0,0-6', $year);
	// Вселенские родительские субботы
	$universal_saturday = bg_get_date_by_rule ('0--57;0-48', $year);
	// Димитриевская родительская суббота
	$dimitry_saturday = bg_get_date_by_rule ('6:10-15;10-19,10-21;10-23,10-25', $year);
	// Ср и Пт сырной седмицы
	$wed_fri = bg_get_date_by_rule ('0--53;0--51', $year);
	// Первые 4 дня Великого поста (дни Великого покоянного канона)
	$lent_start = bg_get_date_by_rule ('0--47,0--44', $year);
	// Преполовение Великого поста
	$lent_half = bg_get_date_by_rule ('0--25', $year);
	// День Великого канона (Мариино стояние)
	$grand_canon = bg_get_date_by_rule ('0--17', $year);
	// Акафист Пресятой Богородице
	$akathist = bg_get_date_by_rule ('0--15', $year);
	
	$transfer_dates = array();
	$data = array();
	// Формируем массив по дням года
	foreach ($events as $event) {
		// Если не високосный год по ст.ст., то события 29 февраля переносим на 28-е
		if ($event['rule'] == '02-29' && $year % 4 != 0) $event['rule'] = '02-28';
		
		$dates = bg_get_date_by_rule ($event['rule'], $year);
		if (!empty($dates)) {
			foreach ($dates as $date) {
				$wd = date("N",strtotime($date));
				$old = get_old_date ($date);
				
			// Отменяем чтения
				// В период триодей чтения только на полиейные праздники и на праздники триоди
				if (in_array($date, $triod_period) && $event['level'] > 3 && $event['level'] != 8 && $event['subtype'] != 'triod') {
					$event['readings'] = array();

				// На Светлой седмице только бденные праздники
				} elseif (in_array($date, $easterweek) && $event['level'] > 2 && $event['level'] != 8) {
					$event['readings'] = array();
				}
				
				// По воскресеньям на Утрени нет чтений праздников кроме двунадесятых
				if ($wd == 7 && $event['level'] > 0) $event['readings']['morning'] = '';
				
			// Переносим праздники (кроме Двунадесятых, Великих и бденных, а также особых дней)
				if (!empty($event['readings']) && (($event['level'] > 2 && $event['level'] != 8) || in_array($event['subtype'],['prefeast']))) {
					
				// Во Вселенские родительские субботы праздники переносим на предыдущий Чт
					if (in_array($date, $universal_saturday)) {
						$event['title'] .= ' '.sprintf(_('(перенос с %s ст.ст.)'), $old);
						$newdate = date ('Y-m-d', strtotime($date.'- 2 days'));
						$data[$newdate]['events'][] = $event;

				// В Димитриевскую родительскую субботу праздники переносим на предыдущую Пт
					} elseif (in_array($date, $dimitry_saturday)) {
						$event['title'] .= ' '.sprintf(_('(перенос с %s ст.ст.)'), $old);
						$newdate = date ('Y-m-d', strtotime($date.'- 1 days'));
						$data[$newdate]['events'][] = $event;
					
				// В среду и пятницу сырной седмицы полиелейные праздники меняем на вседневные предыдущего дня
					} elseif (in_array($date, $wed_fri)) {
						$event['title'] .= ' '.sprintf(_('(перенос с %s ст.ст.)'), $old);
						$newdate = date ('Y-m-d', strtotime($date.'- 1 days'));
						$data[$newdate]['events'][] = $event;
						$transfer_dates[$newdate] = $date;

				// Первые 4 дня Великого поста праздники переносим на следующую Сб
					} elseif (in_array($date, $lent_start)) {
						$event['title'] .= ' '.sprintf(_('(перенос с %s ст.ст.)'), $old);
						$newdate = bg_get_new_date ('0--43', $year);
						$data[$newdate]['events'][] = $event;
						$transfer_dates[$newdate] = $date;

				// В Ср 4-й седмицы, то есть в преполовение Великого поста праздники переносим на Вт 4-й седмицы
					} elseif (in_array($date, $lent_half)) {
						$event['title'] .= ' '.sprintf(_('(перенос с %s ст.ст.)'),  $old);
						$newdate = bg_get_new_date ('0--26', $year);
						$data[$newdate]['events'][] = $event;
						$transfer_dates[$newdate] = $date;

				// В Чт 5-й седмицы — в службу Великого канона праздники переносим на Вт 5-й седмицы
					} elseif (in_array($date, $grand_canon)) {
						$event['title'] .= ' '.sprintf(_('(перенос с %s ст.ст.)'), $old);
						$newdate = bg_get_new_date ('0--19', $year);
						$data[$newdate]['events'][] = $event;
						$transfer_dates[$newdate] = $date;

				// В субботу Акафиста (Сб 5-й седмицы) праздники переносим на Неделю 5-ю Великого поста
					} elseif (in_array($date, $akathist)) {
						$event['title'] .= ' '.sprintf(_('(перенос с %s ст.ст.)'), $old);
						$newdate = bg_get_new_date ('0--14', $year);
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

	// Если в день (date), откуда был перенесен полиелейный праздник нет вседневных событий со службой, 
	// то переносим на него первый вседневный праздник дня (newdate), куда был перенесен полиелей
	foreach ($transfer_dates as $newdate => $date) {
		$event_exist = false;
		foreach($data[$date]['events'] as $ev) {
			if ($ev['type'] == 'event' && in_array($ev['level'], [5, 6])) $event_exist = true;
		}
		if (!$event_exist) { 
			foreach ($data[$newdate]['events'] as $k=>$event) {
				if (in_array($event['level'], [5, 6]) && !empty($event['minea_id'])&& $event['subtype'] != 'triod') {
					$old = get_old_date ($newdate);
					$event['title'] .= ' '.sprintf(_('(перенос с %s ст.ст.)'), $old);
					$data[$date]['events'][] = $event;
					unset ($data[$newdate]['events'][$k]);
					break;
				}
			}
		}
	}
	
	// Дополним массив данных по дням дополнительной информацией
	foreach ($data as $date => $value) {
		$wd = date("N",strtotime($date));
		
		$festivity_ind = '';		// Празднество
		$festivity_type = '';
		$festivity_subtype = '';
		$special_ind = '';			// Особый день
		$day_type = '';
		$day_subtype = '';
		$tipicon_events = array(); 
		$top_events = array(); 
		$icon_title = '';
		$icon = '';
		$main_ind = '';
		$main_level = '';
		$main_type = '';
		$main_subtype = '';
		$main_feast_type = '';
		$main_rank = 0;
		$second_ind = '';

		// Сортируем события по специальным группам
		foreach ($value['events'] as $key => $event) {
		// Пред-/попразднство
			if ($event['type'] == 'festivity') {
				$festivity_ind = $key;
				$festivity_type = $event['type'];
				$festivity_subtype = $event['subtype'];
		// Особый день
			} elseif ($event['type'] != 'event') {
				$special_ind = $key;
				$day_type = $event['type'];
				$day_subtype = $event['subtype'];
		// Есть служба, если она имеет знак Типикона или помечена как вседневная
			} elseif ($event['level'] < 7) {
				 $tipicon_events[] = $key;
			}
		// Рекомендован повышенный уровень службы
			if (!empty($event['top_level'])) {
				 $top_events[] = $key;
			}
		}
		
		// Если вселенская родительская суббота или навечерие, или воскресный день в период Триодей
		// то это главный праздник
		if (!is_blank($special_ind) && in_array($day_subtype, ['universal_saturday', 'eve', 'sunday'] )) {
			$main_ind = $special_ind;
			$event = $value['events'][$special_ind];
			$main_level = $event['level'];
			$main_type = $event['type'];
			$main_subtype = $event['subtype'];
			$main_feast_type = $event['feast_type'];
			$main_rank = 0;
			if (!empty($event['imgs'])) {
				$icon_title = $event['title'];
				$icon = $event['imgs'][0];
			}
		// Четверток Великого канона - главный праздник
		} elseif (in_array($date, $grand_canon)) {
			$main_ind = $special_ind;
			$event = $value['events'][$special_ind];
			$main_level = $event['level'];
			$main_type = $event['type'];
			$main_subtype = $event['subtype'];
			$main_feast_type = $event['feast_type'];
			$main_rank = 0;
			if (!empty($event['imgs'])) {
				$icon_title = $event['title'];
				$icon = $event['imgs'][0];
			}
						
		// Отдание считаем главным праздником
		} elseif ($festivity_ind != '' && $value['events'][$festivity_ind]['subtype'] == 'feastend' &&
			!in_array($date, bg_get_date_by_rule (['01-07;11-25','1:12-26'], $year))) {	// Кроме Собора Предтечи, отдания Введения и Собора Богородицы в Пн (с Неделей Богоотец)

			$main_ind = $festivity_ind;
			$event = $value['events'][$festivity_ind];
			$main_level = $event['level'];
			$main_type = $event['type'];
			$main_subtype = $event['subtype'];
			$main_feast_type = $event['feast_type'];
			$main_rank = 0;
			if (!empty($event['imgs'])) {
				$icon_title = $event['title'];
				$icon = $event['imgs'][0];
			}
			
		
		
		// Найдем главный праздник и икону дня в списке праздников Типикона
		} elseif (sizeof($tipicon_events)) {
			// По умолчанию: Первый элемент в списке
			$main_ind = $tipicon_events[0];
			$ev = $value['events'][$main_ind];				
			$main_level = $ev['level'];
			$main_type = $ev['type'];
			$main_subtype = $ev['subtype'];
			$main_feast_type = $ev['feast_type'];
			$main_rank = intval($main_feast_type.$main_level);
			if (!empty($ev['imgs'])) {
				$icon_title = $ev['title'];
				$icon = $ev['imgs'][0];
			}
			
		}
		// Если есть двунадесятый, великий или бденный праздник, то это Главный праздник 
		foreach ($tipicon_events as $ind) {
			$event = $value['events'][$ind];
			$rank = intval($event['feast_type'].$event['level']);
			if ((!$main_rank || $main_rank > $rank) && $event['level'] <= 2) {
				$main_ind = $ind;
				$main_level = $event['level'];
				$main_type = $event['type'];
				$main_subtype = $event['subtype'];
				$main_feast_type = $event['feast_type'];
				$main_rank = intval($main_feast_type.$main_level);
				if (!empty($event['imgs'])) {
					$icon_title = $event['title'];
					$icon = $event['imgs'][0];
				}
			}
		}
		
	// Второе событие дня (по умолчанию - нет)
		$second_ind = '';
		if (!is_blank($main_ind) && $value['events'][$main_ind]['dual_worship'] > 0) {	// Двойной праздник
			foreach ($value['events'] as $key => $event) {
				// Одинаковый номер пары и другой id 
				if ($event['dual_worship'] == $value['events'][$main_ind]['dual_worship'] && $key != $main_ind) {
					$second_ind = $key;
					break;
				}
			}
		}
		// В попразднство в Неделю совмещение служб отменяется
		// а также в родительские субботы
		if (($wd == 7 && !is_blank($festivity_ind)) || $day_subtype == 'saturday_honor_dead') {
			foreach ($value['events'] as $key => $event) {
				if ($event['dual_worship'] > 0) {
					$data[$date]['events'][$key]['dual_worship'] = 0;
				}
			}
			$second_ind = '';
		} 
		
		
		// Если у главного праздника нет иконы, то найдем первую в списке
		if (empty($icon)) {
			foreach ($value['events'] as $event) {
				if (!empty($event['imgs'])) {
					$icon_title = $event['title'];
					$icon = $event['imgs'][0];
					break;
				}
			}
		}
		
		// Тип литургии
		if (is_ioann_zlatoust ($date)) $liturgy = 1;
		elseif (is_vasiliy_velikiy($date)) $liturgy = 2;
		elseif (is_grigoriy_dvoeslov ($date, $main_level <= 3)) $liturgy = 3;
		elseif (is_grigoriy_dvoeslov ($date, true)) $liturgy = 4;
		else $liturgy = 0;

		// Добавляем в БД основные параметры дня
		$data[$date]['festivity_ind'] = $festivity_ind ?? '';		// Пред-/Попразднство (индекс)
		$data[$date]['festivity_type'] = $festivity_type ?? '';		// Тип празднства
		$data[$date]['festivity_subtype'] = $festivity_subtype??''; // Подтип празднства
		$data[$date]['special_ind'] = $special_ind ?? '';			// Особый день (индекс)
		$data[$date]['day_type'] = $day_type ?? '';					// Тип особого дня
		$data[$date]['day_subtype'] = $day_subtype ?? '';			// Подтип особого дня
		$data[$date]['main_ind'] = $main_ind ?? '';					// Главное событие дня (индекс)
		$data[$date]['main_level'] = $main_level ?? '';				// Уровень главного события дня
		$data[$date]['main_type'] = $main_type ?? '';				// Тип главного события дня
		$data[$date]['main_subtype'] = $main_subtype ?? '';			// Подтип главного события дня
		$data[$date]['main_feast_type'] = $main_feast_type ?? '';	// Тип принадлежности главного события дня
		$data[$date]['main_rank'] = $main_rank ?? '';				// Ранг события

		$data[$date]['second_ind'] = $second_ind ?? '';				// Второе событие дня (индекс)
		$data[$date]['tipicon_events'] = $tipicon_events ?? '';		// Службы по Типикону
		$data[$date]['top_events'] = $top_events ?? '';				// Службы, для которых рекомендован повышенный уровень
		$data[$date]['icon'] = $icon ?? '';							// Икона дня
		$data[$date]['icon_title'] = $icon_title ?? '';				// Название иконы дня
		
	
		$data[$date]['liturgy'] = $liturgy;							// Тип литургии
		$data[$date]['sedmica'] = bg_sedmica ($date);				// Название седмицы/Недели
		$data[$date]['tone'] = bg_getTone ($date);					// Глас Октоиха
		$data[$date]['food'] = bg_getFood ($date);					// Рекомендации пищи
		
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
			if (!($data[$date]['main_level'] == 0 && $data[$date]['main_feast_type'] == '1') && 	// НЕ господский в любой день,
				!($data[$date]['main_level'] <= 2 &&												// НЕ Великий и Бденный
				$date != bg_get_new_date ('09-01', $y)  && $wd < 7) &&								// и НЕ Новолетие в будни,
				$data[$date]['main_type'] != 'eve') {												// НЕ Навечерие

			// Проверяем переносы рядовых чтений на сегодня

				// Вчера Великий или бденный праздник и сегодня вторник
				// или со вторника по субботу и позавчера Великий или бденный праздник
				if (!empty($data[$yesterday]) && 																	// Вчера: 
					(($data[$yesterday]['main_level'] == 0 && $data[$yesterday]['main_feast_type'] == '1') || 	// Господский,
						($data[$yesterday]['main_level'] <= 2 && $wd_y < 7) ||											// или Великий и Бденный в будни
						$data[$yesterday]['main_type'] == 'eve') &&														// или Навечерие
					($wd == 2 || 																					// и сегодня Вторник
					($wd > 1 && $wd < 7 && 																			// или сегодня Вт,Ср,Чт,Пт или Сб
					!empty($data[$before_yesterday]) && 																	// и позавчера: 
					(($data[$before_yesterday]['main_level'] == 0 && $data[$before_yesterday]['main_feast_type'] == '1') || 	// Господский,
						($data[$before_yesterday]['main_level'] <= 2 && $wd_by < 7) ||												// или Великий или Бденный в будни
						$data[$before_yesterday]['main_type'] == 'eve')))) {														// или Навечерие
				
					$readings[] = (array) $or->bg_day_readings ($yesterday, $wd_name[$wd_y-1]);
				}

				// Рядовые чтений на сегодня
				$ordinary = (array) $or->bg_day_readings ($date, _("рядовое"));
				if ($data[$date]['day_type'] == 'weekend' &&
					in_array($data[$date]['day_subtype'], ['sunday_before', 'sunday_after','sunday'])) { 
					$ordinary['apostle'] = '';
					$ordinary['gospel'] = '';
				}
				$ordinary = array_diff($ordinary, array('', false, null));
				if (count($ordinary) <= 1) $ordinary = array();
				$readings[] = $ordinary;

				// Будни и завтра Великий или бденный праздник
				if ($wd < 6 && !empty($data[$tomorrow]) && 															// Завтра:
					(($data[$tomorrow]['main_level'] == 0 && $data[$tomorrow]['main_feast_type'] == '1') || 		// Господский,
						($data[$tomorrow]['main_level'] <= 2 && $wd_t < 7) ||											// или Великий или Бденный в будни
						$tomorrow == bg_get_new_date ('09-01', $y) ||													// или Новолетие
						$data[$tomorrow]['main_type'] == 'eve')) {														// или Навечерие
					
					$readings[] = (array) $or->bg_day_readings ($tomorrow, $wd_name[$wd_t-1]);
				} 
			} elseif ($data[$date]['main_level'] <= 2 && $wd == 1 && !empty($data[$tomorrow]) && 					// Сегодня Великий или Бденный, Пн, и Завтра:
					(($data[$tomorrow]['main_level'] == 0 && $data[$tomorrow]['main_feast_type'] == '1') || 		// Господский,
						($data[$tomorrow]['main_level'] <= 2 && $wd_t < 7) ||											// или Великий и Бденный в будни
						$data[$tomorrow]['main_type'] == 'eve') ) {														// или Навечерие
				$readings[] = (array) $or->bg_day_readings ($date, _("рядовое"));					
			}
		} else $readings[] = (array) $or->bg_day_readings ($date, '');
		
		if ($date == bg_get_new_date ('01-07', $y) || 			// В Собор Богородицы
			$date == bg_get_new_date ('12-26', $y)) 			// и в Собор Предтечи 
				$data[$date]['ordinary_readings'] = array();	// рядовые чтения отменяются

		else $data[$date]['ordinary_readings'] = $readings;		// Рядовые чтения
		$data[$date] = array_slice($data[$date], 1, count($data[$date])-1, true) + array('events' => $data[$date]['events']);
		
	}

	return $data;
}

/*******************************************************************************

	Функция определяет совершается ли в этот день Литургия Василия Великого

*******************************************************************************/  
function is_vasiliy_velikiy($date) {
	
	$old = bg_get_old_date ($date);
	list ($year, $m, $d) = explode ('-', $old);
								
	$date_array = array_merge (	bg_get_date_by_rule ('01-01', $year),							// день памяти Василия Великого 1 (14) января;
								bg_get_date_by_rule ('0--42;0--35;0--28;0--21;0--14', $year),	// 1, 2, 3, 4 и 5-е воскресенье Великого поста;
								bg_get_date_by_rule ('0--3;0--1', $year),						// Великий четверг и Великая суббота на Страстной седмице.
								bg_get_date_by_rule ('1,2,3,4,5:12-24;01-05', $year),			// навечерия праздников Рождества Христова и Крещения 
								bg_get_date_by_rule ('1,7:12-25;01-06', $year) );				// или в самый день этих праздников, 
																								// если их навечерия выпадают в субботу или воскресенье
	if (in_array($date, $date_array)) return true;
	else return false;
}
/*******************************************************************************

	Функция определяет совершается ли в этот день Литургия Преждеосвященных Даров

*******************************************************************************/  
function is_grigoriy_dvoeslov ($date, $polyeles=false) {
	
	list ($year, $m, $d) = explode ('-', $date);
	
	$date_array = array_merge (	bg_get_date_by_rule('3,5:0--48,0--9', $year),	// Ср и Пт Четыредесятницы
								bg_get_date_by_rule('0--17', $year),			// Чт 5-ой седмицы, Мариинино стояние
								bg_get_date_by_rule('0--6,0--4', $year) );		// с Пн по Ср Страстной седмицы
	
	if (in_array($date, bg_get_date_by_rule('03-25', $year)))  return false;							// Благовещение 
	elseif (in_array($date, $date_array)) return true;													// Ср и Пт Четыредесятницы и с Пн по Ср Страстной седмицы
	elseif ($polyeles && in_array($date, bg_get_date_by_rule('1,2,4:0--41,0--9', $year))) return true;	// Полиелей
	else return false;
}

/*******************************************************************************

	Функция определяет совершается ли в этот день Литургия Иоанна Златоуста

*******************************************************************************/  
function is_ioann_zlatoust ($date) {
	
	$old = bg_get_old_date ($date);
	list ($year, $m, $d) = explode ('-', $old);
	
	$date_array = bg_get_date_by_rule('1,2,3,4,5:0--53;0--51;0--48,0--4;0--2', $year);	// Ср и Пт Сырной седмицы и будни Великого поста, 
																						// кроме Великого Четверга
	$date_array = array_merge($date_array, bg_get_date_by_rule('1,2,3,4,5:01-05;12-24', $year));	// Навечерия РХ и Богоявления
	$date_array = array_merge($date_array, bg_get_date_by_rule('5:01-03,01-04;12-22,12-23', $year));
																						
	if (!is_vasiliy_velikiy($date) &&								// НЕ Литургия Василия Великого				
		(in_array($date, bg_get_date_by_rule('03-25', $year)) || 	// и Благовещение 
		!in_array($date, $date_array)) ) return true;				// или НЕ Великий Пост и НЕ Навечерия РХ и Богоявления
	else return false;
}	

/*******************************************************************************

	Функция возвращает тропари и кондаки дня

*******************************************************************************/  
function bg_tropary_days ($date) {

	$old = bg_get_old_date ($date);

	list($y, $m, $d) = explode('-', $old);
	$wd = date("N",strtotime($date));

	$easter = bg_get_easter((int)$y);
	$antieaster = bg_get_easter((int)$y, 7);
	$our_lord = bg_get_date_by_rule('01-06;02-02|0--49;0--7,0-0;0-39;0-49;08-06;09-14;12-25', $y);	// Господские двунадесятые
	$our_lady = bg_get_date_by_rule('03-25;08-15;09-08;11-21', $y);	// Богородичные двунадесятые

	if (in_array ($date, $our_lord)) return '';					// В Страстную Седмицу, на Пасху и в господские двунадесятые праздники ничего не выводить
	elseif (in_array ($date, $our_lady) && $wd != 7) return '';	// В богородичные двунадесятые праздники по будням ничего не выводить
	elseif ($date > $easter && $date < $antieaster) {			// На Светлой седмице Пасхальные тропари и кондаки
		$wd = 0;
		$tone = 0;
	} else {											// Дня или гласа
		$tone = bg_getTone($date);
	}
		
	$locale = setlocale(LC_ALL, 0);
	$tropary_json = dirname(__FILE__).'/locale/'.$locale.'/DATA/tropary.json';
	if (!file_exists($tropary_json)) $tropary_json = dirname(__FILE__).'/tropary.json';
	
	$json = file_get_contents($tropary_json);
	$tropary = json_decode($json, true);
	
	if ($wd == 7) {
		$found_key = array_search($tone, array_column($tropary, 'voice'));
	} else {
		$found_key = array_search($wd, array_column($tropary, 'wd'));
	}

	return $tropary[$found_key];
}

/*************************************************************************************
	Функция переводит абревиатуру книг на язык локали и формирует гиперссылки на сайт Библии

	Параметры:
		$reference - ссылка на Библию на русском языке
		$customLink - имя пользовательской функции, формирующей ссылку на сайт Библии пользователя
		
	Возвращает ссылку на отрывок Св.Писания
		
**************************************************************************************/
// 
function blink ($reference, $customLink) {
	$bg_bibrefs_abbr = array(		// Стандартные обозначение книг Священного Писания
		// Ветхий Завет
		// Пятикнижие Моисея															
		'Gen'		=>"Быт", 
		'Ex'		=>"Исх", 
		'Lev'		=>"Лев",
		'Num'		=>"Чис",
		'Deut'		=>"Втор",
		// «Пророки» (Невиим) 
		'Nav'		=>"Нав",
		'Judg'		=>"Суд",
		'Rth'		=>"Руф",
		'1Sam'		=>"1Цар",
		'2Sam'		=>"2Цар",
		'1King'		=>"3Цар",
		'2King'		=>"4Цар",
		'1Chron'	=>"1Пар",
		'2Chron'	=>"2Пар",
		'Ezr'		=>"1Езд",
		'Nehem'		=>"Неем",
		'Est'		=>"Есф",
		// «Писания» (Ктувим)
		'Job'		=>"Иов",
		'Ps'		=>"Пс",
		'Prov'		=>"Притч", 
		'Eccl'		=>"Еккл",
		'Song'		=>"Песн",
		'Is'		=>"Ис",
		'Jer'		=>"Иер",
		'Lam'		=>"Плч",
		'Ezek'		=>"Иез",
		'Dan'		=>"Дан",	
		// Двенадцать малых пророков 
		'Hos'		=>"Ос",
		'Joel'		=>"Иоил",
		'Am'		=>"Ам",
		'Avd'		=>"Авд",
		'Jona'		=>"Ион",
		'Mic'		=>"Мих",
		'Naum'		=>"Наум",
		'Habak'		=>"Авв",
		'Sofon'		=>"Соф",
		'Hag'		=>"Аг",
		'Zah'		=>"Зах",
		'Mal'		=>"Мал",
		// Второканонические книги
		'1Mac'		=>"1Мак",
		'2Mac'		=>"2Мак",
		'3Mac'		=>"3Мак",
		'Bar'		=>"Вар",
		'2Ezr'		=>"2Езд",
		'3Ezr'		=>"3Езд",
		'Judf'		=>"Иудиф",
		'pJer'		=>"ПослИер",
		'Solom'		=>"Прем",
		'Sir'		=>"Сир",
		'Tov'		=>"Тов",
		// Новый Завет
		// Евангилие
		'Mt'		=>"Мф",
		'Mk'		=>"Мк",
		'Lk'		=>"Лк",
		'Jn'		=>"Ин",
		// Деяния и послания Апостолов
		'Act'		=>"Деян",
		'Jac'		=>"Иак",
		'1Pet'		=>"1Пет",
		'2Pet'		=>"2Пет",
		'1Jn'		=>"1Ин", 
		'2Jn'		=>"2Ин",
		'3Jn'		=>"3Ин",
		'Juda'		=>"Иуд",
		// Послания апостола Павла
		'Rom'		=>"Рим",
		'1Cor'		=>"1Кор",
		'2Cor'		=>"2Кор",
		'Gal'		=>"Гал",
		'Eph'		=>"Еф",
		'Phil'		=>"Флп",
		'Col'		=>"Кол",
		'1Thes'		=>"1Сол",
		'2Thes'		=>"2Сол",
		'1Tim'		=>"1Тим",
		'2Tim'		=>"2Тим",
		'Tit'		=>"Тит",
		'Phlm'		=>"Флм",
		'Hebr'		=>"Евр",
		'Apok'		=>"Отк");


	$bg_bibrefs_translate = array(		// Перевод обозначений книг Священного Писания
		// Ветхий Завет
		// Пятикнижие Моисея															
		'Gen'		=>_("Быт"), 
		'Ex'		=>_("Исх"), 
		'Lev'		=>_("Лев"),
		'Num'		=>_("Чис"),
		'Deut'		=>_("Втор"),
		// «Пророки» (Невиим) 
		'Nav'		=>_("Нав"),
		'Judg'		=>_("Суд"),
		'Rth'		=>_("Руф"),
		'1Sam'		=>_("1Цар"),
		'2Sam'		=>_("2Цар"),
		'1King'		=>_("3Цар"),
		'2King'		=>_("4Цар"),
		'1Chron'	=>_("1Пар"),
		'2Chron'	=>_("2Пар"),
		'Ezr'		=>_("1Езд"),
		'Nehem'		=>_("Неем"),
		'Est'		=>_("Есф"),
		// «Писания» (Ктувим)
		'Job'		=>_("Иов"),
		'Ps'		=>_("Пс"),
		'Prov'		=>_("Притч"), 
		'Eccl'		=>_("Еккл"),
		'Song'		=>_("Песн"),
		'Is'		=>_("Ис"),
		'Jer'		=>_("Иер"),
		'Lam'		=>_("Плч"),
		'Ezek'		=>_("Иез"),
		'Dan'		=>_("Дан"),	
		// Двенадцать малых пророков 
		'Hos'		=>_("Ос"),
		'Joel'		=>_("Иоил"),
		'Am'		=>_("Ам"),
		'Avd'		=>_("Авд"),
		'Jona'		=>_("Ион"),
		'Mic'		=>_("Мих"),
		'Naum'		=>_("Наум"),
		'Habak'		=>_("Авв"),
		'Sofon'		=>_("Соф"),
		'Hag'		=>_("Аг"),
		'Zah'		=>_("Зах"),
		'Mal'		=>_("Мал"),
		// Второканонические книги
		'1Mac'		=>_("1Мак"),
		'1Mac'		=>_("2Мак"),
		'3Mac'		=>_("3Мак"),
		'Bar'		=>_("Вар"),
		'2Ezr'		=>_("2Езд"),
		'3Ezr'		=>_("3Езд"),
		'Judf'		=>_("Иудиф"),
		'pJer'		=>_("ПослИер"),
		'Solom'		=>_("Прем"),
		'Sir'		=>_("Сир"),
		'Tov'		=>_("Тов"),
		// Новый Завет
		// Евангилие
		'Mt'		=>_("Мф"),
		'Mk'		=>_("Мк"),
		'Lk'		=>_("Лк"),
		'Jn'		=>_("Ин"),
		// Деяния и послания Апостолов
		'Act'		=>_("Деян"),
		'Jac'		=>_("Иак"),
		'1Pet'		=>_("1Пет"),
		'2Pet'		=>_("2Пет"),
		'1Jn'		=>_("1Ин"), 
		'2Jn'		=>_("2Ин"),
		'3Jn'		=>_("3Ин"),
		'Juda'		=>_("Иуд"),
		// Послания апостола Павла
		'Rom'		=>_("Рим"),
		'1Cor'		=>_("1Кор"),
		'2Cor'		=>_("2Кор"),
		'Gal'		=>_("Гал"),
		'Eph'		=>_("Еф"),
		'Phil'		=>_("Флп"),
		'Col'		=>_("Кол"),
		'1Thes'		=>_("1Сол"),
		'2Thes'		=>_("2Сол"),
		'1Tim'		=>_("1Тим"),
		'2Tim'		=>_("2Тим"),
		'Tit'		=>_("Тит"),
		'Phlm'		=>_("Флм"),
		'Hebr'		=>_("Евр"),
		'Apok'		=>_("Отк"));


	$bg_bibrefs_name = array_flip($bg_bibrefs_abbr);
	
	$reference = preg_replace('/((\xA0)|\s)+/u', '', $reference); // Уберем пробелы

	$refs = explode (';', $reference);			// Несколько ссылок разделенных точкой с запятой
	$hlink = '';
	foreach($refs as $ref) {
		list($name, $ch) = explode('.',$ref);	// Разделим ссылку на аббревиатуру и номера глав и стихов

		$abbr = $bg_bibrefs_name[$name];		// Английская аббревиатура книги 
		$book = $bg_bibrefs_translate[$abbr];	// Перевод названия книги
		
		// Вызываем пользовательскую функцию для формирования ссылки на Писание
		if (function_exists($customLink)) $hlink .= $customLink ($abbr, $book, $ch).'; ';
		else $hlink .= $ref.'; ';
	}
	$hlink = substr($hlink,0,-2);
	return $hlink;
}

// Пустая переменная (0, 0.0 и '0' - значимые)
function is_blank ($var) {
	if (is_object($var)) return $var->isEmpty();
	if (is_array($var)) return sizeof($var)?false:true;
	if (empty ($var) && $var != 0 && $var !=0.0 && $var != '0') return true;
	else return false;
}
	
// Возвращает дату по старому стилю в формате: день месяц
function get_old_date ($date) {
	list($year, $m, $d) = explode('-', $date);
	$dd = bg_ddif($year);
	$old = date("j-m", strtotime($date.'- '.$dd.' days'));
	$old = preg_replace_callback ('/(\d+)\-(\d+)/u', function ($matches) {
			$monthes = [_("января"),_("февраля"),_("марта"),_("апреля"),_("мая"),_("июня"),_("июля"),_("августа"),_("сентября"),_("октября"),_("ноября"),_("декабря")];
			return $matches[1].' '.$monthes[$matches[2]-1];
		}, $old);
	return $old;
}
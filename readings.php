<?php
/*
	Формируем БД на год
*/
include_once ('bg_ordered_readings.php');
include_once ('sedmica.php');

/*******************************************************************************
	
	Функция получает базу данных календаря из файла json
	и возвращает массив событий для каждого календарного дня указанного года
	
	$year - год в формате YYYY по старому стилю
	
*******************************************************************************/  
function bg_getData($year, $file='calendar.json') {
	
	$wd_name = [_("за понедельник"),_("за вторник"),_("за среду"),_("за четверг"),_("за пятницу"),_("за субботу"),_("за Неделю")];
	
	$filename = 'data/'.$year.'.json';
/*
	if (file_exists($filename)) {
		$json = file_get_contents($filename);
		$data = json_decode($json, true);
		return $data;
	}	
*/
	$locale = setlocale(LC_ALL, 0);
	$calendar_json = './locale/'.$locale.'/DATA/'.$file;
	if (!file_exists($calendar_json)) $calendar_json = $file;
	
	$json = file_get_contents($calendar_json);
	$events = json_decode($json, true);
	
	// Период триодей в текущем году
	$triod_period = bg_get_date_by_rule ('0--56,0-56', $year);
	// Светлая седмица, Вселенские и Димитриевская  родительские субботы, первые 4 дня Великого поста, преполовение Великого поста,
	$easterweek =  bg_get_date_by_rule ('0-0,0-6', $year);
	$universal_saturday = bg_get_date_by_rule ('0--57;0-48', $year);
	$dimitry_saturday = bg_get_date_by_rule ('6:10-15;10-19,10-21;10-23,10-25', $year);
	$lent_start = bg_get_date_by_rule ('0--47,0--44', $year);
	$lent_half = bg_get_date_by_rule ('0--25', $year);
	$grand_canon = bg_get_date_by_rule ('0--17', $year);
	$akathist = bg_get_date_by_rule ('0--15', $year);
	$data = array();
	// Формируем массив по дням года
	foreach ($events as $event) {
		// Если не високосный год по ст.ст., то события 29 февраля переносим на 28-е
		if ($event['rule'] == '02-29' && $year % 4 != 0) $event['rule'] = '02-28';
		$dates = bg_get_date_by_rule ($event['rule'], $year);
		if (!empty($dates)) {
			foreach ($dates as $date) {
				$dd = bg_ddif($year);
				$old = date("j-m", strtotime($date.'- '.$dd.' days'));
				$old = preg_replace_callback ('/(\d+)\-(\d+)/u', function ($matches) {
						$monthes = [_("января"),_("февраля"),_("марта"),_("апреля"),_("мая"),_("июня"),_("июля"),_("августа"),_("сентября"),_("октября"),_("ноября"),_("декабря")];
						return $matches[1].' '.$monthes[$matches[2]-1];
					}, $old);
				
			// Отменяем чтения
				// В период триодей чтения только на полиейные праздники
				if (in_array($date, $triod_period) && $event['level'] > 3 && $event['level'] != 8) {
					$event['readings'] = array();

				// На Светлой седмице нет праздников святых
				} elseif (in_array($date, $easterweek) && $event['level'] > 1 && $event['level'] != 8) {
					$event['readings'] = array();
				}
				
			// Переносим праздники
				if (!empty($event['readings']) && (($event['level'] > 1 && $event['level'] != 8) || in_array($event['subtype'],['prefeast']))) {
					
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
					
				// Первые 4 дня Великого поста праздники переносим на следующую Сб
					} elseif (in_array($date, $lent_start)) {
						$event['title'] .= ' '.sprintf(_('(перенос с %s ст.ст.)'), $old);
						$newdate = bg_get_new_date ('0--43', $year);
						$data[$newdate]['events'][] = $event;

				// В Ср 4-й седмицы, то есть в преполовение Великого поста праздники переносим на Вт 4-й седмицы
					} elseif (in_array($date, $lent_half)) {
						$event['title'] .= ' '.sprintf(_('(перенос с %s ст.ст.)'),  $old);
						$newdate = bg_get_new_date ('0--26', $year);
						$data[$newdate]['events'][] = $event;

				// В Чт 5-й седмицы — в службу Великого канона праздники переносим на Вт 5-й седмицы
					} elseif (in_array($date, $grand_canon)) {
						$event['title'] .= ' '.sprintf(_('(перенос с %s ст.ст.)'), $old);
						$newdate = bg_get_new_date ('0--19', $year);
						$data[$newdate]['events'][] = $event;

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
			} elseif (in_array($event['subtype'], ['', 'feastend'] )) {
				$afterfeast = $event['subtype'];
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
		if (is_ioann_zlatoust ($date)) $liturgy = 1;
		elseif (is_vasiliy_velikiy($date)) $liturgy = 2;
		elseif (is_grigoriy_dvoeslov ($date, $main_level <= 3)) $liturgy = 3;
		else $liturgy = 0;

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
				$ordinary = (array) $or->bg_day_readings ($date, _("Ряд."));
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
				$readings[] = (array) $or->bg_day_readings ($date, _("Ряд."));					
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
								bg_get_date_by_rule('0--17', $year),			// Чт 5-ой седмицы, Мариинино стояние
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
	$tropary_json = './locale/'.$locale.'/DATA/tropary.json';
	if (!file_exists($tropary_json)) $tropary_json = 'tropary.json';
	
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
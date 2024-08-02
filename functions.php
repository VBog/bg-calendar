<?php
/* 
	Функции общего назначения
*/

/*******************************************************************************
	Функция возвращает текущую дату
	Параметры:
		$shift - смещение в днях от текущей даты
	Возвращает:
		Дата в формате Y-m-d
		
	bg_сustomDate() - внешняя функция, позволяющая изменять текущую дату
	
*******************************************************************************/  
function bg_currentDate($shift=0) {
	$date = '';

	if (isset($_REQUEST["date"])) {		// Задана дата
		$date = $_REQUEST["date"];
	}

	if (empty ($date)) {			// Текущая дата
		if (function_exists('bg_сustomDate')) $date = bg_сustomDate();
		else $date = date('Y-m-d', time());
	}
	// Проверяем на валидность, если ошибка, то текущая дата
	$day = explode ('-', $date);
	if (count($day) != 3 || !checkdate($day[1], $day[2], $day[0])){ 
		$date = date('Y-m-d', time()); 
	}
	
	if ($shift) {
		list($year, $m, $d) = explode ('-', $date);
		$date = date( 'Y-m-d', mktime ( 0, 0, 0, (int)$m, $d+$shift, (int)$year ) );
	}
	return $date;
}

/*******************************************************************************
	Функция вычисляет дату по заданному правилу для текущего года
	
	Параметры:
		$year - год в формате YYYY
	
		$rules - правила в формате
		
		дни_недели:интервал_дат
		
		Если правило одно, оно может быть задано как строка, иначе это массив строк.
		
		В качестве правила может быть задано имя встроенной функции 
		(например, "afterfeastCandlemas" или "feastendCandlemas"), 
		которой в качестве параметра передается $year
		и которая возвращает интервал в указанном здесь формате.
		
		** Формат интервалов дат **
		Интервал дат задаётся по Юлианскому календарю в формате m1-d1,m2-d2
		где m1 и d1 - месяц и день начала интервала, 
			m2 и d2 - месяц и день конца интервала.
		Если продолжительность интервала 1 день, 
		дату окончания интервала можно опустить: m-d
		
		Для перходящих дат m=0, а d - количество дней до(-) или после(+) Пасхи 
		в текущем году.
		
		Границы интервала можно задавать в виде опции: m1-d1,m2-d2|m2a-d2a, 
		при этом в качестве границы будет выбрана более ранняя дата.
		
		Если необходимо задать несколько интервалов, они разделяются между собой 
		точкой с запятой:
		m1-d1,m2-d2;m3-d3,m4-d4;m5-d5,m6-d6
		
		Если интервал не задан (пусто), это означает любую дату.
		
		** Дни недели **
		$days - допустимые дни недели (от 1 до 7)
		Задаются через запятую. Если не заданы (пусто) - любой день недели
	
	Возвращает:
		массив доступных дат по Григорианскому календарю в формате Y-m-d, 
		если дата не соответствует заданному правилу, то пустой массив
		
*******************************************************************************/
function bg_get_date_by_rule ($rules, $year) {

	$rules_array = array();
	$dates_array = array();
	if (!is_array($rules)) {
		$fn = explode('=', $rules);
		if (function_exists($fn[0])) {
			if (count($fn) > 1) $rules_array[0] = $fn[0]($year, $fn[1]);
			else $rules_array[0] = $fn[0]($year);
			if (empty($rules_array[0])) return array(); 
		} else $rules_array[0] = $rules;
	} else $rules_array = $rules;

	foreach ($rules_array as $rule) {
		// Разбираем правило на дни недели и интервлы дат
		$rule_array = explode(':', $rule, 2);
		if (count($rule_array) == 2) {
			$days = $rule_array[0];
			$dates = $rule_array[1];
		} else {
			$days = '';
			$dates = $rule_array[0];
		}
		
		// Если $days пусто, то любой день недели
		if (empty($days) || $days == '0') $weekdays = [1,2,3,4,5,6,7];
		// Формируем массив разрешенных дней недели
		else $weekdays = explode(',', $days);
		
		// Если $dates пусто, то любой день
		if (empty($dates)) $dates = '01-01,12-31';

		// Формируем массив разрешенных дат
		$intervals = explode (';', $dates);

		foreach ($intervals as $interval) {
			if (empty($interval)) continue;

			$range = explode (',', $interval);

			if (count($range) == 1) $range[] = $interval;
			$option  = explode ('|', $range[0]);
			if (count($option) == 1) $begin = bg_get_new_date ($option[0], $year);
			else $begin = min (bg_get_new_date ($option[0], $year), bg_get_new_date ($option[1], $year));	// Выбираем более раннюю дату

			$option  = explode ('|', $range[1]);
			if (count($option) == 1) $end = bg_get_new_date ($option[0], $year);
			else {									// Выбираем более раннюю дату
				$end = min (bg_get_new_date ($option[0], $year), bg_get_new_date ($option[1], $year));
				
				// Если при этом последняя дата становится раньше начальной, то интервал не соответствует
				if ($end < $begin) break;	
			}
			// Интервал начинается в одном году, а заканчивается в другом
			if ($begin > $end) {
				$date = $begin;
				while ($date <= $year.'-12-31') {		// проверяем интервал в конце года
					$wd = date("N", strtotime($date));
					if (in_array($wd, $weekdays)) $dates_array[] = $date;
					$date = date("Y-m-d", strtotime($date.'+ 1 days'));
				}
				$date = ($year+1).'-01-01';
				while ($date <= $end) {					// проверяем интервал в начале следующего года
					$wd = date("N", strtotime($date));
					if (in_array($wd, $weekdays)) $dates_array[] = $date;
					$date = date("Y-m-d", strtotime($date.'+ 1 days'));
				}
			// Интервал в текущем году	
			} else {					
				$date = $begin;
				while ($date <= $end) {					// проверяем этот интервал
					$wd = date("N", strtotime($date));
					if (in_array($wd, $weekdays)) $dates_array[] = $date;
					$date = date("Y-m-d", strtotime($date.'+ 1 days'));
				}
			}
		}
	}

	return $dates_array;
}	

/*******************************************************************************
	Функция вычисляет дату по новому стилю в текущем году
		$old  - дата по старому стилю (в формате m-d)
		$year - год (в формате YYYY)
	Возвращает:
		дата по новому стилю
*******************************************************************************/
function bg_get_new_date ($old, $year) {
	$old_arr = explode ('-', $old, 2);
	$m = (int) ($old_arr[0]??0);
	$d = (int) ($old_arr[1]??0);
	if ($year%4 != 0 && $m == 2 && $d == 29) $d = 28;
//	if (!$d) return '';
	
	if ($m == 0) {	// Переходящий праздник
		 $date = bg_get_easter($year, $d);
	} else {
		$dd = intval(($m < 3)?bg_ddif($year-1):bg_ddif($year));
		$date = date( 'Y-m-d', mktime ( 0, 0, 0, $m, $d+$dd, $year ) );
//		if ($date > $year.'-12-31') {
//			$dd = ($m < 3)?bg_ddif($year-2):bg_ddif($year-1);
//			$date = date( 'Y-m-d', mktime ( 0, 0, 0, $m, $d+$dd, $year-1 ) );
//		}
	}
	return $date;
}

/*******************************************************************************
	Функция вычисляет дату по новому стилю в текущем году
		$date  - дата по новому стилю (в формате Y-m-d)
	Возвращает:
		дата по старому стилю
*******************************************************************************/
function bg_get_old_date ($date) {
	list($y, $m, $d) = explode ('-', $date);
	$y = (int) $y;
	$m = (int) $m;
	$d = (int) $d;
	
	$dd = intval(($m < 3)?bg_ddif($y-1):bg_ddif($y));
	$old = date( 'Y-m-d', mktime ( 0, 0, 0, $m, $d-$dd, $y ) );

	return $old;
}

/*******************************************************************************
	Функция определяет день Пасхи или переходящий праздник в указанном году
	Параметры:
		$year - год в формате Y
		$shift - смещение даты относительно Пасхи (переходящий праздник)
				по умолчанию $shift=0 - день Пасхи
	Возвращает:
		Дату Пасхи или переходящий праздник в формате Y-m-d	
*******************************************************************************/
function bg_get_easter($year, $shift=0) {
	$year = (int) $year;
	$a=((19*($year%19)+15)%30);
	$b=((2*($year%4)+4*($year%7)+6*$a+6)%7);
	if ($a+$b>9) {
		$day=$a+$b-9;
		$month=4;
	} else {
		$day=22+$a+$b;
		$month=3;
	}
	$dd = bg_ddif($year);
	
	return date( 'Y-m-d', mktime ( 0, 0, 0, $month, $day+$dd+intval($shift), intval($year) ) );
}

/*******************************************************************************
	Функция возвращает количество дней между датами по новому и старому стилю
	Параметры:
		$year - год в формате Y
	Возвращает:
		Количество дней между датами по новому и старому стилю	
*******************************************************************************/  
function bg_ddif($year) {
	return ($year-$year%100)/100 - ($year-$year%400)/400 - 2;
}

/*******************************************************************************
	Функция возвращает количество дней между Пасхой и указанной датой по новому стилю
	Параметры:
		$date - дата в формате Y-m-d
		$year - год для расчета Пасхи  
	Возвращает:
		Количество дней между Пасхой и указанной датой	
*******************************************************************************/  
function bg_date_easter_dif($date, $year='') {
	if (empty($year)) $year = explode('-', bg_get_old_date ($date))[0];
	$interval = date_diff(date_create(bg_get_easter($year)), date_create($date));
	return (int)$interval->format('%R%a');
}

/*******************************************************************************
	Функция возвращает количество дней между двумя датами по новому стилю
	Параметры:
		$date1, $date2 - дата в формате Y-m-d

	Возвращает:
		Количество дней между $date1, $date2
*******************************************************************************/  
function bg_date_diff ($date1, $date2) {
	$interval = date_diff(date_create($date1), date_create($date2));
	return (int)$interval->format('%R%a');
}

/*******************************************************************************
	Функция возвращает глас по Октоиху для указанной даты
	Параметры:
		$date - дата в формате Y-m-d
	Возвращает:
		Глас по Октоиху	
*******************************************************************************/  
function bg_getTone($date) {
	list($year, $m, $d) = explode ('-', $date);
	$num = bg_date_easter_dif($date, (int)$year);
	if ($num < 0) {									// Если дата раньше Пасхи этого года,
		$num = bg_date_easter_dif($date, $year-1);	// то отсчитываем от предыдущей Пасхи
	}
	if ($num < 7) $tone = [1,2,3,4,5,6,8][$num];
	else $tone = floor(($num-7)/7)%8+1;
	return $tone;
}

// Функция возвращает диапазон дат попразднства Сретения
function afterfeastCandlemas ($year, $d = '') {
	
	$date = bg_get_new_date ('02-02', $year);
	$dd = -bg_date_easter_dif($date, $year);
	
	$afterfeast = [
		'65' => '02-03,02-08',
		'64' => '02-03,02-07',
		'63' => '02-03,02-06',
		'62' => '02-03,02-05',
		'61' => '02-03,02-04',
		'60' => '02-03,02-04;02-06,02-07',
		'59' => '02-03;02-05,02-06',
		'58' => '02-04,02-05',
		'57' => '02-03,02-04',
		'56' => '02-03,02-04',
		'55' => '02-03',
		'54' => '02-04',
		'53' => '02-03',
		'52' => '02-04',
		'51' => '02-03'
	];
	
	if ($dd > 64) $dd = 65;
	
	$rule = '';
	if ($dd > 50) {
		if ($d) {
			$intervals = explode (';', $afterfeast[$dd]);
			foreach ($intervals as $interval) {
				$rangs = explode (',', $interval);
				if (count($rangs) == 1) $rangs[1] = $rangs[0]; 
				if ($d >= $rangs[0] && $d <= $rangs[1]) {
					$rule = $d;
					break;
				}
			}
		} else $rule = $afterfeast[$dd];
	}
	return $rule;
}

// Функция возвращает дату отдания Сретения
function feastendCandlemas ($year) {
	
	$date = bg_get_new_date ('02-02', $year);
	$dd = -bg_date_easter_dif($date, $year);
	$feastend = [
		'64' => '02-08',
		'63' => '02-07',
		'62' => '02-06',
		'61' => '02-05',
		'60' => '02-08',
		'59' => '02-07',
		'58' => '02-06',
		'57' => '02-05',
		'56' => '02-06',
		'55' => '02-05',
		'54' => '02-06',
		'53' => '02-05',
		'52' => '02-05',
		'51' => '02-04',
		'50' => '02-03',
		'49' => '02-03'
	];
	
	if ($dd > 64) return '02-09';
	elseif ($dd < 49) return '';
	else return $feastend[$dd];
}
<?php
/*
	Недели, седмицы, пост
*/

/*******************************************************************************

	Функция определяет название Седмицы (Недели) по годичному кругу богослужений

*******************************************************************************/  
function bg_sedmica ($date) {
	
	list($y, $m, $d) = explode('-', $date);
	$wd = date("N",strtotime($date));
	
	// Кол-во дней до(-) / после(+) Пасхи
	$cd = bg_date_easter_dif($date, (int)$y);
	
	if ($cd < -70) {				// До Недели о мытаре и фарисее идут седмицы по Пятидесятнице прошлого года
		$cd = bg_date_easter_dif($date, $y-1);
		$week_number = (int)($cd/7) - 7;
		if ($wd == 7) return sprintf("Неделя %d-я по Пятидесятнице", $week_number);
		else return sprintf("Седмица %d-я по Пятидесятнице", $week_number+1);
	}
	elseif ($cd == -70) return "Неделя о мытаре и фарисее";	// Седмицы подготовительные
	elseif ($cd < -63) return "Седмица о мытаре и фарисее";	
	elseif ($cd == -63) return "Неделя о блудном сыне";	
	elseif ($cd < -56) return "Седмица о блудном сыне";	
	elseif ($cd == -56) return "Неделя мясопустная, о Страшнем суде";	
	elseif ($cd < -49) return "Сырная седмица (масленица)";						
	elseif ($cd == -49) return "Неделя сыропустная. Воспоминание Адамова изгнания. Прощеное воскресенье";						
	elseif ($cd < -13) {										// Седмицы Великого поста
		$week_number = (int)($cd/7) + 7;
		if ($cd == -42) return "Неделя 1-я Великого поста. Торжество Православия";
		if ($cd == -35) return "Неделя 2-я Великого поста. Свт. Григория Паламы, архиепископа Фессалонитского";
		if ($cd == -28) return "Неделя 3-я Великого поста, Крестопоклонная";
		if ($cd == -21) return "Неделя 4-я Великого поста. Прп. Иоанна Лествичника, игумена Синайского";
		if ($cd == -14) return "Неделя 5-я Великого поста. Прп. Марии Египетской";
		else return sprintf("Седмица %d-я по Великого поста", $week_number);
	}
	elseif ($cd < -7) return "Седмица 6-я Великого поста (седмица ваий)";
	elseif ($cd == -7) return "Неделя 6-я Великого поста ваий (цветоносная, Вербное воскресенье)";
	elseif ($cd < 0) return "Страстная седмица";
	elseif ($cd == 0) return "Светлое Христово Воскресение. Пасха";
	elseif ($cd < 7) return "Пасхальная (Светлая) седмица";
	elseif ($cd < 50) {									// Седмицы по Пасхе
		$week_number = (int)($cd/7);
		if ($cd==7) return "Неделя 2-я по Пасхе (Антипасха)";
		elseif ($cd==14) return "Неделя 3-я по Пасхе, жен мироносец";
		elseif ($cd==21) return "Неделя 4-я по Пасхе, о расслабленом";
		elseif ($cd==28) return "Неделя 5-я по Пасхе, о самаряныне";
		elseif ($cd==35) return "Неделя 6-я по Пасхе, о слепом";
		elseif ($cd==42) return "Неделя 7-я по Пасхе, святых отцев I Вселенского Собора";
		elseif ($cd==49) return "Неделя 8-я по Пасхе";
		else return sprintf("Седмица %d-я по Пасхе", $week_number+1);
	}
	else  {														// Седмицы по Пятидесятнице
		$week_number = (int)($cd/7) - 7;
		if ($wd == 7) return sprintf("Неделя %d-я по Пятидесятнице", $week_number);
		else {
			if ($week_number==0) return "Седмица 1-я по Пятидесятнице (Троицкая)";
			else return sprintf("Седмица %d-я по Пятидесятнице", $week_number+1);
		}
	}

	return "";
}

/*******************************************************************************

	Функция возвращает тип пищи на заданную дату согласно иерусалимскому уставу

*******************************************************************************/  
function bg_getFood ($date) {
	
	$foodKind = [
		"Поста нет",								// 0
		"Из трапезы исключается мясо",				// 1
		"Пост. Разрешается рыба",					// 2
		"Пост. Разрешается рыбная икра",			// 3
		"Пост. Пища с растительным маслом",			// 4
		"Пост. Горячая пища без масла",				// 5
		"Пост. Сухоядение",							// 6
		"Пост. Воздержание от пищи"					// 7
	];
	list($y, $m, $d) = explode('-', $date);
	$wd = date("N",strtotime($date));
	$y = (int) $y;
	
	if ($date < bg_get_new_date ('01-05', $y)) {								// Продолжение Святок
		return $foodKind[0];

	} elseif ($date == bg_get_new_date ('01-05', $y)) {							// Навечерие Богоявления
		return $foodKind[6];

	} elseif ($date == bg_get_new_date ('01-06', $y)) {							// Богоявление
		return $foodKind[0];

	} elseif ($date < bg_get_new_date ('0--69', $y)) {							// Зимний мясоед
		if ($wd == 3 || $wd == 5) return $foodKind[2];
		else return $foodKind[0];

	} elseif ($date < bg_get_new_date ('0--62', $y)) {							// Мытаря и фарисея
		return $foodKind[0];

	} elseif ($date < bg_get_new_date ('0--55', $y)) {							// Зимний мясоед (Блудного сына)
		if ($wd == 3 || $wd == 5) return $foodKind[2];
		else return $foodKind[0];

	} elseif ($date < bg_get_new_date ('0--48', $y)) {							// Сырная седмицы
		return $foodKind[1];

	} elseif ($date == bg_get_new_date ('0--48', $y)) {							// Первый день Великого поста
		return $foodKind[7];

	} elseif ($date < bg_get_new_date ('0--8', $y)) {							// Великий пост
		if ($date == bg_get_new_date ('03-25', $y)) return $foodKind[2];		// Благовещение
		if ($wd == 1 || $wd == 3 || $wd == 5) return $foodKind[6];
		elseif ($wd == 2 || $wd == 4) return $foodKind[5];
		else return $foodKind[4];

	} elseif ($date == bg_get_new_date ('0--8', $y)) {							// Лазарева Суббота
		if ($date == bg_get_new_date ('03-25', $y)) return $foodKind[2];
		return $foodKind[3];

	} elseif ($date == bg_get_new_date ('0--7', $y)) {							// Вход Господень в Иерусалим
		return $foodKind[2];

	} elseif ($date < bg_get_new_date ('0-0', $y)) {							// Страстная седмица
		if ($wd == 5) return $foodKind[7];
		elseif ($wd == 6) return $foodKind[5];
		else return $foodKind[6];

	} elseif ($date < bg_get_new_date ('0-6', $y)) {							// Светлая седмицы
		return $foodKind[0];

	} elseif ($date <= bg_get_new_date ('0-49', $y)) {							// Весенний мясоед
		if ($wd == 3 || $wd == 5) return $foodKind[2];
		else return $foodKind[0];

	} elseif ($date <= bg_get_new_date ('0-56', $y)) {							// Троицкая седмицы
		return $foodKind[0];

	} elseif ($date < bg_get_new_date ('06-29', $y)) {							// Апостольский пост
		if ($date == bg_get_new_date ('06-24', $y)) return $foodKind[2];		// Рождество Иоанна Предтечи
		if ($wd == 1) return $foodKind[5];
		elseif ($wd == 3 || $wd == 5) return $foodKind[6];
		else return $foodKind[2];

	} elseif ($date == bg_get_new_date ('06-29', $y)) {							// Петра и Павла
		if ($wd == 3 || $wd == 5) return $foodKind[2];
		else return $foodKind[0];

	} elseif ($date <  bg_get_new_date ('08-01', $y)) {							// Летний мясоед
		if ($wd == 3 || $wd == 5) return $foodKind[4];
		else return $foodKind[0];

	} elseif ($date < bg_get_new_date ('08-15', $y)) {							// Успенский пост
		if ($date == bg_get_new_date ('08-06', $y)) return $foodKind[2];		// Преображение
		if ($wd == 1 || $wd == 3 || $wd == 5) return $foodKind[6];
		elseif ($wd == 2 || $wd == 4) return $foodKind[5];
		else return $foodKind[4];

	} elseif ($date == bg_get_new_date ('08-15', $y)) {							// Успение
		if ($wd == 3 || $wd == 5) return $foodKind[2];
		else return $foodKind[0];

	} elseif ($date <  bg_get_new_date ('11-15', $y)) {							// Осенний мясоед
		if ($date == bg_get_new_date ('08-29', $y)) return $foodKind[4];		// Усекновение головы Иоанна Предтечи
		if ($date == bg_get_new_date ('09-14', $y)) return $foodKind[4];		// Воздвижение
		if ($wd == 3 || $wd == 5){
			if ($date == bg_get_new_date ('09-08', $y)) return $foodKind[2];	// Рождество Богородицы
			elseif ($date == bg_get_new_date ('10-01', $y)) return $foodKind[2];// Покров
			else return $foodKind[4];
		} 
		else return $foodKind[0];

	} elseif ($date < bg_get_new_date ('12-06', $y)) {							// Рождественский пост
		if ($date == bg_get_new_date ('11-21', $y)) return $foodKind[2];		// Введение
		if ($wd == 1) return $foodKind[5];
		elseif ($wd == 3 || $wd == 5) return $foodKind[6];
		else return $foodKind[2];

	} elseif ($date == bg_get_new_date ('12-06', $y)) {							// Св. Николая 
		return $foodKind[2];

	} elseif ($date < bg_get_new_date ('12-20', $y)) {							// Рождественский пост (продолжение)
		if ($wd == 1) return $foodKind[5];
		elseif ($wd == 3 || $wd == 5) return $foodKind[6];
		elseif ($wd == 2 || $wd == 4) return $foodKind[4];
		else return $foodKind[2];

	} elseif ($date < bg_get_new_date ('12-24', $y)) {							// Рождественский пост (окончание)
		if ($wd == 1 || $wd == 3 || $wd == 5) return $foodKind[6];
		elseif ($wd == 2 || $wd == 4) return $foodKind[5];
		else return $foodKind[4];

	} elseif ($date == bg_get_new_date ('12-24', $y)) {							// Рождественский сочельник
		return $foodKind[6];

	} else return $foodKind[0];													// Святки
}

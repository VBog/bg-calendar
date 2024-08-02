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
		if ($wd == 7) return sprintf(_("Неделя %d-я по Пятидесятнице"), $week_number);
		else return sprintf(_("Седмица %d-я по Пятидесятнице"), $week_number+1);
	}
	elseif ($cd == -70) return _("Неделя о мытаре и фарисее");	// Седмицы подготовительные
	elseif ($cd < -63) return _("Седмица о мытаре и фарисее");	
	elseif ($cd == -63) return _("Неделя о блудном сыне");	
	elseif ($cd < -56) return _("Седмица о блудном сыне");	
	elseif ($cd == -56) return _("Неделя мясопустная, о Страшнем суде");	
	elseif ($cd < -49) return _("Сырная седмица (масленица)");						
	elseif ($cd == -49) return _("Неделя сыропустная. Воспоминание Адамова изгнания. Прощеное воскресенье");						
	elseif ($cd < -13) {										// Седмицы Великого поста
		$week_number = (int)($cd/7) + 7;
		if ($cd == -42) return _("Неделя 1-я Великого поста. Торжество Православия");
		if ($cd == -35) return _("Неделя 2-я Великого поста.");
		if ($cd == -28) return _("Неделя 3-я Великого поста, Крестопоклонная");
		if ($cd == -21) return _("Неделя 4-я Великого поста.");
		if ($cd == -14) return _("Неделя 5-я Великого поста.");
		else return sprintf(_("Седмица %d-я Великого поста"), $week_number);
	}
	elseif ($cd < -7) return _("Седмица 6-я Великого поста (седмица ваий)");
	elseif ($cd == -7) return _("Неделя 6-я Великого поста ваий (цветоносная, Вербное воскресенье)");
	elseif ($cd < 0) return _("Страстная седмица");
	elseif ($cd == 0) return _("Светлое Христово Воскресение. Пасха");
	elseif ($cd < 7) return _("Пасхальная (Светлая) седмица");
	elseif ($cd < 50) {									// Седмицы по Пасхе
		$week_number = (int)($cd/7);
		if ($cd==7) return _("Неделя 2-я по Пасхе (Антипасха)");
		elseif ($cd==14) return _("Неделя 3-я по Пасхе, жен мироносец");
		elseif ($cd==21) return _("Неделя 4-я по Пасхе, о расслабленом");
		elseif ($cd==28) return _("Неделя 5-я по Пасхе, о самаряныне");
		elseif ($cd==35) return _("Неделя 6-я по Пасхе, о слепом");
		elseif ($cd==42) return _("Неделя 7-я по Пасхе, святых отцев I Вселенского Собора");
		elseif ($cd==49) return _("Неделя 8-я по Пасхе");
		else return sprintf(_("Седмица %d-я по Пасхе"), $week_number+1);
	}
	else  {														// Седмицы по Пятидесятнице
		$week_number = (int)($cd/7) - 7;
		if ($wd == 7) {
			if ($week_number == 1) return _("Неделя 1-я по Пятидесятнице. Всех Святых");
			else return sprintf(_("Неделя %d-я по Пятидесятнице"), $week_number);
			
		} else {
			if ($week_number==0) return _("Седмица 1-я по Пятидесятнице (Троицкая)");
			else return sprintf(_("Седмица %d-я по Пятидесятнице"), $week_number+1);
		}
	}

	return "";
}

/*******************************************************************************

	Функция возвращает тип пищи на заданную дату согласно иерусалимскому уставу

*******************************************************************************/  
function bg_getFood ($date) {
	
	$fastType = [
		_("Пост"),							// 0
		_("Великий пост"),					// 1
		_("Апостольский пост"),				// 2
		_("Успенский пост"),				// 3
		_("Рождественский пост"),			// 4
		
	];

	$foodKind = [
		_("Поста нет"),						// 0
		_("Из трапезы исключается мясо"),	// 1
		_("Разрешается рыба"),				// 2
		_("Разрешается рыбная икра"),		// 3
		_("Пища с растительным маслом"),	// 4
		_("Горячая пища без масла"),		// 5
		_("Сухоядение"),					// 6
		_("Воздержание от пищи")			// 7
	];
	list($y, $m, $d) = explode('-', $date);
	$wd = date("N",strtotime($date));
	$y = (int) $y;
	
	// Святки
	if ($date < bg_get_new_date ('01-05', $y)) {								// Продолжение Святок
		return $foodKind[0];

	// Навечерие Богоявления
	} elseif ($date == bg_get_new_date ('01-05', $y)) {							// Навечерие Богоявления
		return $fastType[0].'. '.$foodKind[6];

	// Богоявление
	} elseif ($date == bg_get_new_date ('01-06', $y)) {
		return $foodKind[0];

	// Зимний мясоед
	} elseif ($date < bg_get_new_date ('0--69', $y)) {
		if ($wd == 3 || $wd == 5) return $fastType[0].'. '.$foodKind[2];
		else return $foodKind[0];

	// Седмица о мытаре и фарисее
	} elseif ($date < bg_get_new_date ('0--62', $y)) {
		return $foodKind[0];

	// Седмица о блудном сыне. Зимний мясоед
	} elseif ($date < bg_get_new_date ('0--55', $y)) {
		if ($wd == 3 || $wd == 5) return $fastType[0].'. '.$foodKind[2];
		else return $foodKind[0];

	// Сырная седмица
	} elseif ($date < bg_get_new_date ('0--48', $y)) {
		return $foodKind[1];

	// Первый день Великого поста
	} elseif ($date == bg_get_new_date ('0--48', $y)) {
		return $fastType[1].'. '.$foodKind[7];

	// Великий пост
	} elseif ($date < bg_get_new_date ('0--8', $y)) {
		// Благовещение
		if ($date == bg_get_new_date ('03-25', $y)) return $fastType[1].'. '.$foodKind[2];
		if ($wd == 1 || $wd == 3 || $wd == 5) return $fastType[1].'. '.$foodKind[6];
		elseif ($wd == 2 || $wd == 4) return $fastType[1].'. '.$foodKind[5];
		else return $fastType[1].'. '.$foodKind[4];

	// Лазарева Суббота
	} elseif ($date == bg_get_new_date ('0--8', $y)) {
		if ($date == bg_get_new_date ('03-25', $y)) return $fastType[1].'. '.$foodKind[2];
		return $fastType[1].'. '.$foodKind[3];

	// Вход Господень в Иерусалим
	} elseif ($date == bg_get_new_date ('0--7', $y)) {
		return $fastType[1].'. '.$foodKind[2];

	// Страстная седмица
	} elseif ($date < bg_get_new_date ('0-0', $y)) {
		if ($wd == 5) return $fastType[1].'. '.$foodKind[7];
		elseif ($wd == 6) return $fastType[1].'. '.$foodKind[5];
		else return $fastType[1].'. '.$foodKind[6];

	// Светлая седмицы
	} elseif ($date < bg_get_new_date ('0-6', $y)) {
		return $foodKind[0];

	// Весенний мясоед
	} elseif ($date <= bg_get_new_date ('0-49', $y)) {
		if ($wd == 3 || $wd == 5) return $fastType[0].'. '.$foodKind[2];
		else return $foodKind[0];

	// Троицкая седмицы
	} elseif ($date <= bg_get_new_date ('0-56', $y)) {
		return $foodKind[0];

	// Апостольский пост
	} elseif ($date < bg_get_new_date ('06-29', $y)) {
		// Рождество Иоанна Предтечи
		if ($date == bg_get_new_date ('06-24', $y)) return $fastType[2].'. '.$foodKind[2];
		if ($wd == 1) return $fastType[2].'. '.$foodKind[5];
		elseif ($wd == 3 || $wd == 5) return $fastType[2].'. '.$foodKind[6];
		else return $fastType[2].'. '.$foodKind[2];
	
	// Петра и Павла
	} elseif ($date == bg_get_new_date ('06-29', $y)) {
		if ($wd == 3 || $wd == 5) return $fastType[0].'. '.$foodKind[2];
		else return $foodKind[0];
	
	// Летний мясоед
	} elseif ($date <  bg_get_new_date ('08-01', $y)) {
		if ($wd == 3 || $wd == 5) return $fastType[0].'. '.$foodKind[4];
		else return $foodKind[0];

	// Успенский пост
	} elseif ($date < bg_get_new_date ('08-15', $y)) {
		// Преображение
		if ($date == bg_get_new_date ('08-06', $y)) return $fastType[3].'. '.$foodKind[2];		
		if ($wd == 1 || $wd == 3 || $wd == 5) return $fastType[3].'. '.$foodKind[6];
		elseif ($wd == 2 || $wd == 4) return $fastType[3].'. '.$foodKind[5];
		else return $fastType[3].'. '.$foodKind[4];

	// Успение
	} elseif ($date == bg_get_new_date ('08-15', $y)) {
		if ($wd == 3 || $wd == 5) return $fastType[0].'. '.$foodKind[2];
		else return $foodKind[0];

	// Осенний мясоед
	} elseif ($date <  bg_get_new_date ('11-15', $y)) {
		// Усекновение головы Иоанна Предтечи
		if ($date == bg_get_new_date ('08-29', $y)) return $fastType[0].'. '.$foodKind[4];
		// Воздвижение
		if ($date == bg_get_new_date ('09-14', $y)) return $fastType[0].'. '.$foodKind[4];
		if ($wd == 3 || $wd == 5){
			// Рождество Богородицы
			if ($date == bg_get_new_date ('09-08', $y)) return $fastType[0].'. '.$foodKind[2];
			// Покров
			elseif ($date == bg_get_new_date ('10-01', $y)) return $foodKind[2];
			else return $fastType[0].'. '.$foodKind[4];
		} 
		else return $foodKind[0];

	// Рождественский пост
	} elseif ($date < bg_get_new_date ('12-06', $y)) {
		// Введение
		if ($date == bg_get_new_date ('11-21', $y)) return $fastType[4].'. '.$foodKind[2];
		if ($wd == 1) return $fastType[4].'. '.$foodKind[5];
		elseif ($wd == 3 || $wd == 5) return $fastType[4].'. '.$foodKind[6];
		else return $fastType[4].'. '.$foodKind[2];

	// Св. Николая 
	} elseif ($date == bg_get_new_date ('12-06', $y)) {
		return $fastType[4].'. '.$foodKind[2];

	// Рождественский пост (продолжение)
	} elseif ($date < bg_get_new_date ('12-20', $y)) {
		if ($wd == 1) return $foodKind[5];
		elseif ($wd == 3 || $wd == 5) return $fastType[4].'. '.$foodKind[6];
		elseif ($wd == 2 || $wd == 4) return $fastType[4].'. '.$foodKind[4];
		else return $foodKind[2];

	// Рождественский пост (окончание)
	} elseif ($date < bg_get_new_date ('12-24', $y)) {
		if ($wd == 1 || $wd == 3 || $wd == 5) return $fastType[4].'. '.$foodKind[6];
		elseif ($wd == 2 || $wd == 4) return $fastType[4].'. '.$foodKind[5];
		else return $fastType[4].'. '.$foodKind[4];

	// Рождественский сочельник
	} elseif ($date == bg_get_new_date ('12-24', $y)) {
		return $fastType[4].'. '.$foodKind[6];

	// Святки
	} else return $foodKind[0];
}

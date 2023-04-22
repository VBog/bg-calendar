// Установить параметр date в адресную строку
function setParam (param=true) {
	var href = location.href;
	const url = new URL(href);
	href = href.replace (url.search, '');
	href = href.replace (url.hash, '');
	var file = url.search;
	file = file.split('&')[0];
	href = href+file;
	
	if (param) {
		var d = document.getElementById("bg_setDay");
		href = href+'&date='+d.value;
	}
	location.href=href;
}

// Перетаскивание элементов событий
function loadDrag_n_Drop() {
	const tasksListElement = document.querySelector('.tasks__list');
	const taskElements = tasksListElement.querySelectorAll('.tasks__item');

	for (const task of taskElements) {
		task.draggable = true;
	}

	tasksListElement.addEventListener('dragstart', (evt) => {
		evt.target.classList.add('selected');
	});

	tasksListElement.addEventListener('dragend', (evt) => {
		evt.target.classList.remove('selected');
	});

	const getNextElement = (cursorPosition, currentElement) => {
		const currentElementCoord = currentElement.getBoundingClientRect();
		const currentElementCenter = currentElementCoord.y + currentElementCoord.height / 2;
	  
		const nextElement = (cursorPosition < currentElementCenter) ?
			currentElement :
			currentElement.nextElementSibling;
	  
	  return nextElement;
	};

	tasksListElement.addEventListener('dragover', (evt) => {
		evt.preventDefault();
	  
		const activeElement = tasksListElement.querySelector('.selected');
		const currentElement = evt.target;
		const isMoveable = activeElement !== currentElement &&
			currentElement.classList.contains('tasks__item');
		
		if (!isMoveable) return;
	  
		const nextElement = getNextElement(evt.clientY, currentElement);
	  
		if (nextElement && 
			activeElement === nextElement.previousElementSibling ||
			activeElement === nextElement) return;

		tasksListElement.insertBefore(activeElement, nextElement);
	});
}

// Удалить элемент события
function remove_event (e) {
	var el = e.parentNode.parentNode;
	var title = e.parentNode.querySelector('summary').innerText;
	result = confirm(title+"\r\nДействительно удалить это событие?");
	if (result) el.remove();				
}

// Добавить элемент события
function add_event (e) {
	var el = e.parentNode.parentNode;
	e.parentNode.removeAttribute("open");
	var li = document.createElement("li");
	li.className = 'tasks__item';
	li.innerHTML = '<details open><summary><span class="red">Новый элемент</span></summary><textarea class="editor" name="events[]">{\r\n\t"title": "",\r\n\t"translation": "",\r\n\t"level": 0,\r\n\t"priority": 0,\r\n\t"type": "",\r\n\t"subtype": "",\r\n\t"feast_type": "",\r\n\t"sanctity": "",\r\n\t"gender": "",\r\n\t"dual_worship": 0,\r\n\t"common_tropar": 0,\r\n\t"blessed": false,\r\n\t"minea_id": "",\r\n\t"taks": [],\r\n\t"rule": "",\r\n\t"id_list": "",\r\n\t"imgs": [],\r\n\t"readings": {},\r\n\t"id": 0\r\n}</textarea><input type="button" class="add" value="Добавить" onclick="add_event (this);"> <input type="button" class="remove" value="Удалить" onclick="remove_event (this);"> <input type="button" class="validate" value="Проверить" onclick="json_validate (this);"></details>';
	el.parentNode.insertBefore(li, el.nextSibling);
	var details = li.querySelector('details');
	highlight_json (details);
}

// Проверка json на валидность
function json_validate(e) {
	var el = e.parentNode.parentNode;
	var str = e.parentNode.querySelector('textarea').value;
	var summary = e.parentNode.querySelector('summary');
	try {
		var event = JSON.parse(str);
	} catch (e) {
		summary.innerHTML = '<span class="red">'+e+'</span>';
		alert (e);
		return false;
	}
	if (!event.title.trim()) {
		summary.innerHTML = '<span class="red">Ошибка: Нет названия события</span>';
		alert ('Ошибка: Нет названия события');
		return false;
	}
	if (!event.id) {
		summary.innerHTML = '<span class="red">Ошибка: Не указан ID</span>';
		alert ('Ошибка: Не указан ID');
		return false;
	}
	if (!event.rule) {
		summary.innerHTML = '<span class="red">Ошибка: Не указано правило события</span>';
		alert ('Ошибка: Не указано правило события');
		return false;
	}
	summary.innerHTML = (event.id+'').padStart(5, "0")+' '
						+event.title.substr(0, 47)+(event.title.substr(47)?'...':'')
						+' <b>['+(Array.isArray(event.rule)?event.rule.join(']['):event.rule)+']</b>'; 
		alert ('Ok!');
	return true;
}

// Подсветка текста json в textarea
function highlight_json (e) {
	if (!e.getAttribute('data-highlight')) {
		var editor = CodeMirror.fromTextArea(e.children[1], {
			matchBrackets: true,
			autoCloseBrackets: true,
			mode: "application/ld+json",
			lineWrapping: true
		});
		e.setAttribute('data-highlight', true);
	}
}

/*******************************************************
	Запуск скрипта
********************************************************/
window.onload = function() {
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

	// Перетаскивание
	loadDrag_n_Drop();
	
	// Кнопка Удалить
	var anchors = document.getElementsByClassName('remove');
	for(var i = 0; i < anchors.length; i++) {
		var anchor = anchors[i];
		anchor.onclick = function() {
			remove_event (this);
		}
	}

	// Кнопка Добавить
	var anchors = document.getElementsByClassName('add');
	for(var i = 0; i < anchors.length; i++) {
		var anchor = anchors[i];
		anchor.onclick = function() {
			add_event (this);
		}
	}

	// Кнопка Проверить
	var anchors = document.getElementsByClassName('validate');
	for(var i = 0; i < anchors.length; i++) {
		var anchor = anchors[i];
		anchor.onclick = function() {
			json_validate (this);
		}
	}
	
	// Переход по внутренней ссылке на элемент события с открытием details
	var anchors = document.getElementsByClassName('the_event');
	for(var i = 0; i < anchors.length; i++) {
		var anchor = anchors[i];
		anchor.onclick = function() {
			var link = this.getAttribute('href').substring(1);
			var details = document.getElementById(link);
			details.setAttribute('open', 'open');
			highlight_json (details);
		}
	}

	// Подсветка синтексиса json в textarea
	var anchors = document.getElementsByClassName('editor');
	for(var i = 0; i < anchors.length; i++) {
		var anchor = anchors[i];
		anchor.parentNode.onclick = function() {
			highlight_json (this);
		}
	}
}

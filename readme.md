### Bg Orthodox Calendar  ###

Contributors: VBog

Tags: православие, календарь, святые, праздники, чтения, тропари, кондаки, молитвы

License: GPLv2

License URI: http://www.gnu.org/licenses/gpl-2.0.html

Version: 3.3

Библиотека функций для расчета событий православного календаря (РПЦ)


## Description ##

Полноценный православный календарь, оформленный в минималистической манере. 

*Отличительные особенности:*

1. Написан на голом PHP+JS без применения каких-либо движков. Это позволяет внедрить его на любой сайт простым копированием файлов.
2. Вся информация на одной странице. Нет внешних ссылок.
3. Алгоритм в строгом соответствии с Типиконом. Автоматический перенос служб, точный расчет Евангельских и Апостольских чтений. Не требуется ежегодных правок и копирования данных.
4. Простой и интуитивно понятный алгоритм задания правил и расчета дат календарных событий позволяет легко подключать любую другую информацию, связанную с православным календарем.
5. Тексты чтений из служебных Евангелие, Апостола и Паремий.
6. Почти каждое событие содержит краткие жития святых или описание праздника или иконы.
7. Исправлены десятки ошибок календаря АВ и несколько ошибок календаря Патриархии. А сколько еще предстоит исправить?! Есть собственный редактор календаря.

Используйте в качестве примера страницы календаря файл `index.php`

**Файлы библиотеки**

`functions.php` - содержит основные функции для работы с датами

`readings.php` - основные функции для формирования календаря 

`bg_ordered_readings.php` - библиотека для расчета рядовых чтений

`sedmica.php` - названия седмиц и Недель года, постов и пищи



**ФУНКЦИЯ ДЛЯ РАСЧЕТА СОБЫТИЙ КАЛЕНДАРЯ**

`bg_getData($year)`	- Функция получает базу данных календаря из файла json и возвращает массив событий для каждого календарного дня указанного года
	
*Параметр:*
	
* `$year` - год в формате YYYY по старому стилю
	
**Структура данных события календаря**

	Array
	(
	  [events] => Array
		(
		  [0] => Array
			(
				[title] => *Наименование события*
				[translation] => *Поле для перевода наименования события*
				[level] => *уровень значимости события в соответствии со знаком Типикона*
				[priority] => *приоритет отображения события*
				[type] => *тип события*
				[subtype] => *подтип события*
				[feast_type] => *тип праздника:* our_lord|our_lady|saint
				[name] => *имя святого*
				[sanctity] => *лик святости*
				[gender] => *пол:* "male"|"female"
				[dual_worship] => *служба двум святым:* 0|1|2... *(0 - нет, 1,2... - номер пары в текущий день)*
				[common_tropar] => *есть общий тропарь:* false|true
				[blessed] => *есть блаженны:* false|true
				[minea_id] => *ID данных Минеи/Триоди на сайте azbyka.ru/chaso-slov*
				[taks] => Array
				  (
					[0] => Array
					  (
						[id] => *ID*
						[type] => Тропарь|Кондак|Молитва|Величание
						[text] => *текст* 
						[title] => *название*
						[voice] => *глас:* 1-8
					  )
					... 
				  )

				[rule] => *правило расчета даты*
				[id_list] => *список ссылок на святых/праздники в БД azbyka.ru/calendar*
				[imgs] => Array
				  (
					[0] => *ссылка на изображение иконы https://azbyka.ru/days/storage/images/*
					... 
				  )
				
				[readings] => Array	***Чтения Библии***
				  (
					[title] => *название*
					[morning] => *ссылка на чтения на Утрени*
					[apostle] => *ссылка на чтения Апостола на Литургии*
					[gospel] => *ссылка на чтения Евангелие на Литургии*
				  )

				[id] => *ID*
			)
		...
		)
		
	***Параметры дня***
	[afterfeast] => *Попразднство*
	[day_type] => *Тип дня*
	[day_subtype] => *Подтип дня*
	[main_level] => *Уровень значимости главного события*
	[main_type] => *Тип главного события*
	[main_subtype] => *Подтип главного события*
	[main_feast_type] => *Тип праздника главного события*
	[icon] => *Икона дня (ссылка)*
	[icon_title] => *Название иконы дня*
	[liturgy] => *Тип литургии*
	[sedmica] => *Наименование седмицы/Недели*
	[tone] => *Глас Октоиха*
	[food] => *Разрешенная пища*	
	[ordinary_readings] => Array	***Рядовые чтения***
		(
			[title] => *название*
			[morning] => *ссылка на чтения на Утрени*
			[apostle] => *ссылка на чтения Апостола на Литургии*
			[gospel] => *ссылка на чтения Евангелие на Литургии*
			[hour1] => *ссылка на чтения на 1-м Часе*
			[hour3] => *ссылка на чтения на 3-м Часе*
			[hour6] => *ссылка на чтения на 6-м Часе*
			[hour9] => *ссылка на чтения на 9-м Часе*
			[evening] => *ссылка на чтения на Вечерне*
		)
	)

**ФУНКЦИЯ ДЛЯ РАСЧЕТА ДАТЫ ПО ЗАДАННОМУ ПРАВИЛУ**

`bg_get_date_by_rule ($rules, $year)` - функция вычисляет дату по заданному правилу для текущего года
	
*Параметры:*

* `$year` - год в формате YYYY
	
* `$rules` - правила в формате:
		
`дни_недели:интервал_дат`
		
Если правило одно, оно может быть задано как строка, иначе это массив строк.
		
В качестве правила может быть задано имя встроенной функции (например, "afterfeastCandlemas" или "feastendCandlemas"), 
которой в качестве параметра передается `$year` и которая возвращает интервал в указанном здесь формате.
		
** Формат интервалов дат **

Интервал дат задаётся по Юлианскому календарю в формате `m1-d1,m2-d2`

где `m1 и d1` - месяц и день начала интервала, 

	`m2 и d2` - месяц и день конца интервала.

Если продолжительность интервала 1 день, дату окончания интервала можно опустить: `m-d`
		
Для перходящих дат `m=0`, а `d` - количество дней до(-) или после(+) Пасхи в текущем году.
		
Границы интервала можно задавать в виде опции: `m1-d1,m2-d2|m2a-d2a`, при этом в качестве границы будет выбрана более ранняя дата.
		
Если необходимо задать несколько интервалов, они разделяются между собой точкой с запятой:
		
		`m1-d1,m2-d2;m3-d3,m4-d4;m5-d5,m6-d6`
		
Если интервал не задан (пусто), это означает любую дату.
		
** Дни недели **

`$days` - допустимые дни недели (от 1 до 7). Задаются через запятую. Если не заданы (пусто) - любой день недели.
	
*Возвращает:*

массив доступных дат по Григорианскому календарю в формате `Y-m-d`, если дата не соответствует заданному правилу, то пустой массив.	


**ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ**

`bg_tropary_days ($date)` - функция возвращает тропари и кондаки дня

* `$date` - дата по новому стилю в формате `Y-m-d`
 

`blink ($reference, $customLink)` - функция переводит абревиатуру книг на язык локали и формирует гиперссылки на сайт Библии

*Параметры:*
	
* `$reference` - ссылка на Библию на русском языке
		
* `$customLink` - имя пользовательской функции, формирующей ссылку на сайт Библии пользователя. 

`bg_tropary_days` передает ей параметры:

* `$abbr` - обозначение книги на английском языке (как правило используется для указания книги в гиперссылке)

* `$book` - обозначение книги на языке локали

* `$ch` - номера глав и стихов в восточной нотации

		

**ПОЛЕЗНЫЕ ФУНКЦИИ**

`bg_currentDate($shift=0)` - возвращает текущую дату

* `$shift` - смещение в днях от текущей даты

Если в адресной строке задан параметр `?date=Y-m-d`, то это значение, иначе текущая дата 


`bg_get_new_date ($old, $year)` - вычисляет дату по новому стилю в текущем году

* `$old`  - дата по старому стилю (в формате m-d)

* `$year` - год в формате Y


`bg_get_easter($year, $shift=0)` - определяет день Пасхи или переходящий праздник в указанном году

* `$year` - год в формате Y

* `$shift` - смещение даты относительно Пасхи (переходящий праздник)


`bg_ddif($year)` - возвращает количество дней между датами по новому и старому стилю

* `$year` - год в формате Y


`bg_date_easter_dif($date, $year)` - возвращает количество дней между Пасхой и указанной датой по новому стилю

* `$date` - дата по новому стилю в формате `Y-m-d`

* `$year` - год в формате Y


`bg_date_diff ($date1, $date2)` - возвращает количество дней между двумя датами по новому стилю

* `$date1, $date2` - даты в формате Y-m-d


**ЛОКАЛИЗАЦИЯ**

Для локализации календаря используется стандартный механизм локализации PHP на основе функции `gettext(string $message)`.

Все фразы (на русском языке), размещенные внутри кода обвернуты псевдонимом этой функции `_(string $message)`.

На вашей странице укажите имя локали, путь к таблицам перевода, выберите имя текстового домена, например:

```php
// Устанавливаем английский язык
putenv('LC_ALL=en_US');
setlocale(LC_ALL, 'en_US');

// Указываем путь к таблицам переводов
bindtextdomain("calendar", "./locale");

// Выбираем домен
textdomain("calendar");
```

Теперь поиск переводов будет идти в `./locale/en_US/LC_MESSAGES/`.

Для перевода приложения на ваш язык рекомендуем использовать приложение [PoEdit](https://poedit.net/). 
Скопируйте PHP файлы проекта на локальный компьютер, создайте файл перевода с именем домена: `calendar.po` и обновите его из исходного кода в приложении PoEdit. 
Сделайте перевов фраз на ваш язык. Сохраните результат. Будет автоматически создан файл машинного перевода `calendar.mo`, скопируйте оба файла в соответствующий каталог на сервере.

Наиболее трудоёмкий процесс - это перевод json-файлов. В сети есть большое количество как текстовых редакторов, так и специализированных редакторв json-файлов.
Подлежат переводу только поля с именами `"title":` и `"text":`. Поля с другими именами (например, `"gender":`, `"type":`) перводить **НЕЛЬЗЯ!**
Переведенные файлы `calendar.json`, `descriptions.php` и `tropary.json` разместите по соседству с файлами перевода в папке `./locale/en_US/DATA/`.

**РЕДАКТОР СОБЫТИЙ КАЛЕНДАРЯ**

`edit.php` - файл редактора событий

*Возможности:*

1. Редактирование событий в текстовом редакторе с подсветкой синтаксиса. Строго следите за валидностью кода. Для проверки валидности используйте кнопку "Проверить".
2. Выбор событий по датам. Каждое событие в календаре - ссылка на элемент календаря.
3. Cортировка событий Drag'n'Drop.

После сохранения создается копия предыдущей версии файла с указанием даты и времени её создания в имени файла.

***Вспомогательные файлы***

`js/edit.js` - js-скрипт редактора

`js/codemirror.js` - библиотека подсветки синтаксиса [CodeMirror](https://codemirror.net/)

`js/mode/javascript.js` - синтаксис JavaScript и JSON

`css/edit.css` - настройка стилей редактора 

`css/codemirror.css` - стили подсветки синтексиса

*Вызов редактора:*

`https://site.ru/calendar/edit.php?calendar.json`, где `calendar.json` - файл событий календаря.


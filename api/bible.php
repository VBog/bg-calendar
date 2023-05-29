<?php 
/*******************************************************************************

	Функция возвращает текст служебной Библии в соответствии с запросом 
		$ref - ссылка на отрывок в Библии
	
*******************************************************************************/
function bg_get_bible ($ref) {
	
	$json = file_get_contents( dirname(dirname(__FILE__)).'/liturgical_bible/bible.json' );
	$obj = json_decode ($json, true);
	if (empty($obj)) return '';
	$ref = preg_replace ('/\s+/u', '', $ref);

	$txt = "";
	$content = $obj['content'] ?? '';
	if ($content) {
		foreach (array_column($content, 'ref') as $key => $value) {
			$value = preg_replace ('/[АБ]/u', '', $value);
//			if ($value == $ref) break;
			if (mb_strpos($value, $ref) !== false) break;
			else $key = false;
		}
		if ($key === false) return '';
		$txt = $content[$key]['excerpt'];
		if (function_exists('bg_bibrefs')) $value = bg_bibrefs($value);
		$title = '<p><strong>'.$content[$key]['title'].' '.$content[$key]['desc'].' ('.$value.')</strong></p>';
	}
	if ($txt) $txt = '<div class="bg_content">'.$title.$txt.'</div>';
	return $txt;
}
/*******************************************************************************

	Функция возвращает текст служебного Паримийника в соответствии с запросом 
		$ref - ссылка на отрывок в Библии
	
*******************************************************************************/
function bg_get_paremiaes ($ref) {
	
	$json = file_get_contents( dirname(dirname(__FILE__)).'/liturgical_bible/paremiaes.json' );
	$obj = json_decode ($json, true);
	if (empty($obj)) return '';
	$ref = preg_replace ('/\s+/u', '', $ref);

	$txt = "";
	$content = $obj['content'] ?? '';
	if ($content) {
		foreach (array_column($content, 'ref') as $key => $value) {
//			if ($value == $ref) break;
			if (mb_strpos($value, $ref) !== false) break;
			elseif (mb_strpos($ref, $value) !== false) break;
			else $key = false;
		}
		if ($key === false) return '';
		$txt = $content[$key]['excerpt'];
		if (function_exists('bg_bibrefs')) $value = bg_bibrefs($value);
		$title = '<p><strong>'.$content[$key]['title'].' '.$content[$key]['desc'].' ('.$value.')</strong></p>';
	}
	if ($txt) $txt = '<div class="bg_content">'.$title.$txt.'</div>';
	return $txt;
}
	


$ref = $_POST["ref"];
if (!empty($ref)) {
	$text = bg_get_bible ($ref);					// Служебные Апостол и Евангелие
	if (!$text) $text = bg_get_paremiaes ($ref);	// Служебные Паримии
?>
<hr>
<div class="bg_hide_block">
	<input id="bg_hide_block2" type="button" value="&#215;" title="<?php echo _("Скрыть"); ?>">
</div>
<?php
	echo $text.'<br>'; 
}
exit();

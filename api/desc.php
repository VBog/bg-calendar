<?php 






$desc = $_POST["desc"];
$text = '';
if (!empty($desc)) {
	$json = file_get_contents( dirname(dirname(__FILE__)).'/descriptions.json' );
	$descriptions = json_decode ($json, true);
	foreach ($descriptions[$desc] as $description) {
		$text .= '<h4>'.$description['title'].'</h4>';			// Заголовок Жития
		$text .= '<p>'.$description['text'].'</p>';				// Текст Жития
	}
?>
<hr>
<div class="bg_hide_block">
	<input id="bg_hide_block1" type="button" value="&#215;" title="<?php echo _("Скрыть"); ?>">
</div>
<?php
	if ($text) $text = '<div class="bg_content">'.$text.'</div>';
	echo $text.'<br>'; 
}
exit();

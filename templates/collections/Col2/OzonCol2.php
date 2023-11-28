<?php
$blocks = [];
foreach ($component['props']['innerElements'] as $col_item) {
	$block = [];
	$text_align = $component['props']['textAlign'] ?? "left";
	$this->check_count($component['props']['innerElements']);
	$block['img'] = $this->parse_img($col_item, 700, 640);
    $block['title'] = $this->parse_text($col_item['props']['titleContent'], $component['props']["titleFontSize"], $text_align);
    $block['text'] = $this->parse_text($col_item['props']['descriptionContent'], $component['props']["descriptionFontSize"], $text_align);
	$block['imgLink'] = ($col_item['props']['imgLink'] != '') ? 'https://ozon.ru/' . $col_item['props']["imgLink"] : '';
	$blocks[] = $block;
}
$ozon_block = [
        'widgetName' => 'raShowcase',
        'type' => 'tileXL',
        'blocks' => $blocks
];
$ozon_content_inner[] = $ozon_block;

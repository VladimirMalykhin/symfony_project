<?php
$blocks = [];
$this->check_count($component['props']['innerElements']);
$text_align = $component['props']['textAlign'] ?? "left";
foreach ($component['props']['innerElements'] as $col_item) {
    $block = [];
    $block['img'] = $this->parse_img($col_item, 50, 40);
    $block['title'] = $this->parse_text($col_item['props']['titleContent'], $component['props']["titleFontSize"], $text_align);
    $block['text'] = $this->parse_text($col_item['props']['descriptionContent'], $component['props']["descriptionFontSize"], $text_align);
	$block['imgLink'] = ($col_item['props']['imgLink'] != '') ? 'https://ozon.ru/' . $col_item['props']["imgLink"] : '';
    $blocks[] = $block;
}
$ozon_block = [
    'widgetName' => 'raShowcase',
    'type' => 'tileSecondary',
    'blocks' => $blocks
];
$ozon_content_inner[] = $ozon_block;

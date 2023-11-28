<?php
$block = [];
$text_align = $component['props']['textAlign'] ?? "left";


    if ($component['props']['direction'] == 'left'){
        $block['reverse'] = true;
    }
    $block['img'] = $this->parse_img($component, 708, 640);
    $block['title'] = $this->parse_text($component['props']['titleContent'], $component['props']["titleFontSize"], $text_align);
    $block['text'] = $this->parse_text($component['props']['descriptionContent'], $component['props']["descriptionFontSize"], $text_align);
	$block['imgLink'] = ($component['props']['imgLink'] != '') ? 'https://ozon.ru/' . $component['props']["imgLink"] : '';
	$blocks[] = $block;

$ozon_block = [
        'widgetName' => 'raShowcase',
        'type' => 'chess',
        'blocks' => [$block]
];
$ozon_content_inner[] = $ozon_block;

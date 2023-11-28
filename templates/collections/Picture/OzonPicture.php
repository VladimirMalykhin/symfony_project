<?php
$block = [];
$block['imgLink'] = ($component['props']['imgLink'] != '' && $component['props']['imgLink'] != []) ? 'https://ozon.ru/' . $component['props']["imgLink"] : '';
$block['img'] = $this->parse_img($component, 1416, 640, true);
$block['title']['content'] = [""];
$block['text']['content'] = [""];
$ozon_block = [
    'widgetName' => 'raShowcase',
    'type' => 'billboard',
    'blocks' => [$block]
];
$ozon_content_inner[] = $ozon_block;
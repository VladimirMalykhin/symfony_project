<?php
$blocks = [];
$text_align = $component['props']['textAlign'] ?? "left";
foreach ($component['props']['innerElements'] as $col_item) {
	$block = [];
    $title_object = $this->parse_text($col_item['props']['titleContent'], "size5", $text_align);
    $block[] = $title_object['content'];
    $text_object = $this->parse_text($col_item['props']['descriptionContent'], "size5", $text_align);
    $block[] = $text_object['content'];
	$blocks[] = $block;
}
$ozon_block = [
        'widgetName' => 'raTable',

        'table' => [
        	'head' => [
        	    [
        	        "text" => [" "],
                    "contentAlign" => $text_align
                ],
                [
                    "text" => [" "],
                    "contentAlign" => $text_align
                ]
            ],
        	'body' => $blocks
		]
];
$ozon_content_inner[] = $ozon_block;

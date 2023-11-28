<?php
$titleAlign = $component['props']['titleTextAlign'] ?? "left";
$descriptionAlign = $component['props']['descriptionTextAlign'] ?? "left";
if($titleAlign == []) $titleAlign = "left";
if($descriptionAlign == []) $descriptionAlign = "left";
$ozon_block = [
        'widgetName' => 'raTextBlock',
        'title' => $this->parse_text($component['props']['titleContent'], $component['props']["titleFontSize"], $titleAlign, $component['props']["titleColor"]),
		'text' => $this->parse_text($component['props']['descriptionContent'], $component['props']["descriptionFontSize"], $descriptionAlign, $component['props']["descriptionColor"]),
		'theme' => $component['props']['theme'],
		'gapSize' => $component['props']['space'],
		'padding' => $component['props']['padding']
      ];
$ozon_content_inner[] = $ozon_block;
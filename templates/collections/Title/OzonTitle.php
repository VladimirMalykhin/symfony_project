<?php
$text_align = $component['props']['textAlign'] ?? "left";
if($text_align == []) $text_align = "left";
$ozon_block = [
        'widgetName' => 'raTextBlock',
        'title' => $this->parse_text($component['props']['content'], $component['props']["fontSize"], $text_align)
      ];
$ozon_content_inner[] = $ozon_block;
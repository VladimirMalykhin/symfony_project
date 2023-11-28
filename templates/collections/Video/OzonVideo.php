<?php
require_once('/home/my24ttl/libs/getID3-master/getid3/getid3.php');
$getID3 = new getID3();
$filename= $this->public_directory.$component['props']['src'];
$fileinfo = $getID3->analyze($filename);

$width=$fileinfo['video']['resolution_x'];
$height=$fileinfo['video']['resolution_y'];
$component['props']['src'] = str_replace('/uploads/'.$this->number.'/ru/minisite/ozon_template/', '../../', $component['props']['src']);
$ozon_block = [
    'widgetName' => 'raVideo',
    'type' => 'embedded',
    'width' => $width,
    'height' => $height,
    'sources' => [
        [
        'type' => 'video/mp4',
        'src' => $component['props']['src']
        ]
    ]
];

$ozon_content_inner[] = $ozon_block;
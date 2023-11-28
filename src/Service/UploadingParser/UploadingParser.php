<?php

namespace App\Service\UploadingParser;



use Symfony\Component\HttpFoundation\JsonResponse;

use App\Service\MediaParser\MediaParser;
use App\Infrastructure\ParametersInterface\ParametersInterface;


class UploadingParser
{
    private array $template_object;

    public function __construct($template_object)
    {
        $this->template_object = $template_object;
    }


    private function remove_typograf($string)
    {
        $string = strip_tags(
            trim($string)
        );

        $replaceable = [
            '¹', '²', '³', '⁴', '⁵', '⁶', '⁷', '⁸', '⁹',
            '¹⁰', '¹¹','¹²', '¹³', '¹⁴', '¹⁵', '¹⁶', '¹⁷', '¹⁸',
            '¹⁹', '²⁰'
        ];

        for ($i = 0; $i < 20; $i++) {
            $string = str_replace('<sup>' . $i . '</sup>', $replaceable[$i], $string);
        }

        // с пробелом
        for ($i = 0; $i < 20; $i++) {
            $string = str_replace('<sup>' . $i . ' </sup>', $replaceable[$i], $string);
        }

        switch (true) {
            case strripos($string, '<sup>TM</sup>'):
                $string = str_replace("<sup>TM</sup>", '™', $string);
                break;
            case strripos($string, '\t'):
                $string = str_replace('\t', '', $string);
                break;
            default:
                $string = html_entity_decode(strip_tags($string));
        }
        return $string;
    }


    private function parse_text($item) :array
    {
        $param_object = new ParametersInterface();
        foreach($param_object->getUsedParams('text', $item['props']) as $param_name){
            if($item['props'][$param_name] != '' && isset($item['props'][$param_name])) {
                $item['props'][$param_name] = $this->remove_typograf($item['props'][$param_name]);
            }
        }
        foreach($param_object->getUsedArrayParams('text_array', $item['props']) as $param_name){
            for($i = 0; $i < count($item['props'][$param_name]); $i++){
                if($item['props'][$param_name][$i] != '' && isset($item['props'][$param_name])){
                    $item['props'][$param_name][$i] = $this->remove_typograf($item['props'][$param_name][$i]);
                }
            }
        }
        return $item;
    }


    private function parse_text_epackage($item) :array
    {
        $param_object = new ParametersInterface();
        foreach($param_object->getUsedParams('text', $item['props']) as $param_name){
            if($item['props'][$param_name] != '' && isset($item['props'][$param_name])) {
                $item['props'][$param_name] = str_replace("<br>", "\n", $item['props'][$param_name]);
                $item['props'][$param_name] = $this->remove_typograf($item['props'][$param_name]);
                $item['props'][$param_name] = str_replace("\r\n", "<br>", $item['props'][$param_name]);
                $item['props'][$param_name] = str_replace("\r", "<br>", $item['props'][$param_name]);
                $item['props'][$param_name] = str_replace("\n", "<br>", $item['props'][$param_name]);
            }
        }
        foreach($param_object->getUsedArrayParams('text_array', $item['props']) as $param_name){
            for($i = 0; $i < count($item['props'][$param_name]); $i++){
                if($item['props'][$param_name][$i] != '' && isset($item['props'][$param_name])){
                    $item['props'][$param_name][$i] = str_replace("<br>", "\n", $item['props'][$param_name]);
                    $item['props'][$param_name][$i] = $this->remove_typograf($item['props'][$param_name][$i]);
                    $item['props'][$param_name][$i] = str_replace("\r\n", "<br>", $item['props'][$param_name][$i]);
                    $item['props'][$param_name][$i] = str_replace("\r", "<br>", $item['props'][$param_name][$i]);
                    $item['props'][$param_name][$i] = str_replace("\n", "<br>", $item['props'][$param_name][$i]);
                }
            }
        }
        return $item;
    }


    public function parse_upload() :array
    {
        for($i=0;$i<count($this->template_object); $i++) {
            $this->template_object[$i] = $this->parse_text($this->template_object[$i]);
            if(isset($this->template_object[$i]['props']['innerElements'])){
                for($c=0;$c<count($this->template_object[$i]['props']['innerElements']); $c++) {
                    $this->template_object[$i]['props']['innerElements'][$c] = $this->parse_text($this->template_object[$i]['props']['innerElements'][$c]);
                }
            }
        }
        return $this->template_object;
    }


    public function parse_upload_epackage() :array
    {
        for($i=0;$i<count($this->template_object); $i++) {
            $this->template_object[$i] = $this->parse_text_epackage($this->template_object[$i]);
            if(isset($this->template_object[$i]['props']['innerElements'])){
                for($c=0;$c<count($this->template_object[$i]['props']['innerElements']); $c++) {
                    $this->template_object[$i]['props']['innerElements'][$c] = $this->parse_text_epackage($this->template_object[$i]['props']['innerElements'][$c]);
                }
            }
        }
        return $this->template_object;
    }
}
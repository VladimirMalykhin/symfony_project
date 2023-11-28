<?php

namespace App\Service\TemplateParser;



use Symfony\Component\HttpFoundation\JsonResponse;

use App\Service\MediaParser\MediaParser;
use App\Infrastructure\ParametersInterface\ParametersInterface;

class TemplateParser
{
    private array $template_object;
    private string $template_name;
    private string $locale_name;
    private int $epackage_number;
    private string $temp_folder;
    private string $epackage_folder;
    private array $images = [];

    public function __construct($template_object, $template_name, $epackage_number, $temp_folder, $epackage_folder, $locale_name)
    {
        $this->template_object = $template_object;
        $this->template_name = $template_name;
        $this->epackage_number = $epackage_number;
        $this->temp_folder = $temp_folder;
        $this->epackage_folder = $epackage_folder;
        $this->locale_name = $locale_name;
    }


    private function parse_src_mvideo($param) :array
    {
        $param_object = new ParametersInterface();
        foreach($param_object->getUsedParams('media', $param['props']) as $param_name){
            if(isset($param['props'][$param_name]) && $param['props'][$param_name] != ''){
                $media_parser = new MediaParser($param['props'][$param_name], $this->template_name, $this->epackage_number, $this->temp_folder, $this->epackage_folder, $this->locale_name);
                $param['props'][$param_name] = $media_parser->parse();
            }

        }
        return $param;
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


    public function parse_src($param) :array
    {
        if(isset($param['props']['srcMob']) && (strpos($param['props']['srcMob'],'placeholder')) && (!strpos($param['props']['src'],'placeholder'))){
            $param['props']['srcMob'] = $param['props']['src'];
        }
        $param_object = new ParametersInterface();
        foreach($param_object->getUsedParams('media', $param['props']) as $param_name){
            if(isset($param['props'][$param_name]) && $param['props'][$param_name] != ''){
                $media_parser = new MediaParser($param['props'][$param_name], $this->template_name, $this->epackage_number, $this->temp_folder, $this->epackage_folder, $this->locale_name);
                $param['props'][$param_name] = $media_parser->parse();
                $this->images = array_merge($this->images, $media_parser->getImages());
            }

        }
        foreach($param_object->getUsedArrayParams('media_array', $param['props']) as $param_name){
                for($i = 0; $i < count($param['props'][$param_name]); $i++){
                    if(isset($param['props'][$param_name])){
                        $media_parser = new MediaParser($param['props'][$param_name][$i], $this->template_name, $this->epackage_number, $this->temp_folder, $this->epackage_folder, $this->locale_name);
                        $param['props'][$param_name][$i] = $media_parser->parse();
                        $this->images = array_merge($this->images, $media_parser->getImages());
                    }

                }

        }
        return $param;
    }


    public function parse_src_optimizing($param) :array
    {

        $param_object = new ParametersInterface();
        foreach($param_object->getUsedParams('media', $param['props']) as $param_name){
            if(isset($param['props'][$param_name]) && $param['props'][$param_name] != ''){
                $media_parser = new MediaParser($param['props'][$param_name], $this->template_name, $this->epackage_number, $this->temp_folder, $this->epackage_folder, $this->locale_name);
                $param['props'][$param_name] = $media_parser->optimize();
            }

        }
        foreach($param_object->getUsedArrayParams('media_array', $param['props']) as $param_name){
                for($i = 0; $i < count($param['props'][$param_name]); $i++){
                    if(isset($param['props'][$param_name])){
                        $media_parser = new MediaParser($param['props'][$param_name][$i], $this->template_name, $this->epackage_number, $this->temp_folder, $this->epackage_folder, $this->locale_name);
                        $param['props'][$param_name][$i] = $media_parser->optimize();
                    }

                }

        }
        return $param;
    }


    public function getImages()
    {
        return $this->images;
    }


    private function sort_img()
    {
        for($i=0;$i<count($this->template_object); $i++) {
            if(isset($this->template_object[$i]['props']['src'])){
                $title_img = explode('img/', $this->template_object[$i]['props']['src']);
                $number_img = explode('.', $title_img[1]);
                $new_img = 'img-' . ($i + 1);
                rename($this->temp_folder.$this->template_object[$i]['props']['src'], $this->temp_folder.$title_img[0].'img/'.$new_img.'.'.$number_img[1]);
                $this->template_object[$i]['props']['src'] = str_replace($number_img[0], $new_img, $this->template_object[$i]['props']['src']);

                if(isset($this->template_object[$i]['props']['srcMob'])){
                    $this->template_object[$i]['props']['srcMob'] = str_replace($number_img[0], $new_img, $this->template_object[$i]['props']['srcMob']);
                }
            }
        }
    }


    private function check_structure()
    {
        if(!is_dir($this->epackage_folder.'/'.$this->epackage_number.'/'.$this->locale_name.'/minisite/'.$this->template_name)){
            mkdir($this->epackage_folder.'/'.$this->epackage_number.'/'.$this->locale_name.'/minisite/'.$this->template_name);
            mkdir($this->epackage_folder.'/'.$this->epackage_number.'/'.$this->locale_name.'/minisite/'.$this->template_name.'/content');
            mkdir($this->epackage_folder.'/'.$this->epackage_number.'/'.$this->locale_name.'/minisite/'.$this->template_name.'/content/json');
            mkdir($this->epackage_folder.'/'.$this->epackage_number.'/'.$this->locale_name.'/minisite/'.$this->template_name.'/assets');
            mkdir($this->epackage_folder.'/'.$this->epackage_number.'/'.$this->locale_name.'/minisite/'.$this->template_name.'/assets/img');
        }
		if(!is_dir($this->epackage_folder.'/'.$this->epackage_number.'/'.$this->locale_name.'/minisite/'.$this->template_name.'/assets/img')){
			mkdir($this->epackage_folder.'/'.$this->epackage_number.'/'.$this->locale_name.'/minisite/'.$this->template_name.'/assets/img');
		}
    }


    private function check_render_structure()
    {
        if($this->template_name != 'ozon_template'){
            if(!is_dir($this->epackage_folder.'/'.$this->epackage_number.'/'.$this->locale_name.'/minisite/'.$this->template_name.'/content/iframe')){
                mkdir($this->epackage_folder.'/'.$this->epackage_number.'/'.$this->locale_name.'/minisite/'.$this->template_name.'/content/iframe');
            }
            if(!is_dir($this->epackage_folder.'/'.$this->epackage_number.'/'.$this->locale_name.'/minisite/'.$this->template_name.'/content/html')){
                mkdir($this->epackage_folder.'/'.$this->epackage_number.'/'.$this->locale_name.'/minisite/'.$this->template_name.'/content/html');
            }
        }
        
    }


    private function check_font_structure()
    {
        if(!is_dir($this->epackage_folder.'/'.$this->epackage_number.'/'.$this->locale_name.'/minisite/'.$this->template_name.'/assets/fonts')){
            mkdir($this->epackage_folder.'/'.$this->epackage_number.'/'.$this->locale_name.'/minisite/'.$this->template_name.'/assets/fonts');
        }
    }


    private function write_results() :void
    {
        file_put_contents($this->epackage_folder.'/'.$this->epackage_number.'/'.$this->locale_name.'/minisite/'.$this->template_name.'/content/json/index-helper.json', json_encode($this->template_object));
    }


    public function parse_mvideo() :array
    {
        $this->template_object['props'] = $this->template_object;
        $this->template_object = $this->parse_src_mvideo($this->template_object);
        $object_response = $this->template_object['props'];
        return $object_response;
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


    public function parse() :array
    {
        $this->check_structure();
        if($this->template_name != 'ozon_template'){
            $this->check_render_structure();
        }
        for($i=0;$i<count($this->template_object); $i++) {
            $this->template_object[$i] = $this->parse_src($this->template_object[$i]);
            $this->template_object[$i] = $this->parse_text($this->template_object[$i]);
            if(isset($this->template_object[$i]['props']['innerElements'])){
                for($c=0;$c<count($this->template_object[$i]['props']['innerElements']); $c++) {
                    $this->template_object[$i]['props']['innerElements'][$c] = $this->parse_src($this->template_object[$i]['props']['innerElements'][$c]);
                    $this->template_object[$i]['props']['innerElements'][$c] = $this->parse_text($this->template_object[$i]['props']['innerElements'][$c]);
                }
            }
        }
        if($this->template_name == 'img'){
            $this->sort_img();
        }
        $this->write_results();
        return $this->template_object;
    }


    public function parse_optimizing() :array
    {
        for($i=0;$i<count($this->template_object); $i++) {
            $this->template_object[$i] = $this->parse_src_optimizing($this->template_object[$i]);
            if(isset($this->template_object[$i]['props']['innerElements'])){
                for($c=0;$c<count($this->template_object[$i]['props']['innerElements']); $c++) {
                    $this->template_object[$i]['props']['innerElements'][$c] = $this->parse_src_optimizing($this->template_object[$i]['props']['innerElements'][$c]);
                }
            }
        }

        return $this->template_object;
    }

}
<?php

namespace App\Service\TemplateService\EldService;

use App\Service\MediaParser\MediaParser;
use App\Infrastructure\ParametersInterface\ParametersInterface;
use App\Service\TemplateService\BaseService;
use App\Service\Typograf\Typograf;


class EldService
{

    private int $number;
    private string $locale_name;
    private string $template_name;
    private string $epack_directory;
    private string $styles;
    private string $public_directory;
    private string $collections_folder;
    private array $content;
    private array $content_items;
    private $remoteTypograf;


    public function __construct(array $content, int $number, string $public_directory, string $epack_directory, string $locale_name, string $template_name, string $collections_folder)
    {
        $this->epack_directory = $epack_directory;
        $this->number = $number;
        $this->public_directory = $public_directory;
        $this->locale_name = $locale_name;
        $this->template_name = $template_name;
        $this->content = $content;
        $this->collections_folder = $collections_folder;
        $this->styles = '';
        $this->content_items = [];
        $this->remoteTypograf = new Typograf('utf-8');
        $this->remoteTypograf = new Typograf('utf-8');
        $this->remoteTypograf->htmlEntities();
        $this->remoteTypograf->br (false);
        $this->remoteTypograf->p (true);
        $this->remoteTypograf->nobr (3);
        $this->remoteTypograf->quotA ('laquo raquo');
        $this->remoteTypograf->quotB ('bdquo ldquo');

    }

    private function parse_img($item, $number, $template_name, $locale) :array
    {
        $param_object = new ParametersInterface();
        foreach($param_object->getUsedParams('media', $item['props']) as $param_name){
            if(isset($item['props'][$param_name]) && $item['props'][$param_name] != '') {
                $item['props'][$param_name] = str_replace('/uploads/' . $number . '/' . $locale . '/minisite/' . $template_name . '/', '../../', $item['props'][$param_name]);
            }

        }

        foreach($param_object->getUsedArrayParams('media_array', $item['props']) as $param_name){
            for ($i = 0; $i < count($item['props'][$param_name]); $i++){
                if(isset($item['props'][$param_name])){
                    $item['props'][$param_name][$i] = str_replace('/uploads/' . $number . '/'.$locale.'/minisite/'.$template_name.'/', '../../', $item['props'][$param_name][$i]);
                }

            }
        }

        return $item;
    }

    private function parse_text($item) :array
    {
        $param_object = new ParametersInterface();
        foreach($param_object->getUsedParams('text', $item['props']) as $param_name){
            if($item['props'][$param_name] != '' && isset($item['props'][$param_name])) {
                $item['props'][$param_name] = str_replace("\r\n", "<br>", $item['props'][$param_name]);
                $item['props'][$param_name] = str_replace("\r", "<br>", $item['props'][$param_name]);
                $item['props'][$param_name] = str_replace("\n", "<br>", $item['props'][$param_name]);
                $item['props'][$param_name] = $this->start_typograf($item['props'][$param_name]);
            }
        }
        foreach($param_object->getUsedArrayParams('text_array', $item['props']) as $param_name){
            for($i = 0; $i < count($item['props'][$param_name]); $i++){
                if($item['props'][$param_name][$i] != '' && isset($item['props'][$param_name])){
                    $item['props'][$param_name][$i] = str_replace("\r\n", "<br>", $item['props'][$param_name][$i]);
                    $item['props'][$param_name][$i] = str_replace("\r", "<br>", $item['props'][$param_name][$i]);
                    $item['props'][$param_name][$i] = str_replace("\n", "<br>", $item['props'][$param_name][$i]);
                    $item['props'][$param_name][$i] = $this->start_typograf($item['props'][$param_name][$i]);
                }
            }
        }
        return $item;

    }


    private function add_basestyles()
    {
        $this->styles .= file_get_contents($this->collections_folder . '/Base/eldstyle.css');
        if(strpos($this->locale_name, '_ar'))
            $this->styles .= file_get_contents($this->collections_folder . '/Base/arstyle.css');
    }


    private function add_styles($content)
    {
        $this->styles .= file_get_contents($this->collections_folder . '/' . $content['component'] . '/' . $content['component'] . '.css');
    }


    private function start_typograf($text)
    {
        return $this->remoteTypograf->processText($text);
    }


    private function parse_item($item) :array
    {
        $baseService = new BaseService();
        if(isset($item['props']['innerElements']))
        {
            for($i = 0; $i < count($item['props']['innerElements']); $i++){
                $item['props']['innerElements'][$i] = $this->parse_img($item['props']['innerElements'][$i], $this->number, $this->template_name, $this->locale_name);
                $item['props']['innerElements'][$i] = $this->parse_text($item['props']['innerElements'][$i]);
            }
        }
        $item = $this->parse_img($item, $this->number, $this->template_name, $this->locale_name);
        $item = $this->parse_text($item);

        return $item;
    }


    public function parse()
    {
        $this->add_basestyles();
        $baseService = new BaseService();
        for($i = 0; $i < count($this->content); $i++){
            $this->content[$i] = $this->parse_item($this->content[$i]);
            if(isset($this->content[$i]['component'])){
                if(!in_array($this->content[$i]['component'], $this->content_items)){
                    $this->add_styles($this->content[$i]);
                    $this->content_items[] = $this->content[$i]['component'];
                }
            }
        }
        $baseService->write_results($this->epack_directory . '/' . $this->number . '/' . $this->locale_name . '/' . 'minisite/'.$this->template_name.'/content/json/index.json', $this->content);
        return $this->content;
    }


    public function getStyles() :string
    {
        return $this->styles;
    }

}

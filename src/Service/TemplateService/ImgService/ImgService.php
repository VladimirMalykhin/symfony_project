<?php
namespace App\Service\TemplateService\ImgService;

use App\Infrastructure\ParametersInterface\ParametersInterface;
use App\Service\TemplateService\BaseService;

class ImgService
{

    private int $number;
    private string $locale_name;
    private string $epack_directory;
    private string $public_directory;
    private array $content;
    private string $template_name;
    private string $styles;
    

    public function __construct(array $content, int $number, string $public_directory, string $epack_directory, string $locale_name, string $template_name)
    {
        $this->content = $content;
        $this->epack_directory = $epack_directory;
        $this->number = $number;
        $this->public_directory = $public_directory;
        $this->locale_name = $locale_name;
        $this->template_name = $template_name;
        $this->styles = '';
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


    private function parse_item($item) :array
    {
        $baseService = new BaseService();
        $item = $this->parse_img($item, $this->number, $this->template_name, $this->locale_name);
        return $item;
    }


    public function parse()
    {
        $baseService = new BaseService();
        for($i = 0; $i < count($this->content); $i++){
            $this->content[$i] = $this->parse_item($this->content[$i]);
        }
        $baseService->write_results($this->epack_directory . '/' . $this->number . '/' . $this->locale_name . '/' . 'minisite/img_template/content/json/index.json', $this->content);
        return $this->content;
    }


    public function getStyles() :string
    {
        return $this->styles;
    }

}
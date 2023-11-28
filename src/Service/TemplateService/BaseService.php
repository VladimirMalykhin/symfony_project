<?php
namespace App\Service\TemplateService;

use App\Infrastructure\ParametersInterface\ParametersInterface;

class BaseService
{

    public final function parse_text($item) :array
    {
        $item = str_replace("\r\n", '<br>', $item);
        $item = str_replace("\r", '<br>', $item);
        $item = str_replace("\n", '<br>', $item);
        return json_decode($item, true);
    }


    public final function parse_img($item, $number, $template_name, $locale) :array
    {
        $param_object = new ParametersInterface();
        foreach($param_object->getUsedParams('media', $item['props']) as $param_name){
            if(isset($item['props'][$param_name])){
                $item['props'][$param_name] = str_replace('/uploads/' . $number . '/'.$locale.'/minisite/'.$template_name.'/', '../../', $item['props'][$param_name]);
            }


        }

        foreach($param_object->getUsedArrayParams('media_array', $item['props']) as $param_name){
            for ($i = 0; $i < count($item['props'][$param_name]); $i++){
                $item['props'][$param_name][$i] = str_replace('/uploads/' . $number . '/'.$locale.'/minisite/'.$template_name.'/', '../../', $item['props'][$param_name][$i]);
            }
        }

        return $item;
    }


    public function write_results($file_path, $content)
    {
        file_put_contents($file_path, json_encode($content));
    }

}

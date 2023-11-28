<?php

namespace App\Service\LocaleParser;


use App\Service\MediaParser\MediaParser;
use App\Service\TemplateService\AliService\AliService;
use App\Service\TemplateService\EldService\EldService;
use App\Service\TemplateService\MvideoService\MvideoService;
use App\Service\TemplateService\MvideoServiceBase\MvideoServiceBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\TemplateService\MasterService\MasterService;
use App\Service\TemplateService\OzonService\OzonService;
use App\Service\TemplateService\AmazonService\AmazonService;
use App\Service\TemplateService\ImgService\ImgService;
use App\Service\TemplateParser\TemplateParser;

class LocaleParser
{
    private string $name;
    private string $epackage_folder;
    private string $styles;
    private string $font_styles;
    private string $public_folder;
    private string $base_folder;
    private string $fonts_folder;
    private array $locale_content;
    private array $render_content;
    private array $fonts_used;
    private int $number;
    private string $collections_folder;
    private array $images = [];


    public function __construct($name, $locale_content, $number, $base_folder, $fonts)
    {
        $this->name = $name;
        $this->epackage_folder = $base_folder.'/public/uploads';
        $this->fonts_folder = $base_folder.'/public/fonts';
        $this->public_folder = $base_folder.'/public';
        $this->locale_content = $locale_content;
        $this->number = $number;
        $this->base_folder = $base_folder;
        $this->fonts_used = $fonts;
        $this->collections_folder = $base_folder.'/templates/collections';
        $this->styles = '';
        $this->font_styles = '';
        $this->render_content = [];
    }


    public function getRenderContent() :array
    {
        return $this->render_content;
    }


    private function add_render($content, $template_name)
    {
        $this->render_content[] = ['content' => $content, 'styles' => $this->styles, 'template' => $template_name];
    }


    private function check_structure()
    {
        if(!is_dir($this->epackage_folder.'/'.$this->number.'/'.$this->name)){
            mkdir($this->epackage_folder.'/'.$this->number.'/'.$this->name);
            mkdir($this->epackage_folder.'/'.$this->number.'/'.$this->name.'/minisite');
        }
    }


    public function parse_cover($item)
    {
        $template_object = new TemplateParser($item, 'mvideo_template', $this->number, $this->public_folder, $this->epackage_folder, $this->name);
        return $template_object->parse_mvideo();
    }


    private function parse_fonts($template_name)
    {
        if(!is_dir($this->epackage_folder . '/' . $this->number . '/'.$this->name.'/minisite/'.$template_name.'/assets')) {
            mkdir($this->epackage_folder . '/' . $this->number . '/'.$this->name.'/minisite/'.$template_name.'/assets');
        }
        if(!is_dir($this->epackage_folder . '/' . $this->number . '/'.$this->name.'/minisite/'.$template_name.'/assets/fonts')){
            mkdir($this->epackage_folder . '/' . $this->number . '/'.$this->name.'/minisite/'.$template_name.'/assets/fonts');
        }
        foreach ($this->fonts_used as $font)
        {
            $font_content = file_get_contents($this->fonts_folder . '/' . $font . '/font.css');
            $this->font_styles .= str_replace('&quot;', '"', $font_content);
            $this->copy_folder($this->fonts_folder . '/' . $font, $this->epackage_folder . '/' . $this->number . '/'.$this->name.'/minisite/'.$template_name.'/assets/fonts/'.$font);
        }
    }


    private function parse_templates()
    {
        foreach($this->locale_content as $template_key => $template_value){
            if($this->locale_content[$template_key] != []){
                $template_object = new TemplateParser($template_value, $template_key, $this->number, $this->public_folder, $this->epackage_folder, $this->name);
                $this->locale_content[$template_key] = $template_object->parse();
                $this->images = array_merge($this->images, $template_object->getImages());
            }
        }
    }


    private function parse_templates_optimizing()
    {
        foreach($this->locale_content as $template_key => $template_value){
            if($this->locale_content[$template_key] != []){
                $template_object = new TemplateParser($template_value, $template_key, $this->number, $this->public_folder, $this->epackage_folder, $this->name);
                $this->locale_content[$template_key] = $template_object->parse_optimizing();
            }
        }
    }


    private function parse_templates_uploading()
    {
        foreach($this->locale_content as $template_key => $template_value){
            if($this->locale_content[$template_key] != []){
                $template_object = new TemplateParser($template_value, $template_key, $this->number, $this->public_folder, $this->epackage_folder, $this->name);
                $this->locale_content[$template_key] = $template_object->parse();
            }
        }
    }


    private function change_templates() :array
    {
        
        foreach($this->locale_content as $template_key => $template_value){
            $this->styles = '';
            switch ($template_key) {
                case 'ozon_template':
                    if($this->locale_content['ozon_template'] != []){
                        $template_object = new OzonService($template_value, $this->number, $this->public_folder, $this->epackage_folder, $this->name, $this->base_folder);
                        $template_object->parse();
                    }
                    break; 
                case 'amazon_template':
                    if($this->locale_content['amazon_template'] != []){
                        $template_object = new AmazonService($template_value, $this->number, $this->public_folder, $this->epackage_folder, $this->name, $this->base_folder);
                        $template_object->parse();
                    }
                    break;
                case 'img_template':
                    if($this->locale_content['img_template'] != []){
                        $template_object = new ImgService($template_value, $this->number, $this->public_folder, $this->epackage_folder, $this->name, $template_key);
                        $this->locale_content[$template_key] = $template_object->parse();
                        $this->styles = $template_object->getStyles();
                        $this->add_render($this->locale_content[$template_key], $template_key);
                    }
                    break;
                case 'master_template':
                    $this->clear_folder($this->epackage_folder . '/' . $this->number . '/'.$this->name.'/minisite/'.$template_key.'/assets/fonts', $this->number);
                    if($this->locale_content['master_template'] != []){
                        $this->clear_folder($this->epackage_folder . '/' . $this->number . '/'.$this->name.'/minisite/'.$template_key.'/assets/fonts', $this->number);
                        $this->parse_fonts($template_key);
                        if($this->locale_content[$template_key] != []){
                            $template_object = new MasterService($template_value, $this->number, $this->public_folder, $this->epackage_folder, $this->name, $template_key, $this->collections_folder);
                            $this->locale_content[$template_key] = $template_object->parse();
                            $this->styles = $template_object->getStyles();
                            $this->styles .= $this->font_styles;
                            $this->add_render($this->locale_content[$template_key], $template_key);
                        }
                    }
                    break;
                case 'ali_template':
                    $this->clear_folder($this->epackage_folder . '/' . $this->number . '/'.$this->name.'/minisite/'.$template_key.'/assets/fonts', $this->number);
                    if($this->locale_content['ali_template'] != []){
                        $this->clear_folder($this->epackage_folder . '/' . $this->number . '/'.$this->name.'/minisite/'.$template_key.'/assets/fonts', $this->number);
                        $this->parse_fonts($template_key);
                        if($this->locale_content[$template_key] != []){
                            $template_object = new AliService($template_value, $this->number, $this->public_folder, $this->epackage_folder, $this->name, $template_key, $this->collections_folder);
                            $this->locale_content[$template_key] = $template_object->parse();
                            $this->styles = $template_object->getStyles();
                            $this->styles .= $this->font_styles;
                            $this->add_render($this->locale_content[$template_key], $template_key);
                        }
                    }
                    break;
                case 'eld_template':
                    $this->clear_folder($this->epackage_folder . '/' . $this->number . '/'.$this->name.'/minisite/'.$template_key.'/assets/fonts', $this->number);
                    if($this->locale_content['eld_template'] != []){
                        $this->clear_folder($this->epackage_folder . '/' . $this->number . '/'.$this->name.'/minisite/'.$template_key.'/assets/fonts', $this->number);
                        $this->parse_fonts($template_key);
                        if($this->locale_content[$template_key] != []){
                            $template_object = new EldService($template_value, $this->number, $this->public_folder, $this->epackage_folder, $this->name, $template_key, $this->collections_folder);
                            $this->locale_content[$template_key] = $template_object->parse();
                            $this->styles = $template_object->getStyles();
                            $this->styles .= $this->font_styles;
                            $this->add_render($this->locale_content[$template_key], $template_key);
                        }
                    }
                    break;
                case 'mvideo_template':
                    if($this->locale_content[$template_key] != []){
                        $this->clear_folder($this->epackage_folder . '/' . $this->number . '/'.$this->name.'/minisite/'.$template_key.'/assets/fonts', $this->number);
                        $this->parse_fonts($template_key);
                        $template_object = new MvideoServiceBase($template_value, $this->number, $this->public_folder, $this->epackage_folder, $this->name, $template_key, $this->collections_folder);
                        $this->locale_content[$template_key] = $template_object->parse();
                        $this->styles = $template_object->getStyles();
                        $this->styles .= $this->font_styles;
                        $this->add_render($this->locale_content[$template_key], $template_key);
                    }
                    break;
                default:
                    if($this->locale_content[$template_key] != []){
						$this->clear_folder($this->epackage_folder . '/' . $this->number . '/'.$this->name.'/minisite/'.$template_key.'/assets/fonts', $this->number);
                        $this->parse_fonts($template_key);
                        $template_object = new MasterService($template_value, $this->number, $this->public_folder, $this->epackage_folder, $this->name, $template_key, $this->collections_folder);
                        $this->locale_content[$template_key] = $template_object->parse();
                        $this->styles = $template_object->getStyles();
                        $this->styles .= $this->font_styles;
                        $this->add_render($this->locale_content[$template_key], $template_key);
                    }
            }

        }
        return $this->locale_content;
        
    }


    public function parse_constructor() :array
    {
        $this->check_structure();
        $this->parse_templates();
        return $this->locale_content;
    }


    public function parse_for_optimizing() :array
    {
        $this->parse_templates_optimizing();
        return $this->locale_content;
    }


    public function parse_constructor_uploading() :array
    {
        $this->parse_templates();
        return $this->locale_content;
    }


    public function parse_epackage() :array
    {
        $this->change_templates();
        return $this->locale_content;
    }


    public function parse_cover_epackage($cover_item, $content)
    {
        $template_object = new MvideoService($cover_item, $content, $this->number, $this->public_folder, $this->epackage_folder, $this->name, $this->base_folder);
        $template_object->parse();
    }


    public function getImages()
    {
        return $this->images;
    }


    public function parse_copy($epackage_name)
    {
        foreach($this->locale_content as $template_key => $template_value) {
            $this->change_content($epackage_name, $template_key, 'index.json');
            $this->change_content($epackage_name, $template_key, 'index-helper.json');

        }
    }


    private function change_content($epackage_name, $template_name, $file_name)
    {
        if(file_exists($this->epackage_folder.'/'.$epackage_name.'/'.$this->name.'/minisite/'.$template_name.'/content/json/'.$file_name)){
            $content_file = file_get_contents($this->epackage_folder.'/'.$epackage_name.'/'.$this->name.'/minisite/'.$template_name.'/content/json/'.$file_name);
            $content_file = str_replace($this->number, $epackage_name, $content_file);
            file_put_contents($this->epackage_folder.'/'.$epackage_name.'/'.$this->name.'/minisite/'.$template_name.'/content/json/'.$file_name, $content_file);
        }
    }


    function clear_folder($dir, $number){
        if (is_dir($dir)) {
            $scn = scandir($dir);
            foreach ($scn as $files) {
                if ($files !== '.') {
                    if ($files !== '..') {
                        if (!is_dir($dir . '/' . $files)) {
                            if($files != $number.'.zip'){
                                unlink($dir . '/' . $files);
                            }
                        } else {
                            $this->clear_folder($dir . '/' . $files, $number);
                            rmdir($dir . '/' . $files);
                        }
                    }
                }
            }
        }
    }


    private function copy_folder($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if ($file != '.' && $file != '..') {
                if (is_dir($src . '/' . $file)) {
                    $this->copy_folder($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
}
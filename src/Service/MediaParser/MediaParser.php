<?php

namespace App\Service\MediaParser;


use Symfony\Component\HttpFoundation\JsonResponse;
use Spatie\ImageOptimizer\OptimizerChainFactory;
use App\Repository\ImagesRepository;
use App\Service\MediaImageService\MediaImageService;

class MediaParser
{
    private string $media_url;
    private string $template_name;
    private int $epackage_number;
    private string $temp_folder;
    private string $epackage_folder;
    private string $locale_name;
    private array $images = [];


    public function __construct($media_url, $template_name, $epackage_number, $temp_folder, $epackage_folder, $locale_name)
    {
        $this->media_url = $media_url;
        $this->template_name = $template_name;
        $this->epackage_number = $epackage_number;
        $this->temp_folder = $temp_folder;
        $this->epackage_folder = $epackage_folder;
        $this->locale_name = $locale_name;
    }


    private function folder_check() :void
    {
        if(strpos($this->media_url, 'video')){
            if(!is_dir($this->epackage_folder.'/'.$this->epackage_number.'/'.$this->locale_name.'/minisite/'.$this->template_name.'/assets/video')){
                mkdir($this->epackage_folder.'/'.$this->epackage_number.'/'.$this->locale_name.'/minisite/'.$this->template_name.'/assets/video');
            }
        }
        if(strpos($this->media_url, 'pdf')){
            if(!is_dir($this->epackage_folder.'/'.$this->epackage_number.'/'.$this->locale_name.'/minisite/'.$this->template_name.'/assets/pdf')){
                mkdir($this->epackage_folder.'/'.$this->epackage_number.'/'.$this->locale_name.'/minisite/'.$this->template_name.'/assets/pdf');
            }
        }
    }


    private function define_param($payload, $id_param) :string
    {
        $params = explode('/', $payload);
        return $params[$id_param];
    }


    private function placeholder_parse() :void
    {
        if (file_exists($this->temp_folder . $this->media_url)) {
            if (strpos($this->media_url, 'mp4')) {
                $folder_final = str_replace('/placeholders', '/video', $this->media_url);
            } else {
                $folder_final = str_replace('/placeholders', '/img', $this->media_url);
            }
            copy($this->temp_folder . $this->media_url, $this->epackage_folder . '/' . $this->epackage_number . '/'.$this->locale_name.'/minisite/' . $this->template_name . '/assets/' . $folder_final);
            $this->media_url = '/uploads' . '/' . $this->epackage_number . '/'.$this->locale_name.'/minisite/' . $this->template_name . '/assets' . $folder_final;
        } else {
            $this->media_url = 'File not found';
        }
    }


    private function tmp_parse() :void
    {
        if (file_exists($this->temp_folder . $this->media_url) && $this->media_url != '') {
            $final_src = str_replace('/temp', '', $this->media_url);
            copy($this->temp_folder . $this->media_url, $this->epackage_folder . '/' . $this->epackage_number . '/'.$this->locale_name.'/minisite/' . $this->template_name . '/assets/' . $final_src);
            $this->media_url = '/uploads' . '/' . $this->epackage_number . '/'.$this->locale_name.'/minisite/' . $this->template_name . '/assets' . $final_src;
        } elseif($this->media_url != '') {
            $this->media_url = 'File not found';
        }
    }


    private function master_parse() :void
    {
        $old_image = $this->media_url;
        $this->media_url = str_replace($this->define_param($this->media_url, 5), $this->template_name, $this->media_url);
        if(!file_exists($this->temp_folder.$this->media_url)){
            copy($this->temp_folder.$old_image, $this->temp_folder.$this->media_url);
        }
    }

    private function locale_parse() :void
    {
        $old_image = $this->media_url;
        $this->media_url = str_replace($this->define_param($this->media_url, 3), $this->locale_name, $this->media_url);
        if(!file_exists($this->temp_folder.$this->media_url)){
            copy($this->temp_folder.$old_image, $this->temp_folder.$this->media_url);
        }
    }


    private function locale_master_parse() :void
    {
        $old_image = $this->media_url;
        $this->media_url = str_replace($this->define_param($this->media_url, 3), $this->locale_name, $this->media_url);
        $this->media_url = str_replace($this->define_param($this->media_url, 5), $this->template_name, $this->media_url);
        if(!file_exists($this->temp_folder.$this->media_url)){
            copy($this->temp_folder.$old_image, $this->temp_folder.$this->media_url);
        }
    }


    public function optimize() :string
    {
        if($this->media_url != ""){
            $time_var = time();
            $rand_name = rand(1000000, 9999999);
            $picture_title = $time_var.$rand_name;
            $extension = explode('.', $this->media_url)[1];
            $epackageFolder = str_replace("uploads", '', $this->epackage_folder);
            if($extension != 'mp4' && $extension != 'pdf' &&  filesize($epackageFolder.$this->media_url) > 300000){
                copy($epackageFolder.$this->media_url, $this->temp_folder.'/temp/img/'.$picture_title.'.'.$extension);
                $optimizerChain = OptimizerChainFactory::create();

                $optimizerChain->optimize($this->temp_folder.'/temp/img/'.$picture_title.'.'.$extension);
                $url = '/temp/img/'.$picture_title.'.'.$extension;
            } else {
                $url = $this->media_url;
            }
			
			if($extension != 'mp4' && $extension != 'pdf'){
				$imageService = new MediaImageService($this->temp_folder.$url);
				$imageService->isResizing();
			}
            return $url;
        } else {
            return "";
        }
        
    }


    public function parse() :string
    {
        $this->folder_check();
        $epackageFolder = str_replace("uploads", '', $this->epackage_folder);
        if(strpos($this->media_url, $this->template_name) && strpos($this->media_url, $this->locale_name) && strpos($this->media_url, '/'.$this->locale_name.'/')){
            
            $this->images[] = ['epackage' => $this->epackage_number, 'url' => $this->media_url, 'size' => filesize($epackageFolder.$this->media_url)];
            return $this->media_url;
        } else {
            $count_parts = explode('/', $this->media_url);
            if(count($count_parts) > 5){

                if (!strpos($this->media_url, $this->template_name) && !strpos($this->media_url, '/'.$this->locale_name.'/')){
                    $this->locale_master_parse();
                    return $this->media_url;
                }
                if (!strpos($this->media_url, $this->template_name)){
                    $this->master_parse();
                    return $this->media_url;
                }
                if (!strpos($this->media_url, '/'.$this->locale_name.'/')){
                    $this->locale_parse();
                    return $this->media_url;
                }
            } else {
                if(strpos($this->media_url, 'laceholder')) {
                    $this->placeholder_parse();
                    return $this->media_url;
                }
                if(strpos($this->media_url, "temp/")) {
                    $this->tmp_parse();
                    return $this->media_url;
                }
            }


        }

        $this->images[] = ['epackage' => $this->epackage_number, 'url' => $this->media_url, 'size' => filesize($epackageFolder.$this->media_url)];

        return $this->media_url;
    }


    public function getImages()
    {
        return $this->images;
    }
}
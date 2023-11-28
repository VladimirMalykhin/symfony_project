<?php
namespace App\Service\EpackageCleaner;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EpackageCleaner
{
    private string $epackage_number;
    private string $epackage_folder;
    private array $structure;
    private string $cover_content;

    public function __construct($epackage_number, $epackage_folder, $structure, $cover_content)
    {
        $this->epackage_number = $epackage_number;
        $this->epackage_folder = $epackage_folder;
        $this->structure = $structure;
        $this->cover_content = $cover_content;
    }


    private function clean_files($template_name, $template_content, $locale_name, $folder_name) :void
    {
        if(is_dir($this->epackage_folder . '/' . $this->epackage_number . '/' . $locale_name . '/minisite/' . $template_name . '/assets/' . $folder_name)) {

            $files = scandir($this->epackage_folder . '/' . $this->epackage_number . '/' . $locale_name . '/minisite/' . $template_name . '/assets/' . $folder_name);

            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    if($template_name == 'mvideo_template'){
                        if (!strpos(json_encode($template_content), $file) && !strpos($this->cover_content, $file)) {
                            unlink($this->epackage_folder . '/' . $this->epackage_number . '/'. $locale_name .'/minisite/' . $template_name . '/assets/' . $folder_name . '/' . $file);
                        }
                    } else {
                        if (!strpos(json_encode($template_content), $file)) {
                            unlink($this->epackage_folder . '/' . $this->epackage_number . '/'. $locale_name .'/minisite/' . $template_name . '/assets/' . $folder_name . '/' . $file);
                        }
                    }

                }

            }
        }
    }


    private function clean_templates($name, $content) :void
    {
        $templates = [];
        foreach ($content as $template_name => $template_content){
            $templates[] = $template_name;
        }
        if(is_dir($this->epackage_folder . '/' . $this->epackage_number . '/' . $name . '/minisite')){
            $files = scandir($this->epackage_folder . '/' . $this->epackage_number . '/' . $name . '/minisite');
        } else {
            $files = scandir($this->epackage_folder . '/' . $this->epackage_number . '/ru/' . $name . '/minisite');
        }

        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                if (!in_array($file, $templates)) {
                    $this->emptyDir($this->epackage_folder . '/' . $this->epackage_number . '/' . $name . '/minisite/' . $file);
                    rmdir($this->epackage_folder . '/' . $this->epackage_number . '/' . $name . '/minisite/' . $file);
                } else {
                    $this->clean_files($file, $content[$file], $name, 'video');
                    $this->clean_files($file, $content[$file], $name, 'img');
                }
            }

        }
    }


    private function clean_locales() :void
    {
        $locales = [];
        foreach ($this->structure as $locale_name => $locale_content){
            $locales[] = $locale_name;
        }
        $files = scandir($this->epackage_folder . '/' . $this->epackage_number);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..' && $file != 'manifest.json') {
                if (!in_array($file, $locales)) {
                    $this->emptyDir($this->epackage_folder . '/' . $this->epackage_number . '/' . $file);
                    rmdir($this->epackage_folder . '/' . $this->epackage_number . '/' . $file);
                }
            }
        }
        foreach ($this->structure as $locale_name => $locale_content){
            $this->clean_templates($locale_name, $locale_content);
        }
    }


    private function emptyDir($dir)
    {
        if (is_dir($dir)) {
            $scn = scandir($dir);
            foreach ($scn as $files) {
                if ($files !== '.') {
                    if ($files !== '..') {
                        if (!is_dir($dir . '/' . $files)) {
                            unlink($dir . '/' . $files);
                        } else {
                            $this->emptyDir($dir . '/' . $files);
                            rmdir($dir . '/' . $files);
                        }
                    }
                }
            }
        }
    }


    public function clean_epackage() :void
    {
        $this->clean_locales();
    }

}
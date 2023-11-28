<?php

namespace App\Service\Tmp;

class TmpCleaning
{
    private string $project_name;
    private string $folder_name;

    public function __construct($project_name, $folder_name){
        $this->project_name = $project_name;
        $this->folder_name = $folder_name;
    }

    public function clean(){
        $files = glob('/home/my24ttl/'.$this->project_name.'/public/temp/'.$this->folder_name.'/*');
        foreach($files as $file) {
            $file_name = explode('.', $file);
            $file_number = $file_name[0];
            $full_names_file = explode($this->folder_name.'/', $file_number);
            $file_date = substr($full_names_file[1], 0,-7);
            $file_date = (int)$file_date;
            $moment_time = time();
            if($moment_time - $file_date > 86400){
                unlink($file);
            }
        }
    }
}
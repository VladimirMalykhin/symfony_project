<?php

namespace App\Service\ResponseService\Types;

abstract class BaseTypeResponse {


    protected function getDate($date) :array
    {
        return ['date' => $date];
    }


    protected function updateManifest($manifest) :array
    {
        if(!is_array($manifest['creationDate'])){
            $date_created = $manifest['creationDate'];
            $manifest['creationDate'] = [];
            $manifest['creationDate']['date'] = $date_created;
        }
        return $manifest;
    }
}
<?php

namespace App\Service\Api\Convertion;

class EpackageConvertion{

}


function convertParameters($code) :string
{
    $code = str_replace('file', 'packageId', $code);
    $code = str_replace('createdAt', 'date_created', $code);
    $code = str_replace('updatedAt', 'date_updated', $code);
    $code = str_replace('isUpdated', 'is_updated', $code);
    return $code;
}

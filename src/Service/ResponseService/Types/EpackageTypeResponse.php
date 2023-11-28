<?php

namespace App\Service\ResponseService\Types;

class EpackageTypeResponse extends BaseTypeResponse {

    private $code = 200;
    private $manifest;
    private $components;
    private $packageID;
    private $status;
    private $date_created;
    private $parameters;


    public function __construct($epackage, array $parameters)
    {
        $this->manifest = $this->updateManifest(json_decode($epackage['manifest'], true));
        $this->components = json_decode($epackage['structure'], true);
        $this->packageID = $epackage['file'];
        $this->status = $epackage['status'];
        $this->date_created = date_format(date_create($epackage['created_at']), 'Y-m-d H:i:s.u');
        $this->parameters = $parameters;
    }


    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'manifest' => $this->manifest,
            'components' => $this->components,
            'packageID' => $this->packageID,
            'status' => $this->status,
            'date_created' => self::getDate($this->date_created)
        ];
    }
}
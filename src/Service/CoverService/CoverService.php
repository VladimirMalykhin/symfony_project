<?php

namespace App\Service\CoverService;


class CoverService {

    private $epackageNumber;
    private $contentFile;
    private $coverArray;
    private $paramsImage = [
        'srcMob' => 'mobileUrl',
        'srcTablet' => 'tabletUrl',
        'srcDesktop' => 'desktopUrl'
    ];
    private $paramsText = [
        'buttonText' => 'buttonText',
        'altAttrText' => 'altAttrText'
    ];


    public function __construct(string $epackageNumber, string $contentFile)
    {
        $this->epackageNumber = $epackageNumber;
        $this->contentFile = json_decode($contentFile, true);
    }


    private function setImageParams() :void
    {
        foreach ($this->paramsImage as $key => $value)
            $this->coverArray[$key] = str_replace('../../', '/uploads/' . $this->epackageNumber . '/ru/minisite/mvideo_template/', $this->contentFile['CoverBlock']['img'][$value]);
    }


    private function setTextParams() :void
    {
        foreach ($this->paramsText as $key => $value)
            $this->coverArray[$key] = $this->contentFile['CoverBlock'][$value];
    }


    public function getCover() :array
    {
        if(isset($this->contentFile['CoverBlock'])){
            $this->setImageParams();
            $this->setTextParams();
        }
        return $this->coverArray;
    }
}
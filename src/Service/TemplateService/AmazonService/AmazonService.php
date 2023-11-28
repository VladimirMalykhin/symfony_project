<?php

namespace App\Service\TemplateService\AmazonService;

use App\Serializer\CustomSerializer;
use App\Exception\UserException;

use App\Service\Components\Amazon\StandardHeaderImageTextModule\StandardHeaderImageTextModule;
use App\Service\Components\Amazon\StandardFourImageTextModule\StandardFourImageTextModule;
use App\Service\Components\Amazon\StandardThreeImageTextModule\StandardThreeImageTextModule;
use App\Service\Components\Amazon\StandardFourImageTextQuarantModule\StandardFourImageTextQuarantModule;
use App\Service\Components\Amazon\StandardTextModule\StandardTextModule;
use App\Service\Components\Amazon\StandardProductDescriptionModule\StandardProductDescriptionModule;
use App\Service\Components\Amazon\StandardCompanyLogoModule\StandardCompanyLogoModule;
use App\Service\Components\Amazon\StandardImageTextOverlayModule\StandardImageTextOverlayModule;
use App\Service\Components\Amazon\StandardImageSidebarModule\StandardImageSidebarModule;
use App\Service\Components\Amazon\StandardSingleSideImageModule\StandardSingleSideImageModule;

use Lexik\Bundle\JWTAuthenticationBundle\Exception\InvalidPayloadException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


class AmazonService
{
    private array $items;
    private int $number;
    private string $directory;
    private string $locale_name;
    private string $epack_directory;
    private string $public_directory;

    public function __construct($items, $number, $public_directory, $epack_directory, $locale_name, $directory)
    {
        $this->items = $items;
        $this->locale_name = $locale_name;
        $this->epack_directory = $epack_directory;
        $this->number = $number;
        $this->public_directory = $public_directory;
        $this->directory = $directory;
    }


    public function parse() :void
    {

        $components = [];
        foreach($this->items as $component){
            if(isset($component['component']) && $component['component'] != ''){
                if($component['component'] == 'StandardFourImageTextModule'){
                    $module = new StandardFourImageTextModule($this->public_directory);
                }
				if($component['component'] == 'StandardFourImageTextQuarantModule'){
                    $module = new StandardFourImageTextQuarantModule($this->public_directory);
                }
				if($component['component'] == 'StandardThreeImageTextModule'){
                    $module = new StandardThreeImageTextModule($this->public_directory);
                }
				if($component['component'] == 'StandardTextModule'){
                    $module = new StandardTextModule();
                }
				if($component['component'] == 'StandardHeaderImageTextModule'){
                    $module = new StandardHeaderImageTextModule($this->public_directory);
                }
                if($component['component'] == 'StandardSingleSideImageModule'){
                    $module = new StandardSingleSideImageModule($this->public_directory);
                }
				if($component['component'] == 'StandardImageTextOverlayModule'){
                    $module = new StandardImageTextOverlayModule($this->public_directory);
                }
				if($component['component'] == 'StandardCompanyLogoModule'){
                    $module = new StandardCompanyLogoModule($this->public_directory);
                }
				if($component['component'] == 'StandardProductDescriptionModule'){
                    $module = new StandardProductDescriptionModule();
                }
				if($component['component'] == 'StandardImageSidebarModule'){
                    $module = new StandardImageSidebarModule($this->public_directory);
                }
                $components[] = $module->setData($component);
            }
        }
        $amazonContent = $this->toArray($components);
        $amazonContentStr = str_replace('\/uploads\/'.$this->number.'\/'.$this->locale_name.'\/minisite\/amazon_template\/', '../../', 
            json_encode($amazonContent));
        file_put_contents($this->epack_directory.'/'.$this->number.'/'.$this->locale_name.'/minisite/amazon_template/content/json/index.json', $amazonContentStr);
    }


    private function toArray($components)
    {
        return [
            "contentModuleList" => $components
        ];
    }




}
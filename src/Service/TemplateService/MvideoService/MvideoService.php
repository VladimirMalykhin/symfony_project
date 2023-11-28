<?php

namespace App\Service\TemplateService\MvideoService;

use Lexik\Bundle\JWTAuthenticationBundle\Exception\InvalidPayloadException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MvideoService
{
    private array $mvideo_content = [];
    private array $items;
    private string $content;
    private int $number;
    private string $directory;
    private string $locale_name;
    private string $epack_directory;
    private string $public_directory;

    public function __construct($items, $content, $number, $public_directory, $epack_directory, $locale_name, $directory)
    {
        $this->items = $items;
        $this->locale_name = $locale_name;
        $this->content = $content;
        $this->epack_directory = $epack_directory;
        $this->number = $number;
        $this->public_directory = $public_directory;
        $this->directory = $directory;
    }


    public function parse() :void
    {
        $mvideo_content = [];
        $mvideo_content['CoverBlock'] = [
            'img' => [
                'desktopUrl' => str_replace('/uploads/'.$this->number.'/'.$this->locale_name.'/minisite/mvideo_template/', '../../', $this->items['srcDesktop']),
                'tabletUrl' => str_replace('/uploads/'.$this->number.'/'.$this->locale_name.'/minisite/mvideo_template/', '../../', $this->items['srcTablet']),
                'mobileUrl' => str_replace('/uploads/'.$this->number.'/'.$this->locale_name.'/minisite/mvideo_template/', '../../', $this->items['srcMob'])
            ],
            'buttonText' => $this->items['buttonText'],
            'altAttrText' => $this->items['altAttrText']
        ];
        $mvideo_content['content'] = $this->content;
        $mvideo_content_string = json_encode($mvideo_content);
        $html_content_mvideo = str_replace('<', '&lt;', $mvideo_content_string);
        $html_content_mvideo = str_replace('>', '&gtl', $html_content_mvideo);
        file_put_contents($this->epack_directory.'/'.$this->number.'/'.$this->locale_name.'/minisite/mvideo_template/content/json/index.json', $mvideo_content_string);
        file_put_contents($this->epack_directory.'/'.$this->number.'/'.$this->locale_name.'/minisite/mvideo_template/content/iframe/index.html', $html_content_mvideo);
        file_put_contents($this->epack_directory.'/'.$this->number.'/'.$this->locale_name.'/minisite/mvideo_template/content/html/index.html', $html_content_mvideo);
    }

}
<?php

namespace App\Service\TemplateService\OzonService;

use App\Serializer\CustomSerializer;
use App\Exception\UserException;

use Lexik\Bundle\JWTAuthenticationBundle\Exception\InvalidPayloadException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


class OzonService
{
    private float $version = 0.3;
    private array $ozon_content = [];
    private int $ozon_params_flag = 0;
    // private string $ozon_params = '?session_id=44444&user_id=555555';
    private string $ozon_params = '';
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


    private function check_count($items)
    {
        if(count($items) < 2)
        {
            $response = new JsonResponse();
            $response->setContent("Count of items must be more than 1");
            $response->setStatusCode(402);
            return $response;
        } else {
            return true;
        }
    }


    private function ExtValidate($path)
    {

        if(strpos($path, 'svg') == true || strpos($path, 'webp') == true) {
            
            throw new UserException($path . ' - Check the image format. Svg and webp are not acceptable.');
        }
    }


    private function WidthValidate($url, $width)
    {
        if($width < 200) {
            
            throw new UserException($url . ' - Check the size of images. They should be at least 200x200px');
        }
    }


    private function create_img_with_fields($width, $height, $src_img_path, $path_to_save, $public_path, $number) :string
    {
        $src_img_path = str_replace('../../../', $this->epack_directory.'/'.$number.'/'.$this->locale_name.'/minisite/', $src_img_path);
        $file_info = pathinfo($src_img_path);
        $ext = $file_info['extension'];
        $mime_type = mime_content_type($path_to_save);
        if (stristr($ext, '?')) {
            $ext = stristr($ext, '?', true);
        }
        if ($ext !== 'jpg' && $ext !== 'png') {
            return $public_path;
        }
        if ($mime_type === 'image/jpeg') {
            $src_img = (imagecreatefromjpeg($src_img_path));
        } elseif ($mime_type === 'image/png') {
            $src_img = (imagecreatefrompng($src_img_path));
        }
        $image_size = getimagesize($src_img_path);
        $dest_img = imagecreatetruecolor($width, $height);
        $bg = imagecolorallocate($dest_img, 255, 255, 255);
        imagefill($dest_img, 0, 0, $bg);
        if ($ext === 'png') {
            imagecolortransparent($dest_img, $bg);
        }
        // imagejpeg();
        $src_w = $image_size[0];
        $src_h = $image_size[1];
        $dest_w = $image_size[0];
        $dest_h = $image_size[1];
        if ($width < $src_w) {
            $dest_w = $width;
            $dest_h = $width * $image_size[1] / $image_size[0];
        }
        if ($height < $src_h) {
            $dest_h = $height;
            $dest_w = $height * $image_size[0] / $image_size[1];
        }

        $src_x = 0;
        $src_y = 0;

        $dest_x = ($width - $src_w) / 2;
        $dest_y = ($height - $src_h) / 2;
        if ($width < $src_w || $height < $src_h) {
            $dest_x = 0;
            $dest_y = 0;
        }
        imagecopyresized($dest_img, $src_img, $dest_x, $dest_y, $src_x, $src_y, $dest_w, $dest_h, $src_w, $src_h);
        if ($ext === 'jpg') {
            imagejpeg($dest_img, $path_to_save);
        } elseif ($ext === 'png') {
            imagepng($dest_img, $path_to_save);
        }
        return $public_path;
    }


    private function check_ozon_params() :void
    {
        if ($this->ozon_params_flag === 0) {
            $this->ozon_params = '';
            $this->ozon_params_flag = 1;
        }
    }


    private function define_height($width, $max_width, $height)
    {
        return $height / ($width / $max_width);
    }


    private function parse_text($text, $size, $align, $color = '') :array
    {
        $block = [];
        $block['content'] = str_replace("\r\n", "<br>", $text);
        $block['content'] = str_replace("\r", "<br>", $block['content']);
        $block['content'] = str_replace("\n", "<br>", $block['content']);
        $block['content'] = explode("<br>", $block['content']);
        $block['size'] = "size" . $size;
        $block['align'] = $align;
		if($color != '') $block['color'] = $color;
        return $block;
    }

    private function parse_img($col_item, $max_width, $max_width_mob, $is_billboard=false)
    {
        $block = [];
        $image = $col_item['props']['src'];
        if(file_exists($this->public_directory.$image)){
            $this->ExtValidate($image);
            if(strpos($image, 'svg') != true) {
                $img_size = getimagesize($this->public_directory.$image);
                $this->WidthValidate($image, $img_size[0]);
				$this->WidthValidate($image, $img_size[1]);
                if ($img_size[0] > $max_width && $is_billboard === false) {
                    $block['width'] = $max_width;
                    $block['height'] = ceil($this->define_height($img_size[0], $max_width, $img_size[1]));
                } elseif ($img_size[0] < $max_width && $is_billboard === false) {
                    $block['width'] = $max_width;
                    $block['height'] = $img_size[1];
                    $image = $this->create_img_with_fields($max_width, $img_size[1], $this->public_directory.$image, $this->public_directory.$image, $image, $image);
                } elseif($is_billboard == true) {
                    if($img_size[0] >= 1416){
                        $block['width'] = 1416;
                        $block['height'] = ceil($this->define_height($img_size[0], 1416, $img_size[1]));
                    } else {
                        $block['width'] = 1416;
                        $block['height'] = $img_size[1];
                        $image = $this->create_img_with_fields(1416, $img_size[1], $this->public_directory.$image, $this->public_directory.$image, $image, $image);
                    }
                } else {
                    $block['width'] = $img_size[0];
                    $block['height'] = $img_size[1];
                }
            }
        }
        $block['src'] = $image ;
        if(isset($col_item['props']['srcMob']) && $col_item['props']['srcMob'] != ''){
            $block['srcMobile'] = $col_item['props']['srcMob'];
        } else {
            $block['srcMobile'] = $image;
        }
        if(file_exists($this->public_directory.$block['srcMobile']) && !strpos($image, 'svg')){
			$this->ExtValidate($block['srcMobile']);
            $new_sizes = getimagesize($this->public_directory.$block['srcMobile']);
			$this->WidthValidate($block['srcMobile'], $new_sizes[0]);
			$this->WidthValidate($block['srcMobile'], $new_sizes[1]);
            if($new_sizes[0] > $max_width_mob)
            {
                $block['widthMobile'] = $max_width_mob;
                $block['heightMobile'] = ceil($this->define_height($new_sizes[0], $max_width_mob, $new_sizes[1]));
            } else {
                $block['widthMobile'] = $new_sizes[0];
                $block['heightMobile'] = $new_sizes[1];
            }

        } else {
            $block['widthMobile'] = $block['width'] ?? 650;
            $block['heightMobile'] = $block['height'] ?? 650;
        }
        $block['src'] = str_replace('/uploads/'.$this->number.'/'.$this->locale_name.'/minisite/ozon_template/', '../../', $block['src']);
        $block['srcMobile'] = str_replace('/uploads/'.$this->number.'/'.$this->locale_name.'/minisite/ozon_template/', '../../', $block['srcMobile']);
        $block['src'] .= $this->ozon_params;
        $block['srcMobile'] .= $this->ozon_params;
		$block['isParandja'] = false;
		$block['isParandjaMobile'] = false;
        $this->check_ozon_params();
        return $block;
    }


    public function parse() :void
    {
        $ozon_content_inner = [];
        foreach($this->items as $component){
            if(isset($component['component']) && $component['component'] != ''){
                $component_path = $this->directory.'/templates/collections/'.str_replace('Ozon', '', $component['component']).'/'.$component['component'].'.php';
                if(file_exists($component_path)){
                    include $component_path;
                }
            }
        }
        $this->ozon_content['version'] = $this->version;
        $this->ozon_content['content'] = $ozon_content_inner;
        $ozon_content_string = str_replace('/uploads/'.$this->number.'/'.$this->locale_name.'/minisite/ozon_template/', '../../', json_encode($this->ozon_content));
        file_put_contents($this->epack_directory.'/'.$this->number.'/'.$this->locale_name.'/minisite/ozon_template/content/json/index.json', $ozon_content_string);
    }

}
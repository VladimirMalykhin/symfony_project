<?php

declare(strict_types = 1);

namespace App\Service\MediaImageService;

class MediaImageService
{
	public string $path;
	
	public function __construct($pathImg)
	{
		$this->path = $pathImg;
	}
	
	
	public function isResizing()
	{
		$sizes = getimagesize($this->path);
		if($sizes[0] > 1418){
			$heightNew = $this->defineHeight($sizes[0], 1418, $sizes[1]);
			$this->resize(1418, $heightNew);
		}
	}
	
	
	private function defineHeight($width, $widthMax, $height)
    {
        return ceil($height / ($width / $widthMax));
    }
	
	
	private function resize($width, $height)
    {
        
        $file_info = pathinfo($this->path);
        $ext = $file_info['extension'];
        $mime_type = mime_content_type($this->path);
        if (stristr($ext, '?')) {
            $ext = stristr($ext, '?', true);
        }
        if ($ext !== 'jpg' && $ext !== 'png') {
            return $public_path;
        }
        if ($mime_type === 'image/jpeg') {
            $src_img = (imagecreatefromjpeg($this->path));
        } elseif ($mime_type === 'image/png') {
            $src_img = (imagecreatefrompng($this->path));
        }
        $image_size = getimagesize($this->path);
        $dest_img = imagecreatetruecolor($width, (int)$height);
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
        imagecopyresized($dest_img, $src_img, $dest_x, $dest_y, $src_x, $src_y, (int)$dest_w, (int)$dest_h, $src_w, $src_h);
        if ($ext === 'jpg') {
            imagejpeg($dest_img, $this->path);
        } elseif ($ext === 'png') {
            imagepng($dest_img, $this->path);
        }
    }

	
}
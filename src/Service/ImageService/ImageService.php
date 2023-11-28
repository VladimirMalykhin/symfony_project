<?php

namespace App\Service\ImageService;

class ImageService
{

	
	public function toArray($imageSrc) : array
	{
		$imageSizes = getimagesize($imageSrc);
		return ['size' => ['width' => ['value' => $imageSizes[0], 'units' => 'pixels'], 'height' => ['value' => $imageSizes[1], 'units' => 'pixels']]];
	}
}
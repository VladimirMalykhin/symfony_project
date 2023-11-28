<?php

namespace App\Service\Components\Amazon\StandardImageTextOverlayModule;

use App\Exception\UserException;
class StandardImageTextOverlayModule
{
	private $contentModuleType = "STANDARD_IMAGE_TEXT_OVERLAY";
	private $standardCompanyLogo = null;
	private $standardComparisonTable = null;
	private $data = [];
	private $directory;


	public function __construct($directory)
	{
		$this->directory = $directory;
	}

	public function setData($component)
	{
		$this->data['overlayColorType'] = strtoupper($component['props']['overlayColorType']);
		$this->data['block']['headline']['value'] = $component['props']['headlineText'];
		$this->data['block']['headline']['decoratorSet'] = [];
		$textNew['value'] = $component['props']['bodyText'];
		$textNew['decoratorSet'] = [];
		$this->data['block']['body']['textList'][] = $textNew;
		$this->data['block']['image']['uploadDestinationId'] = $component['props']['mainImage'];
		$this->data['block']['image']['altText'] = $component['props']['mainImageAltText'];
		$imageSizes = getimagesize($this->directory.$component['props']['mainImage']);
		$this->data['block']['image']['imageCropSpecification']['size']['width']['value']= $imageSizes[0];
			$this->data['block']['image']['imageCropSpecification']['size']['width']['units']= 'pixels';
			$this->data['block']['image']['imageCropSpecification']['size']['height']['value']= $imageSizes[1];
			$this->data['block']['image']['imageCropSpecification']['size']['height']['units']= 'pixels';
		return $this->toArray();
	}


	private function toArray()
	{
		return [
			"contentModuleType" => $this->contentModuleType,
			"standardCompanyLogo" => $this->standardCompanyLogo,
			"standardComparisonTable" => $this->standardComparisonTable,
			"standardImageTextOverlay" => $this->data
		];
	}
}
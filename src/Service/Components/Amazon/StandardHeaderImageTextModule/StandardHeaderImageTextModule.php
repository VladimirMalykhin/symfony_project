<?php

namespace App\Service\Components\Amazon\StandardHeaderImageTextModule;


class StandardHeaderImageTextModule
{
	private $contentModuleType = "STANDARD_HEADER_IMAGE_TEXT";
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
		if($component['props']['mainHeadlineText'] != ""){
			$this->data['headline']['value'] = $component['props']['mainHeadlineText'];
			$this->data['headline']['decoratorSet'] = [];
		}
		
		if($component['props']['mainSubHeadlineText'] != ""){
			$this->data['block']['headline']['value'] = $component['props']['mainSubHeadlineText'];
			$this->data['block']['headline']['decoratorSet'] = [];
		}
		
		if($component['props']['mainBodyText'] != ""){
			$textNew['value'] = $component['props']['mainBodyText'];
			$textNew['decoratorSet'] = [];
			$this->data['block']['body']['textList'][] = $textNew;
		}
		
		$this->data['block']['image']['altText'] = $component['props']['mainImageAltText'];
		$this->data['block']['image']['uploadDestinationId'] = $component['props']['mainImage'];
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
			"standardHeaderImageText" => $this->data
		];
	}
}
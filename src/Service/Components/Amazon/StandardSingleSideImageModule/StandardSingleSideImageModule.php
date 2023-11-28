<?php

namespace App\Service\Components\Amazon\StandardSingleSideImageModule;


class StandardSingleSideImageModule
{
	private $contentModuleType = "STANDARD_SINGLE_SIDE_IMAGE";
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
		$this->data['imagePositionType'] = strtoupper($component['props']['imagePosition']);
		$this->data['block']['headline']['value'] = $component['props']['mainHeadlineText'];
		$this->data['block']['headline']['decoratorSet'] = [];
		$textNew['value'] = $component['props']['mainBodyText'];
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
			"standardSingleSideImage" => $this->data
		];
	}
}
<?php

namespace App\Service\Components\Amazon\StandardImageSidebarModule;


class StandardImageSidebarModule
{
	private $contentModuleType = "STANDARD_IMAGE_SIDEBAR";
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
		$this->data['headline']['value'] = $component['props']['mainHeadlineText'];
		$this->data['headline']['decoratorSet'] = [];
		$this->data['descriptionTextBlock']['headline']['value'] = $component['props']['mainSubHeadlineText'];
		$this->data['descriptionTextBlock']['headline']['decoratorSet'] = [];
		$textNew['value'] = $component['props']['mainBodyText'];
		$textNew['decoratorSet'] = [];
		$this->data['descriptionTextBlock']['body']['textList'][] = $textNew;
		$bulletNew['position'] = 1;
		$bulletNew['text']['value'] = $component['props']['mainBulletPoint'];
		$bulletNew['text']['decoratorSet'] = [];
		$this->data['descriptionListBlock']['textList'][] = $bulletNew;
		$bulletNew['position'] = 1;
		$bulletNew['text']['value'] = $component['props']['sideBulletPoint'];
		$bulletNew['text']['decoratorSet'] = [];
		$this->data['sidebarListBlock']['textList'][] = $bulletNew;
		$this->data['imageCaptionBlock']['image']['uploadDestinationId'] = $component['props']['mainImage'];
		$this->data['imageCaptionBlock']['image']['altText'] = $component['props']['mainImageAltText'];
		$imageSizes = getimagesize($this->directory.$component['props']['mainImage']);
		$this->data['imageCaptionBlock']['image']['imageCropSpecification']['size']['width']['value']= $imageSizes[0];
			$this->data['imageCaptionBlock']['image']['imageCropSpecification']['size']['width']['units']= 'pixels';
			$this->data['imageCaptionBlock']['image']['imageCropSpecification']['size']['height']['value']= $imageSizes[1];
			$this->data['imageCaptionBlock']['image']['imageCropSpecification']['size']['height']['units']= 'pixels';
		
		$this->data['sidebarImageTextBlock']['headline']['value'] = $component['props']['sidebarHeadlineText'];
		$this->data['sidebarImageTextBlock']['headline']['decoratorSet'] = [];
		$textNew2['value'] = $component['props']['sidebarBodyText'];
		$textNew2['decoratorSet'] = [];
		$this->data['sidebarImageTextBlock']['body']['textList'][] = $textNew2;
		$this->data['sidebarImageTextBlock']['image']['uploadDestinationId'] = $component['props']['sidebarImage'];
		$this->data['sidebarImageTextBlock']['image']['altText'] = $component['props']['sidebarImageAltText'];
		$imageSizes = getimagesize($this->directory.$component['props']['sidebarImage']);
		$this->data['sidebarImageTextBlock']['image']['imageCropSpecification']['size']['width']['value']= $imageSizes[0];
			$this->data['sidebarImageTextBlock']['image']['imageCropSpecification']['size']['width']['units']= 'pixels';
			$this->data['sidebarImageTextBlock']['image']['imageCropSpecification']['size']['height']['value']= $imageSizes[1];
			$this->data['sidebarImageTextBlock']['image']['imageCropSpecification']['size']['height']['units']= 'pixels';
		return $this->toArray();
	}


	private function toArray()
	{
		return [
			"contentModuleType" => $this->contentModuleType,
			"standardCompanyLogo" => $this->standardCompanyLogo,
			"standardComparisonTable" => $this->standardComparisonTable,
			"standardImageSidebar" => $this->data
		];
	}
}
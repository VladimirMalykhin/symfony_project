<?php

namespace App\Service\Components\Amazon\StandardCompanyLogoModule;


class StandardCompanyLogoModule
{
	private $contentModuleType = "STANDARD_COMPANY_LOGO";
	private $data = [];
	private $directory;


	public function __construct($directory)
	{
		$this->directory = $directory;
	}

	public function setData($component)
	{
		$this->data['companyLogo']['uploadDestinationId'] = $component['props']['mainImage'];
		$this->data['companyLogo']['altText'] = $component['props']['mainImageAltText'];
		$imageSizes = getimagesize($this->directory.$component['props']['mainImage']);
		$this->data['companyLogo']['imageCropSpecification']['size']['width']['value']= $imageSizes[0];
			$this->data['companyLogo']['imageCropSpecification']['size']['width']['units']= 'pixels';
			$this->data['companyLogo']['imageCropSpecification']['size']['height']['value']= $imageSizes[1];
			$this->data['companyLogo']['imageCropSpecification']['size']['height']['units']= 'pixels';
		return $this->toArray();
	}


	private function toArray()
	{
		return [
			"contentModuleType" => $this->contentModuleType,
			"standardCompanyLogo" => $this->data
		];
	}
}
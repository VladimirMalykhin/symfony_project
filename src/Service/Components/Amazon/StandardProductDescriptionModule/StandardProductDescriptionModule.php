<?php

namespace App\Service\Components\Amazon\StandardProductDescriptionModule;

use App\Exception\UserException;
class StandardProductDescriptionModule
{
	private $contentModuleType = "STANDARD_PRODUCT_DESCRIPTION";
	private $standardCompanyLogo = null;
	private $standardComparisonTable = null;
	private $data = [];


	public function setData($component)
	{
		$textNew['value'] = $component['props']['bodyText'];
		$textNew['decoratorSet'] = [];
		$this->data['body']['textList'][] = $textNew;
		return $this->toArray();
	}


	private function toArray()
	{
		return [
			"contentModuleType" => $this->contentModuleType,
			"standardCompanyLogo" => $this->standardCompanyLogo,
			"standardComparisonTable" => $this->standardComparisonTable,
			"standardProductDescription" => $this->data
		];
	}
}
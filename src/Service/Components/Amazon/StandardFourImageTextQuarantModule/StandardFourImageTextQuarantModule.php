<?php

namespace App\Service\Components\Amazon\StandardFourImageTextQuarantModule;

use App\Exception\UserException;
class StandardFourImageTextQuarantModule
{
	private $contentModuleType = "STANDARD_FOUR_TEXT_QUARANT";
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
		
		$this->setBlocks($component['props']['innerElements']);
		return $this->toArray();
	}


	private function setBlocks($blocks)
	{

		for($i = 0; $i < count($blocks); $i++)
		{
			$blockNumber = $i + 1;
			$this->data['block'.$blockNumber]['headline']['value'] = $blocks[$i]['props']['blockHeadline'];
			$this->data['block'.$blockNumber]['headline']['decoratorSet'] = [];
			$textNew['value'] = $blocks[$i]['props']['blockBodyText'];
			$textNew['decoratorSet'] = [];
			$this->data['block'.$blockNumber]['body']['textList'][] = $textNew;
			$this->data['block'.$blockNumber]['image']['uploadDestinationId'] = $blocks[$i]['props']['blockImage'];
			$this->data['block'.$blockNumber]['image']['altText'] = 'Image '.$blockNumber;
			$imageSizes = getimagesize($this->directory.$blocks[$i]['props']['blockImage']);
			$this->data['block'.$blockNumber]['image']['imageCropSpecification']['size']['width']['value']= $imageSizes[0];
			$this->data['block'.$blockNumber]['image']['imageCropSpecification']['size']['width']['units']= 'pixels';
			$this->data['block'.$blockNumber]['image']['imageCropSpecification']['size']['height']['value']= $imageSizes[1];
			$this->data['block'.$blockNumber]['image']['imageCropSpecification']['size']['height']['units']= 'pixels';
		}
	}


	private function toArray()
	{
		return [
			"contentModuleType" => $this->contentModuleType,
			"standardCompanyLogo" => $this->standardCompanyLogo,
			"standardComparisonTable" => $this->standardComparisonTable,
			"standardFourImageTextQuarant" => $this->data
		];
	}
}
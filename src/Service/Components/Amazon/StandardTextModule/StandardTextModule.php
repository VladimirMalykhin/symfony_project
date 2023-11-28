<?php

namespace App\Service\Components\Amazon\StandardTextModule;

use App\Exception\UserException;
class StandardTextModule
{
	private $contentModuleType = "STANDARD_TEXT";
	private $data = [];


	public function setData($component)
	{
		$this->data['headline']['value'] = $component['props']['headlineText'];
		$this->data['headline']['decoratorSet'] = [];
		$textNew['value'] = $component['props']['bodyText'];
		$textNew['decoratorSet'] = [];
		$this->data['body']['textList'][] = $textNew;
		return $this->toArray();
	}


	private function toArray()
	{
		return [
			"contentModuleType" => $this->contentModuleType,
			"standardText" => $this->data
		];
	}
}
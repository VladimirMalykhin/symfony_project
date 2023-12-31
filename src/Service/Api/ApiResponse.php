<?php

declare(strict_types=1);

namespace App\Service\Api;

class ApiResponse
{
    private $data;

    public function __construct(
        $data

    ) {
        $this->data = $data;
    }


    public function getData()
    {
        return $this->data;
    }

    public function setData($data): self
    {
        $this->data = $data;
        return $this;
    }



    public function toArray(): array
    {
        return [
            'data' => $this->getData()
        ];
    }
}

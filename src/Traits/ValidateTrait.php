<?php

namespace App\Traits;

use Cassandra\Exception\ValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

trait ValidateTrait
{
    /**
    * @var ValidatorInterface
    */
    private ValidatorInterface $domainValidator;

    /**
    * @required
    */
    public function setDomainValidator(ValidatorInterface $domainValidator): void
    {
        $this->domainValidator = $domainValidator;
    }

    /**
    * @throws ValidationException
    */
    protected function validate($data): void
    {
        $validationErrors = $this->domainValidator->validate($data);
        $violations = [];

        foreach ($validationErrors as $error) {
            $property = $error->getPropertyPath();

            if (isset($property[$property])) {
            continue;
            }

            $violations[$error->getPropertyPath()] = $error->getMessage();
        }

        if ($violations) {
            throw new ValidationException(join(PHP_EOL, $violations));
        }
    }
}
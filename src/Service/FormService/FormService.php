<?php

namespace App\Service\FormService;

use App\Exception\BadRequestException;
use App\Exception\ValidationException;

use App\Service\Form\ImageType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Form\Extension\Core\Type\FormType;

class FormService
{
    private $requestStack;
    private $formFactory;

    public function __construct(RequestStack $requestStack, FormFactoryInterface $formFactory)
    {
        $this->requestStack = $requestStack;
        $this->formFactory = $formFactory;
    }

    /**
     * @throws ValidationException
     * @throws BadRequestException
     */
    public function handleForm($formClass): array
    {
        $form = $this->formFactory->createBuilder(FormType::class)
        ->add('file', ImageType::class)
        ->getForm();
        $form->handleRequest($this->requestStack->getCurrentRequest());

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                $this->throwValidationException($form);
            }

            return $form->getData();
        } else {
            throw new BadRequestException('Form is not submitted', 400);
        }


    }

    /**
     * @throws ValidationException
     */
    public function throwValidationException(FormInterface $form): void
    {
        $errors = $this->convertFormToArray($form);

        if ($errors) {
            throw new ValidationException(join(PHP_EOL, $errors));
        }
    }

    public function convertFormToArray(FormInterface $data): ?array
    {
        $form = $errors = [];

        foreach ($data->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }

        if ($errors) {
            $form['errors'] = $errors;
        }

        foreach ($data->all() as $child) {
            if ($child instanceof FormInterface) {
                $errorsArray = $this->convertFormToArray($child);
                if ($errorsArray) {
                    $form['errors'][] = join(' ', $errorsArray);
                }
            }
        }

        return $form['errors'] ?? null;
    }
}

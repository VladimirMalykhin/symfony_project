<?php
namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event)
    {
        // You get the exception object from the received event
        $exception = $event->getThrowable();
        $message = ['data' => ['code' => 402, 'message' => $exception->getMessage()]];

        // Customize your response object to display the exception details
		$parameters = explode('-', $exception->getMessage());
		if(count($parameters) > 1){
			$response = new JsonResponse(['data' => ['code' => 402, 'url' => $parameters[0], 'message' => $parameters[1]]]);
		} else {
			$response = new JsonResponse(['data' => ['code' => 402, 'message' => 'Something wrong Please try later']]);
		}
        
        $response->setStatusCode(402);

        // HttpExceptionInterface is a special type of exception that
        // holds status code and header details

        // sends the modified response object to the event
        $event->setResponse($response);
    }
}
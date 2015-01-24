<?php
namespace AppBundle\Controller\Traits;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ApiHandlerTrait
{
    /**
     * @param $data
     *
     * @return JsonResponse
     */
    protected function handleView($data)
    {
        if ($this->get('request')->query->has('callback')) {
            return $this->transformToJsonp($data);
        } else {
            return new JsonResponse($data);
        }
    }

    /**
     * @param $message
     * @param int $code
     *
     * @return JsonResponse
     */
    protected function handleError($message, $code = 400)
    {
        $response = $this->handleView([
            'status'  => 'failed',
            'message' => $message
        ]);

        $response->setStatusCode($code);

        return $response;
    }

    /**
     * @param $data
     *
     * @return Response
     */
    protected function transformToJsonp($data)
    {
        $callback = $this->get('request')->get('callback');
        $result = sprintf("$callback(%s);", json_encode($data));

        return new Response($result);
    }
}
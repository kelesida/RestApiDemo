<?php

namespace App\Controller;

use App\Manager\RestApiManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class RestApiController extends AbstractController
{
    /**
     * @var RestApiManager
     */
    private $manager;

    public function __construct(RestApiManager $restApiManager)
    {
        $this->manager = $restApiManager;
    }

    /**
     * Endpoint бронирование стола
     *
     * @Route("/api/v2/reservation", name="rest_api_reservation", methods={"POST"})
     * @return JsonResponse
     */
    public function reservation()
    {
        try {
            $data = $this->manager->reservation();
        } catch (Exception $e) {
            $data = [
                'code' => $e->getCode(),
                'error' => $e->getMessage()
            ];
        }

        return $this->json(
            $data,
            $data['code']
        );
    }
}

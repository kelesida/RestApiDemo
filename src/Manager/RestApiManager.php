<?php

namespace App\Manager;

use App\Exception\ExceptionService;
use App\Service\ReservationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

class RestApiManager
{
    /**
     * @var ReservationService
     */
    private $reservation;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->reservation = new ReservationService($entityManager);
    }

    public function reservation(): array
    {
        $request = Request::createFromGlobals();
        $postData = $request->request->all();

        $validator = Validation::createValidator();
        $constraint = new Assert\Collection([
            'date' => [
                new Assert\NotBlank(),
                new Assert\Regex("/^[0-9]{2}\.[0-9]{2}\.[0-9]{4}$/"),
            ],
            'from' => [
                new Assert\NotBlank(),
                new Assert\Regex("/^[0-9]{2}\:[0-9]{2}$/"),
            ],
            'to' => [
                new Assert\NotBlank(),
                new Assert\Regex("/^[0-9]{2}\:[0-9]{2}$/"),
            ],
            'table_id' => [
                new Assert\Regex("/^\d+$/")
            ],
        ]);

        $violations = $validator->validate($postData, $constraint);
        if ($violations->count()) {
            throw ExceptionService::exceptionValidation($violations);
        }

        $this->reservation->checkingTimeRange($postData['from'], $postData['to']);

        $tableId = (int) $postData['table_id'];

        if ($tableId) {
            $this->reservation->checkingTableIsBusy(
                $postData['date'],
                $postData['from'],
                $postData['to'],
                $postData['table_id']
            );
        } else {
            $tableId = $this->reservation->getNotBusyTable(
                $postData['date'],
                $postData['from'],
                $postData['to']
            );
        }

        return [
            'cost' => $this->reservation->make(
                $postData['date'],
                $postData['from'],
                $postData['to'],
                $tableId
            ),
            'table_id' => $tableId,
            'code' => 200
        ];
    }
}

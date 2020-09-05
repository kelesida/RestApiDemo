<?php

namespace App\Service;

use App\Entity\Reservation;
use App\Entity\Table;
use App\Exception\ExceptionService;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class ReservationService
{
    const MIN_TIME = 30;
    const COST_1_HOUR = 300;

    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    /**
     * 3. Происходит проверка, что время бронирования не менее минимально возможного времени бронирования.
     * Если нет – ошибка.
     *
     * @param $timeFrom
     * @param $timeTo
     * @throws ExceptionService
     */
    public function checkingTimeRange($timeFrom, $timeTo)
    {

        if ($this->getRangeTimeInMinutes($timeFrom, $timeTo) < self::MIN_TIME) {
            throw ExceptionService::exceptionTimeIsSmallToo();
        }
    }

    private function getRangeTimeInMinutes($timeFrom, $timeTo)
    {
        $timeFrom = new DateTime($timeFrom);
        $hoursFrom = (int) $timeFrom->format('H');
        $minutesFrom = (int) $timeFrom->format('i');
        $timeTo = new DateTime($timeTo);
        $hoursTo = (int) $timeTo->format('H');
        $minutesTo = (int) $timeTo->format('i');

        return ($hoursTo - $hoursFrom) * 60 + $minutesTo - $minutesFrom;
    }

    /**
     * 4. Если получен table_id, то необходимо проверить, что данный стол свободен в выбранное время.
     * Если стол занят – ошибка.
     *
     * @param $date
     * @param $from
     * @param $to
     * @param $tableId
     * @throws ExceptionService
     */
    public function checkingTableIsBusy($date, $from, $to, $tableId)
    {
        $items = $this->entityManager
            ->getRepository(Reservation::class)
            ->findBusyByTableId($date, $from, $to, $tableId);

        if (count($items)) {
            throw ExceptionService::exceptionReservationIsBusy();
        }
    }

    /**
     * 5. Если table_id не получен, то необходимо проверить, есть ли хоть один свободный стол в выбранное время.
     * Если нет – ошибка.
     *
     * @param $date
     * @param $from
     * @param $to
     * @return mixed
     * @throws ExceptionService
     */
    public function getNotBusyTable($date, $from, $to)
    {
        $tableId = $this->entityManager
            ->getRepository(Reservation::class)
            ->getNotBusyTableId($date, $from, $to);

        if (is_null($tableId)) {
            throw ExceptionService::exceptionReservationIsBusy();
        }

        return $tableId;
    }

    /**
     * 6. Если ошибки нет, то необходимо забронировать стол на выбранное время, рассчитать стоимость аренды
     * и вернуть успешный ответ с указанием стоимости аренды
     *
     * @param $date
     * @param $from
     * @param $to
     * @param int $tableId
     * @return int
     * @throws ExceptionService
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function make($date, $from, $to, int $tableId)
    {
        // проверка на существование Table с данным tableId
        $table = $this->entityManager->getRepository(Table::class)->find($tableId);

        if (!$table) {
            throw ExceptionService::exceptionNotFoundEntity();
        }

        $minutes = $this->getRangeTimeInMinutes($from, $to);
        $hours = (int) round($minutes / 60);
        $hours = (($hours * 60) < $minutes) ? $hours + 1 : $hours;
        $cost = $hours * self::COST_1_HOUR;

        $reservation = new Reservation();
        $reservation->setCreatedAt(new \DateTime('now'));
        $reservation->setDate(new \DateTime($date));
        $reservation->setTimeFrom(new \DateTime($from));
        $reservation->setTimeTo(new \DateTime($to));
        $reservation->setTableId($table);
        $this->entityManager->persist($reservation);
        $this->entityManager->flush();

        unset($reservation);

        return $cost;
    }
}

<?php

namespace App\Repository;

use App\Entity\Reservation;
use App\Entity\Table;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Reservation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reservation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reservation[]    findAll()
 * @method Reservation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    /**
     * @param $date
     * @param $from
     * @param $to
     * @param $tableId
     * @return int|mixed|string
     * @throws \Exception
     */
    public function findBusyByTableId($date, $from, $to, $tableId)
    {
        $qb = $this->createQueryBuilder('n');
        $qb
            ->select('r')
            ->from(Reservation::class, 'r')
            ->where('r.tableId = :table_id')
            ->andWhere('r.date = :date')
            ->andWhere($qb->expr()->orX(
                'r.timeFrom BETWEEN :from AND :to',
                'r.timeTo BETWEEN :from AND :to'
            ));

        return $this->getEntityManager()->createQuery($qb)
            ->setMaxResults(1) // если есть хотя бы 1 строка
            ->setParameters([
                'table_id' => $tableId,
                'date' => new \DateTime($date),
                'from' => $from,
                'to' => $to
            ])->getResult();
    }

    /**
     * @param $date
     * @param $from
     * @param $to
     * @return int|null
     * @throws \Exception
     */
    public function getNotBusyTableId($date, $from, $to)
    {
        $qb2  = $this->getEntityManager()->createQueryBuilder();
        $qb2->select('IDENTITY(r.tableId)')
            ->from(Reservation::class, 'r')
            ->where('r.date = :date')
            ->andWhere($qb2->expr()->orX(
                'r.timeFrom BETWEEN :from AND :to',
                'r.timeTo BETWEEN :from AND :to'
            ));

        $qb  = $this->getEntityManager()->createQueryBuilder();
        $qb->select('t.id')
            ->from(Table::class, 't')
            ->where(
                $qb->expr()->notIn(
                't.id',
                    $qb2->getDQL()
            ))
            ->setParameters([
                'date' => new \DateTime($date),
                'from' => $from,
                'to' => $to
            ])
            ->setMaxResults(1)
        ;

        $items = $qb->getQuery()->getResult();

        return (0 === count($items))? null : $items[0]['id'];
    }
}

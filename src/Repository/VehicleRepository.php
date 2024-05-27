<?php

namespace App\Repository;

use App\Entity\Vehicle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vehicle>
 */
class VehicleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vehicle::class);
    }
    public function findAllModelsAndCounts()
    {
        return $this->createQueryBuilder('v')
            ->select('v.model, COUNT(v.id) AS modelCount')
            ->groupBy('v.model')
            ->getQuery()
            ->getResult();
    }


}

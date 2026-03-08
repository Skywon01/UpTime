<?php

namespace App\Repository;

use App\Entity\Part;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Part>
 */
class PartRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Part::class);
    }

    public function findLowStockParts(int $threshold = 5): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.stockQuantity <= :seuil')
            ->setParameter('seuil', $threshold)
            ->leftJoin('p.supplier', 's') // On joint le fournisseur pour éviter des requêtes SQL en boucle
            ->addSelect('s')
            ->orderBy('p.stockQuantity', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findLowStockPartsByCompany($company, int $limit = 5): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.company = :company')
            ->andWhere('p.stockQuantity <= 5')
            ->setParameter('company', $company)
            ->leftJoin('p.supplier', 's') // Optionnel : pour afficher le nom du fournisseur sans ramer
            ->addSelect('s')
            ->orderBy('p.stockQuantity', 'ASC') // <--- C'ÉTAIT ICI ! (p.stock -> p.stockQuantity)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

}

<?php declare(strict_types = 1);

/**
 * This file is part of the iot-backend project.
 * Copyright (c) 2025 Slavomír Švigar <slavo.svigar@gmail.com>
 */

namespace App\Model\Services;

use App\Model\Entity\Temperature;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\OptimisticLockException;

final class TemperatureRepository extends EntityRepository {

    /**
     * @var EntityManager
     */
    private EntityManager $em;

    /**
     * PropertyRepository constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager){
        parent::__construct ($entityManager, new ClassMetadata(Temperature::class));

        $this->em = $entityManager;
    }

	/**
	 * @param int $id
	 * @return Temperature|null
     * @throws \Doctrine\ORM\ORMException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 * @throws \Doctrine\ORM\TransactionRequiredException
	 */
    public function getById(int $id) : ?Temperature {
    	$user = $this->em->find(Temperature::class, $id);

    	if($user instanceof Temperature) {
    		return $user;
		}
		return null;
	}

    /**
     * @param array $criteria
     * @param $orderBy
     * @param $limit
     * @param $offset
     * @return mixed
     */
    public function findTemperatureBy(array $criteria = array(), $orderBy = array(), $limit = NULL, $offset = NULL)
    {
        return $this->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findOneTemperatureBy(array $criteria = array(), array $orderBy = array())
    {
        return $this->findOneBy($criteria, $orderBy);
    }

    /**
     * @param $temperatureValue
     * @return Temperature
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function create($temperatureValue) {

        $temperature = new Temperature;
        $temperature->setTemperature($temperatureValue);
        $temperature->setDate(new DateTime());

        $this->em->persist($temperature);
        $this->em->flush();

        return $temperature;
    }

    public function findFromDate(DateTime $date): array
    {
        $qb = $this->em->createQueryBuilder();

        $qb->select('w');
        $prepareStatement = $qb->from('App\Model\Entity\Temperature', 'w');

        return $prepareStatement
            ->where('w.date >= :searchDate')
            ->setParameter('searchDate', $date)
            ->getQuery()
            ->getResult();

    }

    public function findBetweenDates(\DateTime $startDate, \DateTime $endDate): array
    {
        $qb = $this->createQueryBuilder('t');

        return $qb->where('t.date >= :startDate')
            ->andWhere('t.date <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('t.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function deleteOlderThan(DateTime $date): int
    {
        $qb = $this->createQueryBuilder('t');

        $qb->delete()
            ->where('t.date < :date')
            ->setParameter('date', $date);

        return $qb->getQuery()->execute();
    }
}

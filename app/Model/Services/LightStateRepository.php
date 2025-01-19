<?php declare(strict_types = 1);

/**
 * This file is part of the iot-backend project.
 * Copyright (c) 2025 Slavomír Švigar <slavo.svigar@gmail.com>
 */

namespace App\Model\Services;

use App\Model\Entity\LightState;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\OptimisticLockException;

final class LightStateRepository extends EntityRepository {

    /**
     * @var EntityManager
     */
    private EntityManager $em;

    /**
     * PropertyRepository constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager){
        parent::__construct ($entityManager, new ClassMetadata(LightState::class));

        $this->em = $entityManager;
    }

	/**
	 * @param int $id
	 * @return LightState|null
     * @throws \Doctrine\ORM\ORMException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 * @throws \Doctrine\ORM\TransactionRequiredException
	 */
    public function getById(int $id) : ?LightState {
    	$user = $this->em->find(LightState::class, $id);

    	if($user instanceof LightState) {
    		return $user;
		}
		return null;
	}

    /**
     * @param array $criteria
     * @param $orderBy
     * @param $limit
     * @param $offset
     * @return LightState[]
     */
    public function findLightStateBy(array $criteria = array(), $orderBy = array(), $limit = NULL, $offset = NULL)
    {
        return $this->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findOneLightStateBy(array $criteria = array(), array $orderBy = array())
    {
        return $this->findOneBy($criteria, $orderBy);
    }

    /**
     * @param $lightStateValue
     * @return LightState
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function create($lightStateValue) {

        $lightState = new LightState;
        $lightState->setState($lightStateValue);
        $lightState->setDate(new DateTime());

        $this->em->persist($lightState);
        $this->em->flush();

        return $lightState;
    }

    public function findFromDate(DateTime $date): array
    {
        $qb = $this->em->createQueryBuilder();

        $qb->select('w');
        $prepareStatement = $qb->from('App\Model\Entity\LightState', 'w');

        return $prepareStatement
            ->where('w.date >= :searchDate')
            ->setParameter('searchDate', $date)
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

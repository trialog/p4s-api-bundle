<?php
namespace Amisure\P4SApiBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

class EventRepository extends EntityRepository
{

	public function findByBeneficiary($beneficiaryId, $inTheFuture = true)
	{
		$qry = $this->createQueryBuilder('e')
			->select('ev')
			->from($this->_entityName, 'ev')
			->leftJoin('ev.participants', 'u')
			->where('u.id = :beneficiaryId' . ($inTheFuture ? ' AND ev.dateEnd >= :now ' : ''))
			->setParameter('beneficiaryId', $beneficiaryId)
			->orderBy('ev.dateStart', 'DESC');
		if ($inTheFuture) {
			$qry->setParameter('now', new \DateTime());
		}
		$q = $qry->getQuery();
		try {
			$events = $q->getResult();
		} catch (NoResultException $e) {
			return null;
		}
		return $events;
	}
}
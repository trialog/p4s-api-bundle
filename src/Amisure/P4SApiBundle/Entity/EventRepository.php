<?php
namespace Amisure\P4SApiBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

class EventRepository extends EntityRepository
{

	public function findByBeneficiary($beneficiaryId, $endBefore = '', $startAfter = '')
	{
		if (! ($endBefore instanceof \DateTime)) {
			if ('' != $endBefore) {
				$endBefore = new \DateTime('@' . $endBefore);
			}
			else {
				$endBefore = false;
			}
		}
		if (! ($startAfter instanceof \DateTime)) {
			$startAfter = new \DateTime((empty($startAfter) ? '' : '@' . $startAfter));
		}
		$qry = $this->createQueryBuilder('e')
			->select('ev')
			->from($this->_entityName, 'ev')
			->leftJoin('ev.participants', 'u')
			->where('u.id = :beneficiaryId AND ev.dateStart >= :startAfter ' . ($endBefore ? ' AND ev.dateEnd <= :endBefore ' : ''))
			->setParameter('beneficiaryId', $beneficiaryId)
			->setParameter('startAfter', $startAfter)
			->orderBy('ev.dateStart', 'ASC');
		if ($endBefore) {
			$qry->setParameter('endBefore', $endBefore);
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
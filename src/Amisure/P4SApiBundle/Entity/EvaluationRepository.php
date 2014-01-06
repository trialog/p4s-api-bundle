<?php
namespace Amisure\P4SApiBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class EvaluationRepository extends EntityRepository
{
	/**
	 * @deprecated
	 */
	public function findMostRecent($beneficiaryId, $completed=true)
	{
		$q = $this->createQueryBuilder('e')
		->select('ev')
		->from($this->_entityName, 'ev')
		->leftJoin('ev.elements', 'el')
		->where('ev.beneficiaryId = :beneficiaryId')
		->orderBy('ev.evaluationDate', 'DESC')
		->setParameter('beneficiaryId', $beneficiaryId)
		//TODO add completed support
		->setMaxResults(1)
		->getQuery();
		try {
			$evaluation = $q->getSingleResult();
		} catch (NoResultException $e) {
			return null;
		}
		return $evaluation;
	}
}
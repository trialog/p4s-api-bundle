<?php
namespace Amisure\P4SApiBundle\Entity\User;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class UserRepository extends EntityRepository
{

	public function findOrganizationBy($beneficiaryId, $organizationType)
	{
		$q = $this->createQueryBuilder('u')
			->select('u, r')
			->leftJoin('u.roles', 'r')
			->where('u.organizationType = :organizationType AND u.beneficiaries IN :beneficiaryId')
			->setParameter('beneficiaryId', $beneficiaryId)
			->setParameter('organizationType', $organizationType)
			->getQuery();
		
		try {
			$user = $q->getSingleResult();
		} catch (NoResultException $e) {
			throw new UsernameNotFoundException(sprintf('Unable to find an active admin AmisureP4SApiBundle:User object identified by "%s".', $username), 0, $e);
		}
		return $user;
	}
}
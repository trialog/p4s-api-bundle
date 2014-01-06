<?php
namespace Amisure\P4SApiBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

class OrganizationRepository extends EntityRepository
{

	public function findByBeneficiary($beneficiaryId, $organizationType = '%')
	{
		$q = $this->createQueryBuilder('o')
			->select('o')
			->leftJoin('o.contact', 'ou')
			->where('o.type LIKE :organizationType
					AND :beneficiaryId MEMBER OF ou.beneficiaries')
			->orderBy('o.name', 'ASC')
			->setParameter('organizationType', $organizationType)
			->setParameter('beneficiaryId', $beneficiaryId)
			->getQuery();
		
		try {
			$organizations = $q->getResult();
		} catch (NoResultException $e) {
			// throw new \Exception(sprintf('Unable to find an a SaadList associated to this beneficiary "%s".', $beneficiaryId), 0, $e);
			return null;
		}
		return $organizations;
	}
	public function findByDepartement($organizationType = '%', $departementCode = '')
	{
		$q = $this->createQueryBuilder('o')
			->select('o')
			->leftJoin('o.contact', 'ou')
			->where('o.type LIKE :organizationType
					AND ou.zipcode LIKE :departementCode')
			->orderBy('o.name', 'ASC')
			->setParameter('organizationType', $organizationType)
			->setParameter('departementCode', $departementCode.'%')
			->getQuery();
		
		try {
			$organizations = $q->getResult();
		} catch (NoResultException $e) {
			// throw new \Exception(sprintf('Unable to find an a SaadList associated to this beneficiary "%s".', $beneficiaryId), 0, $e);
			return null;
		}
		return $organizations;
	}
}
<?php
namespace Amisure\P4SApiBundle\Entity\User;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

class OrganizationUserRepository extends EntityRepository
{

	public function findOrganizationBy($beneficiaryId, $organizationType)
	{
		$beneficiary = $this->_em->getRepository('Amisure\P4SApiBundle\Entity\User\BeneficiaryUser')->findOneBy(array('username' => $beneficiaryId));
		$q = $this->_em->createQuery('SELECT o
			FROM Amisure\P4SApiBundle\Entity\User\OrganizationUser o
			WHERE o.organizationType = :organizationType
			AND :beneficiary MEMBER OF o.beneficiaries');
		$q->setParameter('beneficiary', $beneficiary);
		$q->setParameter('organizationType', $organizationType);
		// $rsm = new ResultSetMapping();
		// $rsm->addScalarResult('id', 'u.id');
		// $rsm->addScalarResult('username', 'u.username');
		// $query = $this->_em->createNativeQuery('SELECT u.id, u.username, u.discr
		// FROM s1_user u
		// INNER JOIN s1_benificiary_organization bo ON bo.organizationuser_id = u.id
		// WHERE u.organizationType LIKE :organizationType
		// AND u.discr LIKE :discr
		// AND bo.beneficiaryuser_id = :beneficiaryId;',
		// $rsm);
		// $query->setParameter('discr', 'organizationUser');
		// $query->setParameter('beneficiaryId', $beneficiaryId);
		// $query->setParameter('organizationType', $organizationType);
		// echo $beneficiaryId;
		// echo $organizationType;
		// $users = $query->getResult();
		// var_dump($users);
		// die('test');
		// $q = $this->createQueryBuilder('u')
		// ->select('u, r')
		// ->leftJoin('u.roles', 'r')
		// ->where('u.organizationType = :organizationType AND u.beneficiaries IN :beneficiaryId')
		// ->setParameter('beneficiaryId', $beneficiaryId)
		// ->setParameter('organizationType', $organizationType)
		// ->getQuery();
		
		try {
			$user = $q->getSingleResult();
		} catch (NoResultException $e) {
			// throw new UsernameNotFoundException(sprintf('Unable to find an OrganizationUser %s object linked with "%s".', $organizationType, $beneficiaryId), 0, $e);
			return null;
		}
		return $user;
	}
}
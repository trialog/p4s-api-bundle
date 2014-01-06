<?php
namespace Amisure\P4SApiBundle\Entity\User;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

class BeneficiaryUserRepository extends EntityRepository
{

	public function findByRelatedBeneficiaries($organizationUser)
	{
		$q = $this->_em->createQuery('SELECT b
			FROM Amisure\P4SApiBundle\Entity\User\BeneficiaryUser b
			WHERE :organizationUser MEMBER OF b.organizations');
		$q->setParameter('organizationUser', $organizationUser);
		try {
			$users = $q->getResult();
		} catch (NoResultException $e) {
			// throw new UsernameNotFoundException(sprintf('Unable to find a list of BeneficiaryUser object linked with "%s".', $organizationUserId), 0, $e);
			return null;
		}
		return $users;
	}
}
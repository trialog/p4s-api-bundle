<?php
namespace Amisure\P4SApiBundle\Login;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use HWI\Bundle\OAuthBundle\Security\Core\User\EntityUserProvider;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Amisure\P4SApiBundle\Entity\UserConstants;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Session\Session;
use Amisure\P4SApiBundle\Entity\User\OrganizationUser;
use Amisure\P4SApiBundle\Entity\User\BeneficiaryUser;

class UserProvider extends EntityUserProvider implements UserProviderInterface
{

	protected $session, $doctrine, $em, $container;

	public function __construct($session, $doctrine, $service_container)
	{
		$this->session = $session;
		$this->doctrine = $doctrine;
		$this->em = $doctrine->getManager();
		$this->container = $service_container;
	}

	public function loadUserByUsername($username)
	{
		$q = $this->em->createQueryBuilder('u')
			->select('u, r')
			->from('Amisure\P4SApiBundle\Entity\User\SessionUser', 'u')
			->join('u.roles', 'r')
			->where('u.username = :username')
			->setParameter('username', $username)
			->getQuery();
		
		try {
			// La méthode Query::getSingleResult() lance une exception
			// s'il n'y a pas d'entrée correspondante aux critères
			$user = $q->getSingleResult();
		} catch (NoResultException $e) {
			// throw new UsernameNotFoundException(sprintf('Unable to find an active admin Amisure\P4SApiBundle\Entity\User\SessionUser object identified by "%s".', $username), 0, $e);
			return null;
		}
		return $user;
	}
	
	/*
	 * (non-PHPdoc) @see \HWI\Bundle\OAuthBundle\Security\Core\User\EntityUserProvider::loadUserByOAuthUserResponse()
	 */
	public function loadUserByOAuthUserResponse(UserResponseInterface $response)
	{
		// var_dump($response->getResponse());
		// die('test' . __FILE__ );
		// -- Load user's data from P4S
		$data = json_decode($response->getResponse(), true);
		if (null != $data && array_key_exists('status', $data) && 'OK' == $data['status'] && array_key_exists('profile', $data) && '' != $data['profile']) {
			$p4sId = $data['profile']['id'];
		}
		else {
			throw new UsernameNotFoundException("Unable to load this user info");
		}
		
		$result = $this->loadUserByUsername($p4sId);
		// - Create an account
		if (null == $result) {
			$role = $data['profile']['role'];
			$roleBridge = substr($role, 5, strlen($role) - 1);
			// Org User
			if (UserConstants::ROLE_ORG_USER == $roleBridge || UserConstants::ROLE_ORG_ADMIN_USER == $roleBridge) {
				$user = new OrganizationUser($p4sId, '', $firstname, $lastname);
				$user->setOrganizationType(@$data['profile']['organization_type']);
				// $user->setSubRole(@$data['profile']['sub_role']);
			}
			// Beneficiary
			else {
				$user = new BeneficiaryUser($p4sId, '', $data['profile']['first_name'], $data['profile']['last_name']);
			}
			
			// Add common data
			$user->setAddress($data['profile']);
			$roleRepository = $this->em->getRepository('AmisureP4SApiBundle:Role');
			$user->addRole($roleRepository->findOneBy(array(
				'role' => UserConstants::ROLE_USER
			)));
			$user->addRole($roleRepository->findOneBy(array(
				'role' => $roleBridge
			)));
			$em = $this->doctrine->getManager();
			$em->persist($user);
			$em->flush();
		}
		// - Existing user
		else {
			$user = $result;
		}
		return $this->loadUserByUsername($p4sId);
	}

	public function refreshUser(UserInterface $user)
	{
		$class = get_class($user);
		if (! $this->supportsClass($class)) {
			throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $class));
		}
		return $this->em->getRepository('Amisure\P4SApiBundle\Entity\User\SessionUser')->find($user->getId());
	}

	public function supportsClass($class)
	{
		$entityName = 'Amisure\\P4SApiBundle\\Entity\\User\\SessionUser';
		return $class === $entityName || is_subclass_of($class, $entityName);
	}
}
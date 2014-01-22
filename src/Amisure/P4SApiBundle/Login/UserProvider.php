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
use Doctrine\ORM\EntityManager;
use Amisure\P4SApiBundle\Entity\User\OrganizationUser;
use Amisure\P4SApiBundle\Entity\User\BeneficiaryUser;
use Amisure\P4SApiBundle\Entity\User\UserConstants;
use Amisure\P4SApiBundle\Accessor\Api\IDataAccessor;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Amisure\P4SApiBundle\Accessor\Api\ResponseHelper;

class UserProvider extends EntityUserProvider implements UserProviderInterface
{

	/**
	 *
	 * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
	 */
	private $session;

	/**
	 *
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 *
	 * @var \Amisure\P4SApiBundle\Accessor\Api\IDataAccessor
	 */
	private $dataAccessor;

	public function __construct(SessionInterface $session, EntityManager $em, IDataAccessor $dataAccessor)
	{
		$this->session = $session;
		$this->em = $em;
		$this->dataAccessor = $dataAccessor;
	}

	public function loadUserByUsername($username)
	{
		$user = $this->dataAccessor->getBeneficiary($username);
		if (null == $user) {
			throw new UsernameNotFoundException("Unable to load this user info");
		}
		$roleRepository = $this->em->getRepository('AmisureP4SApiBundle:Role');
		$user->addRole($roleRepository->findOneBy(array(
			'role' => UserConstants::ROLE_USER
		)));
		$user->addRole($roleRepository->findOneBy(array(
			'role' => $user->getRole()
		)));
		return $user;
	}

	public function loadUserByOAuthUserResponse(UserResponseInterface $response)
	{
		// echa($response->getAccessToken());
		// echa($response->getResponse(), __FILE__);
		// -- Load user's data from P4S
		$data = json_decode($response->getResponse(), true);
		if (null != $data && array_key_exists('status', $data) && ResponseHelper::OK == $data['status'] && array_key_exists('data', $data) && null != $data['data']) {
			$p4sId = $data['data']['id'];
		}
		else {
			throw new UsernameNotFoundException("Unable to load this user info");
		}
		
		$result = $this->em->getRepository('Amisure\P4SApiBundle\Entity\User\SessionUser')->findOneBy(array(
			'username' => $p4sId
		));
		// - Create an account
		if (null == $result) {
			$user = $this->fillUser($data['data']);
			$this->createNewUser($user);
		}
		// - Existing user
		else {
			// $user = $result;
			$user = $this->fillUser($data['data']);
			$user->setId($result->getId());
		}
		
		// -- Save access token
		$this->session->set('access_token', $response->getAccessToken());
		return $user;
	}

	public function fillUser($data)
	{
		// Org User
		if (UserConstants::ROLE_ORG_USER == $data['role'] || UserConstants::ROLE_ORG_ADMIN_USER == $data['role']) {
			$user = OrganizationUser::fromJson($data);
		}
		// Beneficiary
		else {
			$user = BeneficiaryUser::fromJson($data);
		}
		
		// Add common data
		$roleRepository = $this->em->getRepository('AmisureP4SApiBundle:Role');
		$user->addRole($roleRepository->findOneBy(array(
			'role' => UserConstants::ROLE_USER
		)));
		$user->addRole($roleRepository->findOneBy(array(
			'role' => $data['role']
		)));
		return $user;
	}

	public function createNewUser($user)
	{
		$this->session->set('newuser', true);
		$this->em->persist($user);
		$this->em->flush();
		return $user;
	}

	public function refreshUser(UserInterface $user)
	{
		$class = get_class($user);
		if (! $this->supportsClass($class)) {
			throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $class));
		}
		return $this->loadUserByUsername($user->getId());
	}

	public function supportsClass($class)
	{
		$entityName = 'Amisure\\P4SApiBundle\\Entity\\User\\SessionUser';
		return $class === $entityName || is_subclass_of($class, $entityName);
	}
}
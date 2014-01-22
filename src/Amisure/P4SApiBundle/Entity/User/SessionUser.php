<?php
namespace Amisure\P4SApiBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Amisure\P4SApiBundle\Entity\Event;
use Amisure\P4SApiBundle\Entity\Role;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUser;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="s1_user")
 * @ORM\Entity(repositoryClass="Amisure\P4SApiBundle\Entity\User\UserRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"sessionUser" = "SessionUser", "beneficiary" = "BeneficiaryUser", "organizationUser" = "OrganizationUser"})
 */
class SessionUser extends OAuthUser implements EquatableInterface, \Serializable
{

	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\Column(type="string", length=25, unique=true)
	 */
	protected $username;

	/**
	 * @ORM\Column(type="string", length=60, nullable=true)
	 */
	private $firstname;

	/**
	 * @ORM\Column(type="string", length=60, nullable=true)
	 */
	private $lastname;

	/**
	 * @ORM\Column(type="string", length=32, nullable=true)
	 */
	private $salt;

	/**
	 * @ORM\Column(type="string", length=40, nullable=true)
	 */
	private $password;

	/**
	 * @ORM\Column(type="string", length=60, nullable=true)
	 */
	private $organizationType;

	/**
	 * @ORM\Column(name="is_active", type="boolean")
	 */
	private $isActive;

	/**
	 *
	 * @var Collection @ORM\ManyToMany(targetEntity="Amisure\P4SApiBundle\Entity\Role", inversedBy="users")
	 *      @ORM\JoinTable(name="s1_user_role")
	 */
	private $roles;

	/**
	 *
	 * @var Collection @ORM\ManyToMany(targetEntity="Amisure\P4SApiBundle\Entity\Event", inversedBy="participants")
	 *      @ORM\JoinTable(name="s1_users_event")
	 */
	private $events;

	public function __construct($params = '', $firstname = '', $lastname = '')
	{
		// From Array
		if (is_array($params)) {
			extract($params);
			$this->setUsername(@$username);
			$this->setFirstname(@$firstname);
			$this->setLastname(@$lastname);
			return;
		}
		// From flat data
		$this->setUsername($params);
		$this->setFirstname($firstname);
		$this->setLastname($lastname);
		
		$this->isActive = true;
		$this->salt = ''; // md5(uniqid(null, true));
		$this->roles = new ArrayCollection();
		$this->events = new ArrayCollection();
	}

	/**
	 * @inheritDoc
	 */
	public function getUsername()
	{
		return $this->username;
	}

	/**
	 * @inheritDoc
	 */
	public function getSalt()
	{
		return $this->salt;
	}

	/**
	 * @inheritDoc
	 */
	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * @inheritDoc
	 */
	public function getRoles()
	{
		return $this->roles->toArray();
	}

	public function getId()
	{
		return $this->id;
	}

	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	public function setUsername($username)
	{
		$this->username = $username;
		return $this;
	}

	public function getFirstname()
	{
		return $this->firstname;
	}

	public function setFirstname($firstname)
	{
		$this->firstname = $firstname;
		return $this;
	}

	public function getLastname()
	{
		return $this->lastname;
	}

	public function setLastname($lastname)
	{
		$this->lastname = $lastname;
		return $this;
	}

	public function setSalt($salt)
	{
		$this->salt = $salt;
		return $this;
	}

	public function setPassword($password)
	{
		$this->password = $password;
		return $this;
	}

	public function getOrganizationType()
	{
		return $this->organizationType;
	}

	public function setOrganizationType($organizationType)
	{
		$this->organizationType = $organizationType;
		return $this;
	}

	public function getIsActive()
	{
		return $this->isActive;
	}

	public function setIsActive($isActive)
	{
		$this->isActive = $isActive;
		return $this;
	}

	public function setRoles($roles)
	{
		$this->roles = $roles;
		return $this;
	}

	public function addRole(Role $role)
	{
		if (! $this->roles->contains($role)) {
			$this->roles->add($role);
		}
		return $this;
	}

	public function setEvents(ArrayCollection $events)
	{
		foreach ($events as $event) {
			$event->addParticipant($this);
		}
		$this->events = $events;
		return $this;
	}

	public function addEvent(\Amisure\P4SApiBundle\Entity\Event $event)
	{
		$event->addParticipant($this);
		if (! $this->events->contains($event)) {
			$this->events->add($event);
		}
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function eraseCredentials()
	{}

	public function isAccountNonExpired()
	{
		return true;
	}

	public function isAccountNonLocked()
	{
		return true;
	}

	public function isCredentialsNonExpired()
	{
		return true;
	}

	public function isEnabled()
	{
		return $this->isActive;
	}

	/**
	 *
	 * @see \Serializable::serialize()
	 */
	public function serialize()
	{
		return serialize(array(
			$this->id
		));
	}

	/**
	 *
	 * @see \Serializable::unserialize()
	 */
	public function unserialize($serialized)
	{
		list ($this->id, ) = unserialize($serialized);
	}
	
	/*
	 * (non-PHPdoc) @see \HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUser::equals()
	 */
	public function equals(UserInterface $user)
	{
		if ((int) $this->getId() === $user->getId()) {
			return true;
		}
		return false;
	}
	
	/*
	 * (non-PHPdoc) @see \Symfony\Component\Security\Core\User\EquatableInterface::isEqualTo()
	 */
	public function isEqualTo(UserInterface $user)
	{
		if ((int) $this->getId() === $user->getId()) {
			return true;
		}
		return false;
	}
	
	public function __toString() 
	{
		return 'SessionUser[id='.$this->id.' , firstname='.$this->getFirstname().', lastname='.$this->getLastname().']';
	}
}

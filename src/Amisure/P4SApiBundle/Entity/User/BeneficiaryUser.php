<?php
namespace Amisure\P4SApiBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="Amisure\P4SApiBundle\Entity\User\BeneficiaryUserRepository")
 *
 * @author Olivier Maridat (Trialog)
 */
class BeneficiaryUser extends AUser
{

	/**
	 *
	 * @var Collection @ORM\ManyToMany(targetEntity="Amisure\P4SApiBundle\Entity\User\OrganizationUser", inversedBy="beneficiaries")
	 *      @ORM\JoinTable(name="s1_beneficiary_organization")
	 */
	private $organizations;

	/**
	 * @ORM\Column(type="string", length=25)
	 */
	private $maidenName;

	/**
	 * @ORM\Column(type="date", length=120)
	 */
	private $birthday;

	/**
	 * @ORM\Column(type="string", length=120)
	 */
	private $birthPlace;

	/**
	 * @ORM\Column(type="string", length=120)
	 */
	private $photo;

	/**
	 *
	 * @param mixed $params
	 *        	Id or array of params (instead of the following of flat params)
	 * @param string $gender        	
	 * @param string $firstname        	
	 * @param string $lastname        	
	 * @param string $maidenName        	
	 * @param string $adresse        	
	 * @param string $email        	
	 * @param string $tel1        	
	 * @param string $tel2        	
	 * @param string $fax        	
	 * @param string $birthday        	
	 * @param string $birthPlace        	
	 */
	public function __construct($params = '', $gender = '', $firstname = '', $lastname = '', $maidenName = '', $address = '', $email = '', $tel1 = '', $tel2 = '', $fax = '', $birthday = '', $birthPlace = '', $photo = '')
	{
		parent::__construct($params, UserConstants::ROLE_BENEFICIARY, $gender, $firstname, $lastname, $address, $email, $tel1, $tel2, $fax);
		// From Array
		if (is_array($params)) {
			extract($params);
			$this->setBirthday(@$birthday);
			$this->setBirthPlace(@$birthPlace);
			$this->setMaidenName(@$maidenName);
			$this->setPhoto(@$photo);
			return;
		}
		// From flat data
		$this->setBirthday($birthday);
		$this->setBirthPlace($birthPlace);
		$this->setMaidenName($maidenName);
		$this->setPhoto($photo);
		$this->organizations = new ArrayCollection();
	}

	public function getOrganizations()
	{
		return $this->organizations;
	}

	public function getOrganization($organizationType)
	{
		if (empty($organizationType) || empty($this->organizations)) {
			return null;
		}
		foreach ($this->organizations as $organizationUser) {
			if ($organizationType == $organizationUser->getOrganizationType()) {
				return $organizationUser;
			}
		}
		return null;
	}

	public function setOrganizations($organizations)
	{
		$this->organizations = $organizations;
		return $this;
	}

	public function addOrganization(OrganizationUser $organization)
	{
		$organization->addBeneficiary($this);
		if (! $this->organizations->contains($organization)) {
			$this->organizations->add($organization);
		}
		return $this;
	}

	public function removeOrganization(OrganizationUser $organization)
	{
		$this->organizations->removeElement($organization);
		return $this;
	}

	public function removeSaad()
	{
		foreach ($this->organizations as $organization) {
			if (UserConstants::SAAD == $organization->getOrganizationType()) {
				$this->removeOrganization($organization);
			}
		}
		return $this;
	}

	public function getBirthday()
	{
		return $this->birthday;
	}

	public function setBirthday($birthday)
	{
		if (is_numeric($birthday)) {
			$birthday = new \DateTime('@' . $birthday);
		}
		if (is_string($birthday)) {
			$birthday = \DateTime::createFromFormat('d/m/Y', $birthday);
		}
		$this->birthday = $birthday;
		return $this;
	}

	public function getBirthPlace()
	{
		return $this->birthPlace;
	}

	public function setBirthPlace($birthPlace)
	{
		$this->birthPlace = $birthPlace;
		return $this;
	}

	public function getMaidenName()
	{
		return $this->maidenName;
	}

	public function setMaidenName($maidenName)
	{
		$this->maidenName = $maidenName;
		return $this;
	}

	public function getPhoto()
	{
		return $this->photo;
	}

	public function setPhoto($photo)
	{
		$this->photo = $photo;
		return $this;
	}

	public static function fromJson($data)
	{
		$id = null;
		if (array_key_exists('beneficiary_id', $data)) {
			$id = $data['beneficiary_id'];
		}
		elseif(array_key_exists('id', $data)) {
			$id = $data['id'];
		}
		$user = new BeneficiaryUser($id, @$data['title'], @$data['first_name'], @$data['last_name'], @$data['maiden_name'], array(
			'address' => @$data['address'],
			'zipcode' => @$data['zipcode'],
			'city' => @$data['city']
		), @$data['email'], @$data['fixed_phone'], @$data['mobile_phone'], '', @$data['date_of_birth'], @$data['place_of_birth'], @$data['photo']);
		$user->setId($id);
		return $user;
	}
}

<?php
namespace Amisure\P4SApiBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="Amisure\P4SApiBundle\Entity\User\OrganizationUserRepository")
 *
 * @author Olivier Maridat (Trialog)
 */
class OrganizationUser extends AUser
{

	/**
	 *
	 * @var Collection @ORM\ManyToMany(targetEntity="Amisure\P4SApiBundle\Entity\User\BeneficiaryUser", mappedBy="organizations")
	 */
	private $beneficiaries;

	/**
	 * @ORM\Column(type="string", length=100)
	 */
	private $subRole;

	/**
	 *
	 * @param mixed $params
	 *        	Id or array of params (instead of the following of flat params)
	 * @param string $gender        	
	 * @param string $firstname        	
	 * @param string $lastname        	
	 * @param string $adresse        	
	 * @param string $email        	
	 * @param string $tel1        	
	 * @param string $tel2        	
	 * @param string $fax        	
	 * @param string $organizationType        	
	 */
	public function __construct($params, $gender = '', $firstname = '', $lastname = '', $address = '', $email = '', $tel1 = '', $tel2 = '', $fax = '', $organizationType = '', $role = 'ROLE_ORG_ADMIN_USER', $subRole = '')
	{
		parent::__construct($params, $role, $gender, $firstname, $lastname, $address, $email, $tel1, $tel2, $fax);
		// From Array
		if (is_array($params)) {
			extract($params);
			$this->setOrganizationType(@$organizationType);
			$this->setSubRole(@$subRole);
			return;
		}
		// From flat data
		$this->setOrganizationType($organizationType);
		$this->setSubRole($subRole);
		
		$this->beneficiaries = new ArrayCollection();
	}

	public function getBeneficiarys()
	{
		return $this->beneficiaries;
	}

	public function setBeneficiarys($beneficiaries)
	{
		$this->beneficiaries = $beneficiaries;
		return $this;
	}

	public function addBeneficiary(BeneficiaryUser $beneficiary)
	{
		if (! $this->beneficiaries->contains($beneficiary)) {
			$this->beneficiaries->add($beneficiary);
		}
		return $this;
	}

	public static function fromJson($data)
	{
		$id = null;
		if (array_key_exists('org_user_id', $data)) {
			$id = $data['org_user_id'];
		}
		elseif (array_key_exists('id', $data)) {
			$id = $data['id'];
		}
		$user = new OrganizationUser($id, @$data['title'], @$data['first_name'], @$data['last_name'], array(
			'address' => @$data['address'],
			'zipcode' => @$data['zipcode'],
			'city' => @$data['city']
		), @$data['email'], @$data['fixed_phone'], @$data['mobile_phone'], '', @$data['organization_type'], @$data['role'], @$data['sub_role']);
		$user->setId($id);
		return $user;
	}

	public function getBeneficiaries()
	{
		return $this->beneficiaries;
	}

	public function setBeneficiaries(ArrayCollection $beneficiaries)
	{
		$this->beneficiaries = $beneficiaries;
		return $this;
	}

	public function getSubRole()
	{
		return $this->subRole;
	}

	public function setSubRole($subRole)
	{
		$this->subRole = $subRole;
		return $this;
	}
}
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
	public function __construct($params, $gender = '', $firstname = '', $lastname = '', $address = '', $email = '', $tel1 = '', $tel2 = '', $fax = '', $organizationType = '')
	{
		parent::__construct($params, $gender, $firstname, $lastname, $address, $email, $tel1, $tel2, $fax);
		// From Array
		if (is_array($params)) {
			extract($params);
			$this->setOrganizationType(@$organizationType);
			return;
		}
		// From flat data
		$this->setOrganizationType($organizationType);
		
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
}
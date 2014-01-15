<?php
namespace Amisure\P4SApiBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 *
 * @author Olivier Maridat (Trialog)
 */
abstract class AUser extends SessionUser
{

	/**
	 * @ORM\Column(type="string", length=10)
	 */
	private $gender;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $address;

	/**
	 * @ORM\Column(type="string", length=10)
	 */
	private $zipcode;

	/**
	 * @ORM\Column(type="string", length=120)
	 */
	private $city;

	/**
	 * @ORM\Column(type="string", length=120)
	 */
	private $country;

	/**
	 * @ORM\Column(type="string", length=120)
	 */
	private $email;

	/**
	 * @ORM\Column(type="string", length=25)
	 */
	private $tel1;

	/**
	 * @ORM\Column(type="string", length=25)
	 */
	private $tel2;

	/**
	 * @ORM\Column(type="string", length=25)
	 */
	private $fax;

	/**
	 *
	 * @param mixed $params
	 *        	Username or array of params (instead of the following of flat params)
	 * @param string $gender        	
	 * @param string $firstname        	
	 * @param string $lastname        	
	 * @param string $adresse        	
	 * @param string $email        	
	 * @param string $tel1        	
	 * @param string $tel2        	
	 * @param string $fax        	
	 */
	public function __construct($params, $gender = '', $firstname = '', $lastname = '', $address = '', $email = '', $tel1 = '', $tel2 = '', $fax = '')
	{
		parent::__construct($params, $firstname, $lastname);
		// From Array
		if (is_array($params)) {
			extract($params);
			$this->setGender(@$gender);
			$this->setAddress(@$address);
			$this->setEmail(@$email);
			$this->setTel1(@$tel1);
			$this->setTel2(@$tel2);
			$this->setFax(@$fax);
			return;
		}
		// From flat data
		$this->setGender($gender);
		$this->setAddress($address);
		$this->setEmail($email);
		$this->setTel1($tel1);
		$this->setTel2($tel2);
		$this->setFax($fax);
	}

	public function getGender()
	{
		return $this->gender;
	}

	public function setGender($gender)
	{
		$this->gender = $gender;
		return $this;
	}

	/**
	 *
	 * @return string M. John Smith
	 */
	public function getFullname()
	{
		return $this->getGender() . ' ' . $this->getFirstname() . ' ' . strtoupper($this->getLastname());
	}

	public function getAddress()
	{
		return $this->address;
	}

	public function setAddress($address)
	{
		$this->address = $address;
		if (is_array($address)) {
			$this->address = @$address['address'];
			$this->setZipcode(@$address['zipcode']);
			$this->setCity(@$address['city']);
			$this->setCountry(@$address['country']);
		}
		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function getZipcode()
	{
		return $this->zipcode;
	}
	/**
	 *
	 * @return string Example : 94 or 77 or 20
	 */
	public function getDepartementCode()
	{
		if (null != $this->zipcode) {
			return substr($this->zipcode, 0, 2);
		}
		return '%';
	}

	/**
	 *
	 * @param string $zipcode        	
	 */
	public function setZipcode($zipcode)
	{
		$this->zipcode = $zipcode;
		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function getCity()
	{
		return $this->city;
	}

	/**
	 *
	 * @param string $city        	
	 */
	public function setCity($city)
	{
		$this->city = $city;
		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function getCountry()
	{
		return $this->country;
	}

	/**
	 *
	 * @param string $country        	
	 */
	public function setCountry($country)
	{
		$this->country = $country;
		return $this;
	}
	
	/**
	 * @param boolean $withCountry True to also add the country. False by default
	 * @return string Fill address : 25 avenue du Général Foy, 75008 Paris 
	 */
	public function getFullAddress($withCountry=false)
	{
		return $this->getAddress().', '.$this->getZipcode().' '.$this->getCity().($withCountry ? ' '.$this->getCountry() : '');
	}

	public function getEmail()
	{
		return $this->email;
	}

	public function setEmail($email)
	{
		$this->email = $email;
		return $this;
	}

	public function getTel1()
	{
		return $this->tel1;
	}

	public function setTel1($tel1)
	{
		$this->tel1 = $tel1;
		return $this;
	}

	public function getTel2()
	{
		return $this->tel2;
	}

	public function setTel2($tel2)
	{
		$this->tel2 = $tel2;
		return $this;
	}

	public function getFax()
	{
		return $this->fax;
	}

	public function setFax($fax)
	{
		$this->fax = $fax;
		return $this;
	}
}
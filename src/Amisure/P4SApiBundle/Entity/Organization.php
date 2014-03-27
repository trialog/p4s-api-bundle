<?php
namespace Amisure\P4SApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints\Collection;
use Amisure\P4SApiBundle\Entity\User\SessionUser;
use Amisure\P4SApiBundle\Entity\User\OrganizationUser;

/**
 * @ORM\Table(name="s1_organization")
 * @ORM\Entity(repositoryClass="Amisure\P4SApiBundle\Entity\OrganizationRepository")
 */
class Organization
{

	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\Column(type="string")
	 */
	private $name;

	/**
	 * @ORM\Column(type="string")
	 */
	private $type;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $address;

	/**
	 * @ORM\Column(type="string", length=10, nullable=true)
	 */
	private $zipcode;

	/**
	 * @ORM\Column(type="string", length=120, nullable=true)
	 */
	private $city;

	/**
	 * @ORM\Column(type="string", length=120, nullable=true)
	 */
	private $country;

	/**
	 * @ORM\Column(type="string", length=120, nullable=true)
	 */
	private $email;

	/**
	 * @ORM\Column(type="string", length=25, nullable=true)
	 */
	private $tel;

	/**
	 * @ORM\Column(type="string", length=25, nullable=true)
	 */
	private $fax;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	private $website;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	private $logo;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	private $finessNumber;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	private $sirenNumber;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	private $siretNumber;

	/**
	 * @ORM\OneToOne(targetEntity="Amisure\P4SApiBundle\Entity\User\OrganizationUser")
	 */
	private $contact;

	public function __construct($name = '', $type = '', $website = '')
	{
		$this->setId(- 1);
		$this->setName($name);
		$this->setType($type);
		$this->setWebsite($website);
	}

	public static function fromJson($data)
	{
		$id = null;
		if (array_key_exists('org_id', $data)) {
			$id = $data['org_id'];
		}
		elseif (array_key_exists('id', $data)) {
			$id = $data['id'];
		}
		$org = new Organization(@$data['name'], @$data['organization_type'], @$data['website']);
		$org->setId($id);
		$org->setAddress($data);
		$org->setTel(@$data['phone']);
		$org->setFax(@$data['fax']);
		$org->setEmail(@$data['fax']);
		$org->setLogo(@$data['logo']);
		$org->setFinessNumber(@$data['finess_number']);
		$org->setSirenNumber(@$data['siren_number']);
		$org->setSiretNumber(@$data['siret_number']);
		return $org;
	}

	public function __toString()
	{
		$str = '<strong>' . $this->name . '</strong>';
		if (! empty($this->website) || ! empty($this->contact)) {
			$str .= '<small>';
			if (! empty($this->website)) {
				$str .= '<br /><strong>Site Web&#160;:</strong>&#160;<a href="' . $this->website . '" title="Site Web de ' . str_replace('"', '&quot;', $this->name) . '">' . $this->website . '</a>';
			}
			if (! empty($this->contact)) {
				$str .= '<br /><strong>Contact&#160;&#160;&#160;:</strong>&#160;' . $this->contact->getGender() . ' ' . $this->contact->getFirstname() . ' ' . strtoupper($this->contact->getLastname()) . '';
			}
			$str .= '</small>';
		}
		return $str;
	}

	public function getOrganizationInfo()
	{
		$str = ''; // '<strong>' . $this->name . '</strong>';
		$str .= '<small>';
		if (! empty($this->website)) {
			$str .= '<br /><strong>Site Web&#160;:</strong>&#160;<a href="' . $this->website . '" title="Site Web de ' . str_replace('"', '&quot;', $this->name) . '">' . $this->website . '</a>';
		}
		$str .= '<br />Tél : ' . @$this->getTel() . ('' != $this->getFax() ? ' | ' . $this->getFax() : '');
		if ('' != $this->getEmail()) {
			$str .= '<br />Email : ' . $this->getEmail();
		}
		if ('' != $this->getAddress()) {
			$str .= '<br />Adresse : ' . $this->getFullAddress();
		}
		if (! empty($this->contact)) {
			$str .= '<br /><strong>Contact&#160;&#160;&#160;:</strong>&#160;' . $this->contact->getFullname();
			$str .= '<br />&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;Tél : ' . $this->contact->getTel1() . ('' != $this->contact->getTel2() ? ' | ' . $this->contact->getTel2() : '') . ('' != $this->contact->getFax() ? ' | ' . $this->contact->getFax() : '');
			if ('' != $this->contact->getEmail()) {
				$str .= '<br />&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;Email : ' . $this->contact->getEmail();
			}
			if ('' != $this->contact->getAddress()) {
				$str .= '<br />&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;Adresse : ' . $this->contact->getFullAddress();
			}
		}
		$str .= '</small>';
		return $str;
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

	public function getSaads()
	{
		return $this->saads;
	}

	public function getName()
	{
		return $this->name;
	}

	/**
	 *
	 * @return \Amisure\P4SApiBundle\Entity\Organisation
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function getWebsite()
	{
		return $this->website;
	}

	/**
	 *
	 * @param string $website        	
	 * @return \Amisure\P4SApiBundle\Entity\Organisation
	 */
	public function setWebsite($website)
	{
		$this->website = $website;
		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function getContact()
	{
		return $this->contact;
	}

	/**
	 *
	 * @return \Amisure\P4SApiBundle\Entity\Organisation
	 */
	public function setContact($contact)
	{
		$this->contact = $contact;
		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 *
	 * @return \Amisure\P4SApiBundle\Entity\Organisation
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
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
	 * @param boolean $withCountry
	 *        	True to also add the country. False by default
	 * @return string Fill address : 25 avenue du Général Foy, 75008 Paris
	 */
	public function getFullAddress($withCountry = false)
	{
		return $this->getAddress() . "\n" . $this->getZipcode() . ' ' . $this->getCity() . ($withCountry ? "\n" . $this->getCountry() : '');
	}

	public function getZipcode()
	{
		return $this->zipcode;
	}

	public function setZipcode($zipcode)
	{
		$this->zipcode = $zipcode;
		return $this;
	}

	public function getCity()
	{
		return $this->city;
	}

	public function setCity($city)
	{
		$this->city = $city;
		return $this;
	}

	public function getCountry()
	{
		return $this->country;
	}

	public function setCountry($country)
	{
		$this->country = $country;
		return $this;
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

	public function getTel()
	{
		return $this->tel;
	}

	public function setTel($tel)
	{
		$this->tel = $tel;
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

	public function getLogo()
	{
		return $this->logo;
	}

	public function setLogo($logo)
	{
		$this->logo = $logo;
		return $this;
	}

	public function getFinessNumber()
	{
		return $this->finessNumber;
	}

	public function setFinessNumber($finessNumber)
	{
		$this->finessNumber = $finessNumber;
		return $this;
	}

	public function getSirenNumber()
	{
		return $this->sirenNumber;
	}

	public function setSirenNumber($sirenNumber)
	{
		$this->sirenNumber = $sirenNumber;
		return $this;
	}

	public function getSiretNumber()
	{
		return $this->siretNumber;
	}

	public function setSiretNumber($siretNumber)
	{
		$this->siretNumber = $siretNumber;
		return $this;
	}
}
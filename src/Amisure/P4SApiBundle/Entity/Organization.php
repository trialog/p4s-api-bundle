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
	 * @ORM\Column(type="string", nullable=true)
	 */
	private $website;

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

	public function getId()
	{
		return $this->id;
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
		$str = '<strong>' . $this->name . '</strong>';
		if (! empty($this->website) || ! empty($this->contact)) {
			$str .= '<small>';
			if (! empty($this->website)) {
				$str .= '<br /><strong>Site Web&#160;:</strong>&#160;<a href="' . $this->website . '" title="Site Web de ' . str_replace('"', '&quot;', $this->name) . '">' . $this->website . '</a>';
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
		}
		return $str;
	}

	/**
	 *
	 * @return \Amisure\P4SApiBundle\Entity\SaadList
	 */
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
}
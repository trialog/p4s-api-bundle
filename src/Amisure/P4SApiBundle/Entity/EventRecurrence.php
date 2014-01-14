<?php
namespace Amisure\P4SApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\EventbriteResourceOwner;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table(name="s1_eventrecurrence")
 * @ORM\Entity
 */
class EventRecurrence
{

	const TypeHour = 'hour';

	const TypeDay = 'day';

	const TypeWeek = 'week';

	const TypeMonth = 'month';

	const TypeYear = 'year';

	const NbInfinite = '9999';

	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\Column(type="integer")
	 */
	private $frequency;

	/**
	 * @ORM\Column(type="text")
	 */
	private $type;

	/**
	 * @ORM\Column(type="integer")
	 */
	private $nb;

	public function __construct($frequency = 1, $type = EventRecurrence::TypeWeek, $nb = 1)
	{
		$this->setFrequency($frequency);
		$this->setType($type);
		$this->setNb($nb);
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

	public function getFrequency()
	{
		return $this->frequency;
	}

	public function setFrequency($frequency)
	{
		$this->frequency = $frequency;
		return $this;
	}

	public function getType()
	{
		return $this->type;
	}

	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}

	public function getNb()
	{
		if ($this->isInfinite()) {
			if ($this->type == EventRecurrence::TypeDay) {
				return 365 / $this->frequency; // 1year
			}
			elseif ($this->type == EventRecurrence::TypeWeek) {
				return 52/$this->frequency;//1year
			}
			elseif ($this->type == EventRecurrence::TypeMonth) {
				return 12 / $this->frequency; // 1year
			}
			elseif ($this->type == EventRecurrence::TypeYear) {
				return 2 / $this->frequency; // 2years
			}
			return 100; // ouch!
		}
		return $this->nb;
	}

	public function isInfinite()
	{
		return ($this->nb == EventRecurrence::NbInfinite);
	}

	public function setNb($nb)
	{
		$this->nb = $nb;
		return $this;
	}
}
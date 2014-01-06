<?php
namespace Amisure\P4SApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints\Collection;
use Amisure\P4SApiBundle\Entity\User\SessionUser;

/**
 * @ORM\Table(name="s1_event")
 * @ORM\Entity(repositoryClass="Amisure\P4SApiBundle\Entity\EventRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Event
{

	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 *
	 * @var Collection @ORM\ManyToMany(targetEntity="Amisure\P4SApiBundle\Entity\User\SessionUser", mappedBy="events", cascade={"persist"})
	 */
	private $participants;

	/**
	 * @ORM\Column(type="text")
	 */
	private $object;

	/**
	 * Beginning of the event
	 *
	 * @var datetime @ORM\Column(type="datetime")
	 */
	private $dateStart;

	/**
	 * End of the event
	 *
	 * @var datetime @ORM\Column(type="datetime")
	 */
	private $dateEnd;

	public function __construct($object = '')
	{
		$this->setId(- 1);
		$this->setObject($object);
		$this->dateStart = new \DateTime();
		$this->dateStart->add(new \DateInterval('P1D'));
		$this->dateEnd = new \DateTime();
		$this->dateEnd->add(new \DateInterval('P1DT2H'));
		$this->participants = new ArrayCollection();
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

	public function getParticipants()
	{
		return $this->participants;
	}

	public function setParticipants(Collection $participants)
	{
		$this->participants = $participants;
		return $this;
	}

	/**
	 *
	 * @param \Amisure\P4SApiBundle\Entity\User\SessionUser $participant        	
	 * @return \Amisure\P4SApiBundle\Entity\Event
	 */
	public function addParticipant(SessionUser $participant)
	{
		if (! $this->participants->contains($participant)) {
			$this->participants->add($participant);
			$participant->addEvent($this);
		}
		return $this;
	}

	/**
	 *
	 * @param \Amisure\P4SApiBundle\Entity\User\SessionUser $participant        	
	 * @return \Amisure\P4SApiBundle\Entity\Event
	 */
	public function removeParticipant(SessionUser $participant)
	{
		$this->participants->removeElement($participant);
		return $this;
	}

	public function getObject()
	{
		return preg_replace('!^\[.+\] (.*)$!iU', '$1', $this->object);
	}

	public function setObject($object)
	{
		$this->object = $object;
		return $this;
	}
	
	public function getCategory()
	{
		$found = 0;
		$cat = preg_replace('!^\[(.+)\] .*$!iU', '$1', $this->object, -1, $found);
		return (0 == $found ? '' : $cat);
	}

	public function getDateStart()
	{
		return $this->dateStart;
	}

	public function setDateStart(\DateTime $dateStart)
	{
		$this->dateStart = $dateStart;
		return $this;
	}

	public function getDateEnd()
	{
		return $this->dateEnd;
	}

	public function setDateEnd(\DateTime $dateEnd)
	{
		$this->dateEnd = $dateEnd;
		return $this;
	}

	/**
	 * @ORM\PrePersist()
	 * @ORM\PreUpdate()
	 */
	public function computeDateEnd()
	{
		if (null === $this->getDateStart()) {
			return;
		}
		$hourDuration = $this->getDateEnd();
		$hourDurationStr = $hourDuration->format('H \h\o\u\r\s i \m\i\n\u\t\e\s');
		$dateEnd = new \DateTime($this->getDateStart()->format('Y-m-d H:i:sP'));
		$dateEnd->add(\DateInterval::createFromDateString($hourDurationStr));
		$this->setDateEnd($dateEnd);
	}
}
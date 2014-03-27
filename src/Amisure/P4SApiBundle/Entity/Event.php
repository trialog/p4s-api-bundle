<?php
namespace Amisure\P4SApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints\Collection;
use Amisure\P4SApiBundle\Entity\User\SessionUser;
use Amisure\P4SApiBundle\Entity\User\UserConstants;

/**
 * @ORM\Table(name="s1_event")
 * @ORM\Entity(repositoryClass="Amisure\P4SApiBundle\Entity\EventRepository")
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
	 * @var Collection @ORM\ManyToMany(targetEntity="Amisure\P4SApiBundle\Entity\User\SessionUser", mappedBy="events")
	 */
	private $participants;

	/**
	 * @ORM\Column(type="text")
	 */
	private $object;

	/**
	 * @ORM\Column(type="text")
	 */
	private $description;

	/**
	 * @ORM\Column(type="text")
	 */
	private $location;

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

	/**
	 * @ORM\ManyToOne(targetEntity="Amisure\P4SApiBundle\Entity\EventRecurrence", cascade={"persist", "remove"})
	 */
	private $recurrence;

	/**
	 * @ORM\OneToMany(targetEntity="Amisure\P4SApiBundle\Entity\Event", mappedBy="parent", orphanRemoval=true, cascade={"persist", "remove"})
	 */
	private $childs;

	/**
	 * @ORM\ManyToOne(targetEntity="Amisure\P4SApiBundle\Entity\Event", inversedBy="childs")
	 * @ORM\JoinColumn(name="parent_event_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
	 */
	private $parent;

	public function __construct($object = '', $description = '', $location = '')
	{
		$this->setId(- 1);
		$this->setObject($object);
		$this->setDescription($description);
		$this->setLocation($location);
		$this->dateStart = new \DateTime();
		$this->dateStart->add(new \DateInterval('P1D'));
		$this->dateStart->setTime($this->dateStart->format('H'), ($this->dateStart->format('i') - ($this->dateStart->format('i') % 15)), $this->dateStart->format('s'));
		$this->dateEnd = new \DateTime();
		$this->dateEnd->add(new \DateInterval('P1DT2H'));
		$this->dateEnd->setTime($this->dateEnd->format('H'), ($this->dateEnd->format('i') - ($this->dateEnd->format('i') % 15)), $this->dateEnd->format('s'));
		$this->participants = new ArrayCollection();
		$this->childs = new ArrayCollection();
	}

	public function toArray()
	{
		$event = array();
		if (!empty($this->id) && -1 != $this->id) {
			$event['id'] = $this->id;
		}
		$event['start_date'] = $this->getDateStart()->getTimestamp();
		$event['end_date'] = $this->getDateEnd()->getTimestamp();
		$event['object'] = $this->object;
		$event['description'] = $this->getDescription();
		$event['place'] = $this->getLocation();
		if (null != $this->getRecurrence()) {
			$event['recurrence'] = $this->getRecurrence()->getType();
			$event['frequency'] = $this->getRecurrence()->getFrequency();
			$event['occ_nbr'] = $this->getRecurrence()->getNb();
		}
		$event['participants'] = array();
		foreach ($this->participants as $participant) {
			$participantArray = array();
			$participantArray['id'] = $participant->getUsername();
			$participantArray['role'] = $participant->getBusinessRole();
			if (UserConstants::ROLE_BENEFICIARY == $participantArray['role']) {
				continue;
			}
			$event['participants'][] = $participantArray;
		}
		return $event;
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
			// $participant->addEvent($this);
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
		return preg_replace('!^\[.+\] (.*)$!isU', '$1', $this->object);
	}

	public function setObject($object)
	{
		$this->object = $object;
		return $this;
	}

	public function getCategory()
	{
		$found = 0;
		$cat = preg_replace('!^\[(.+)\] .*$!isU', '$1', $this->object, - 1, $found);
		return (0 == $found ? '' : $cat);
	}

	public function getDateStart()
	{
		return $this->dateStart;
	}

	public function setDateStart($date)
	{
		if (null != $date && '' != $date && ! ($date instanceof \DateTime)) {
			$date = new \DateTime('@' . $date);
		}
		$this->dateStart = $date;
		return $this;
	}

	public function getDateEnd()
	{
		return $this->dateEnd;
	}

	public function setDateEnd($date)
	{
		if (null != $date && '' != $date && ! ($date instanceof \DateTime)) {
			$date = new \DateTime('@' . $date);
		}
		$this->dateEnd = $date;
		return $this;
	}

	public function getRecurrence()
	{
		return $this->recurrence;
	}

	public function setRecurrence($recurrenceFrequency, $recurrenceType = EventRecurrence::TypeWeek, $recurrenceNb = 0)
	{
		if (null != $recurrenceFrequency && $recurrenceFrequency instanceof EventRecurrence) {
			$this->recurrence = $recurrenceFrequency;
		}
		else {
			$this->recurrence = new EventRecurrence($recurrenceFrequency, $recurrenceType, $recurrenceNb);
		}
		return $this;
	}

	public function getChilds()
	{
		return $this->childs;
	}

	public function setChilds(ArrayCollection $childs)
	{
		$this->childs = $childs;
		return $this;
	}

	/**
	 *
	 * @param Event $child        	
	 * @return \Amisure\P4SApiBundle\Entity\Event
	 */
	public function addChild(Event $child)
	{
		$child->setParent($this);
		if (! $this->childs->contains($child)) {
			$this->childs->add($child);
		}
		return $this;
	}

	/**
	 *
	 * @param int|Event $child
	 *        	Index of the child, or child Event object
	 * @return \Amisure\P4SApiBundle\Entity\Event
	 */
	public function removeChild($child)
	{
		if (is_numeric($child)) {
			$this->childs->remove($child);
		}
		else {
			$this->childs->removeElement($child);
		}
		return $this;
	}

	public function getParent()
	{
		return $this->parent;
	}

	public function setParent($parent)
	{
		$this->parent = $parent;
		return $this;
	}

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

	public function getDescription()
	{
		return $this->description;
	}

	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}

	public function getLocation()
	{
		return $this->location;
	}

	public function setLocation($location)
	{
		$this->location = $location;
		return $this;
	}
}
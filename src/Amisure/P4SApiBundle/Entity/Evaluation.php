<?php
namespace Amisure\P4SApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Evaluation result
 *
 * @ORM\Table(name="p4s_evaluation")
 * @ORM\Entity(repositoryClass="Amisure\P4SApiBundle\Entity\EvaluationRepository")
 */
class Evaluation
{

	const StateFinished = '1';

	const StateInProgress = '0';

	/**
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 *
	 * @var integer
	 */
	private $id;

	/**
	 * Id of the section in the beneficiary folder (in the data server) that hosts the evaluation
	 *
	 * @ORM\Column(name="bf_section_id", type="string", length=255, unique=true)
	 *
	 * @var string
	 */
	private $folderSectionId;

	/**
	 * Logical name : AGGIR, MNA
	 *
	 * @ORM\Column(type="string", length=255)
	 *
	 * @var string
	 */
	private $code;

	/**
	 * @ORM\Column(type="integer")
	 *
	 * @var integer
	 */
	private $state;
	
	/* Folowing elements won't be store anymore in the P4S db once the link with the data server will be ready */
	/**
	 * @ORM\Column(name="beneficiary_id", type="integer")
	 *
	 * @var integer
	 */
	private $beneficiaryId;

	/**
	 * @ORM\Column(name="evaluator_id", type="integer")
	 *
	 * @var integer
	 */
	private $evaluatorId;

	/**
	 * @ORM\Column(name="app_id", type="integer")
	 *
	 * @var integer
	 */
	private $appId;

	/**
	 * @ORM\Column(type="datetime")
	 *
	 * @var \DateTime
	 */
	private $date;

	/**
	 * @ORM\Column(name="last_update", type="datetime")
	 *
	 * @var \DateTime
	 */
	private $lastUpdate;

	/**
	 * Object of the evaluation, added by the evaluator
	 * 
	 * @var string @ORM\Column(type="text")
	 */
	private $object;

	/**
	 * @ORM\OneToMany(targetEntity="Amisure\P4SApiBundle\Entity\EvaluationItem", mappedBy="evaluation", cascade={"persist", "remove"})
	 *
	 * @var Collection
	 */
	private $items;

	public function __construct($folderSectionId = '', $code = '', $beneficiaryId = '', $evaluatorId = '', $appId = '', $state = Evaluation::StateInProgress, $id = -1)
	{
		$this->setId($id);
		$this->setFolderSectionId($folderSectionId);
		$this->setCode($code);
		$this->setBeneficiaryId($beneficiaryId);
		$this->setEvaluatorId($evaluatorId);
		$this->setAppId($appId);
		$this->setState($state);
		$this->date = new \DateTime();
		$this->lastUpdate = new \DateTime();
		$this->items = new ArrayCollection();
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

	public function getFolderSectionId()
	{
		return $this->folderSectionId;
	}

	public function setFolderSectionId($folderSectionId)
	{
		$this->folderSectionId = $folderSectionId;
		return $this;
	}

	/**
	 *
	 * @param string $code
	 *        	Logical name : AGGIR, AMA
	 * @return \Amisure\P4SApiBundle\Entity\Evaluation
	 */
	public function setCode($code)
	{
		$this->code = $code;
		
		return $this;
	}

	/**
	 * Logical name : AGGIR, AMA
	 *
	 * @return string
	 */
	public function getCode()
	{
		return $this->code;
	}

	public function getState()
	{
		return $this->state;
	}

	public function setState($state)
	{
		$this->state = $state;
		return $this;
	}

	public function getBeneficiaryId()
	{
		return $this->beneficiaryId;
	}

	public function setBeneficiaryId($beneficiaryId)
	{
		$this->beneficiaryId = $beneficiaryId;
		return $this;
	}

	public function getEvaluatorId()
	{
		return $this->evaluatorId;
	}

	public function setEvaluatorId($evaluatorId)
	{
		$this->evaluatorId = $evaluatorId;
		return $this;
	}

	public function getAppId()
	{
		return $this->appId;
	}

	public function setAppId($appId)
	{
		$this->appId = $appId;
		return $this;
	}

	public function getDate()
	{
		if (null == $this->date) {
			return 0;
		}
		return $this->date->getTimestamp();
	}

	public function getDateObject()
	{
		return $this->date;
	}

	public function setDate($date)
	{
		if (null != $date && '' != $date && ! ($date instanceof \DateTime)) {
			$date = new \DateTime('@' . $date);
		}
		$this->date = $date;
		return $this;
	}

	public function getLastUpdate()
	{
		if (null == $this->lastUpdate) {
			return 0;
		}
		return $this->lastUpdate->getTimestamp();
	}

	public function getLastUpdateObject()
	{
		return $this->lastUpdate;
	}

	public function setLastUpdate($lastUpdate)
	{
		if (null != $lastUpdate && '' != $lastUpdate && ! ($lastUpdate instanceof \DateTime)) {
			$lastUpdate = new \DateTime('@' . $lastUpdate);
		}
		$this->lastUpdate = $lastUpdate;
		return $this;
	}

	public function getItems()
	{
		return $this->items->toArray();
	}

	public function getItemCollection()
	{
		return $this->items;
	}

	public function getItemById($id)
	{
		foreach ($this->items as $item) {
			if ($item->getId() == $id) {
				return $item;
			}
		}
		return null;
	}

	/**
	 *
	 * @param ArrayCollection $results        	
	 * @return \Amisure\P4SApiBundle\Entity\Evaluation
	 */
	public function setItems($items)
	{
		if (is_array($items)) {
			$items = new ArrayCollection($items);
		}
		$this->items = $items;
		return $this;
	}

	/**
	 * Add a new category that list items
	 *
	 * @param \Amisure\P4SApiBundle\Entity\EvaluationItem|string $item
	 *        	Evaluation item, or name of the evaluation item
	 * @see \Amisure\P4SApiBundle\Entity\EvaluationItem
	 * @return \Amisure\P4SApiBundle\Entity\Evaluation
	 */
	public function addItem($item)
	{
		$evaluationItem = $item;
		if (is_string($item)) {
			$evaluationItem = new EvaluationItem($item);
		}
		$evaluationItem->setEvaluation($this);
		$this->items->add($evaluationItem);
		return $this;
	}

	public function removeItem(EvaluationItem $item)
	{
		$this->items->removeElement($item);
		return $this;
	}

	public static function fromJson($data)
	{
		if (! is_array($data)) {
			return null;
		}
		$element = new Evaluation(@$data['folderSectionId'], @$data['code'], @$data['beneficiaryId'], @$data['evaluatorId'], @$data['appId'], @$data['state'], @$data['id']);
		$element->setDate(@$data['date']);
		$element->setLastUpdate(@$data['lastUpdate']);
		if (array_key_exists('items', $data) && is_array($data['items']) && count($data['items']) > 0) {
			foreach ($data['items'] as $item) {
				if (array_key_exists('responses', $item) && is_array($item['responses']) && ! empty($item['responses'])) {
					$newItem = new EvaluationItem(@$item['responses'], @$item['id']);
					$element->addItem($newItem);
				}
			}
		}
		return $element;
	}

	public function getObject()
	{
		return $this->object;
	}

	public function setObject(string $object)
	{
		$this->object = $object;
		return $this;
	}
	
}

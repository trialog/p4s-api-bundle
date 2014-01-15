<?php
namespace Amisure\P4SApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection as ArrayCollection;

/**
 * @ORM\Table(name="s1_evaluation")
 * @ORM\Entity(repositoryClass="Amisure\P4SApiBundle\Entity\EvaluationRepository")
 */
class Evaluation
{

	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\Column(type="integer")
	 */
	private $beneficiaryId;

	/**
	 * @ORM\Column(type="integer")
	 */
	private $evaluatorId;

	/**
	 * @ORM\Column(type="integer")
	 */
	private $serviceId;

	/**
	 * @ORM\Column(type="boolean")
	 */
	private $finished;

	/**
	 *
	 * @var datetime @ORM\Column(type="datetime")
	 */
	private $evaluationDate;

	/**
	 * @ORM\Column(type="text")
	 */
	private $evaluationObject;

	/**
	 *
	 * @var Collection @ORM\OneToMany(targetEntity="Amisure\P4SApiBundle\Entity\EvaluationElement", mappedBy="evaluation", cascade={"persist", "remove"})
	 */
	private $elements;

	public function __construct($object = '')
	{
		$this->setId(- 1);
		$this->setFinished(false);
		$this->setEvaluationObject($object);
		$this->evaluationDate = new \DateTime();
		$this->elements = new ArrayCollection();
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

	public function getServiceId()
	{
		return $this->serviceId;
	}

	public function setServiceId($serviceId)
	{
		$this->serviceId = $serviceId;
		return $this;
	}

	public function getFinished()
	{
		return $this->finished;
	}

	public function setFinished($finished)
	{
		$this->finished = $finished;
		return $this;
	}

	public function getEvaluationDate()
	{
		return $this->evaluationDate;
	}

	public function setEvaluationDate(\DateTime $evaluationDate)
	{
		$this->evaluationDate = $evaluationDate;
		return $this;
	}

	public function getEvaluationObject()
	{
		return $this->evaluationObject;
	}

	public function setEvaluationObject($evaluationObject)
	{
		$this->evaluationObject = $evaluationObject;
		return $this;
	}

	public function getElements()
	{
		return $this->elements;
	}

	public function setElements(ArrayCollection $elements)
	{
		$this->elements = $elements;
		return $this;
	}

	/**
	 * Add a new element
	 *
	 * @param Amisure\P4SApiBundle\Entity\EvaluationElement|string $element
	 *        	Evaluation element, or description of the evaluation element
	 * @param string $value
	 *        	Value of the evaluation element if the evaluation element itself is not previsously provided
	 * @see EvaluationElement
	 */
	public function addElement($element, $value = '')
	{
		$evaluationElement = $element;
		if (is_string($element)) {
			$evaluationElement = new EvaluationElement($element, $value);
		}
		$evaluationElement->setEvaluation($this);
		$this->elements->add($evaluationElement);
		return $this;
	}

	public function removeElement(\Amisure\P4SApiBundle\Entity\EvaluationElement $element)
	{
		$this->elements->removeElement($element);
		return $this;
	}
}
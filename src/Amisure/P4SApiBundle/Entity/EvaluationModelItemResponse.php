<?php
namespace Amisure\P4SApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EvaluationItemResponse
 *
 * @ORM\Table(name="p4s_evaluationmodelitemresponse")
 * @ORM\Entity
 */
class EvaluationModelItemResponse
{

	const TypeInt = '\d*';

	const TypeDecimal = '\d*\.\d*';

	const TypeString = '.*';

	/**
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 *
	 * @var integer
	 */
	private $id;

	/**
	 *
	 * @var string @ORM\Column(name="response_id", type="string", length=255, nullable=true)
	 */
	private $responseId;

	/**
	 * @ORM\Column(name="type", type="string", length=255)
	 *
	 * @var string
	 */
	private $type;

	/**
	 * @ORM\Column(type="boolean")
	 *
	 * @var boolean
	 */
	private $selected;

	/**
	 * @ORM\Column(name="label", type="text", nullable=true)
	 *
	 * @var string
	 */
	private $label;

	/**
	 * @ORM\Column(name="value", type="text", nullable=true)
	 *
	 * @var string
	 */
	private $value;

	/**
	 * @ORM\ManyToOne(targetEntity="Amisure\P4SApiBundle\Entity\EvaluationModelItem", inversedBy="responses")
	 * @ORM\JoinColumn(nullable=false)
	 *
	 * @var \Amisure\P4SApiBundle\Entity\EvaluationItem
	 */
	private $item;

	public function __construct($value = '', $label = '', $type = EvaluationModelItemResponse::TypeString, $responseId = -1, $id = -1, $selected = false)
	{
		$this->setId($id);
		$this->setResponseId($responseId);
		$this->setValue($value);
		$this->setLabel($label);
		$this->setType($type);
		$this->setSelected($selected);
	}

	/**
	 *
	 * @return integer
	 */
	public function getId()
	{
		return $this->id;
	}

	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	public function getResponseId()
	{
		return $this->responseId;
	}

	public function setResponseId($responseId)
	{
		$this->responseId = $responseId;
		return $this;
	}

	public function getLabel()
	{
		return $this->label;
	}

	public function getResponseText()
	{
		return $this->getLabel();
	}

	public function setLabel($label)
	{
		$this->label = $label;
		return $this;
	}

	public function setResponseText($label)
	{
		return $this->setLabel($label);
	}

	public function getValue()
	{
		return $this->value;
	}

	public function getResponseValue()
	{
		return $this->getValue();
	}

	public function setValue($value)
	{
		$this->value = $value;
		return $this;
	}

	/**
	 *
	 * @param string $value        	
	 * @return \Amisure\P4SApiBundle\Entity\EvaluationItemResponse
	 */
	public function setResponseValue($value)
	{
		return $this->setValue($value);
	}

	public function getType()
	{
		return $this->type;
	}

	public function getResponseValueType()
	{
		return $this->getType();
	}

	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}

	/**
	 * Return true if the response type correspond to the given type
	 *
	 * @param string $type        	
	 * @return boolean
	 */
	public function isType($type)
	{
		return ($this->type == $type);
	}

	public function setResponseValueType($type)
	{
		return $this->setType($type);
	}

	public function getItem()
	{
		return $this->item;
	}

	public function getEvaluationItem()
	{
		return $this->getItem();
	}

	public function setItem($item)
	{
		$this->item = $item;
		return $this;
	}

	public function setEvaluationItem(\Amisure\P4SApiBundle\Entity\EvaluationModelItem $item)
	{
		return $this->setItem($item);
	}

	public function getSelected()
	{
		return $this->selected;
	}

	public function setSelected($selected)
	{
		$this->selected = $selected;
		return $this;
	}
}
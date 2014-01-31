<?php
namespace Amisure\P4SApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * EvaluationItemResponse
 *
 * @ORM\Table(name="p4s_evaluationitemresponse")
 * @ORM\Entity
 */
class EvaluationItemResponse
{

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
	 * @var string @ORM\Column(type="text")
	 */
	private $value;

	/**
	 * @ORM\ManyToOne(targetEntity="Amisure\P4SApiBundle\Entity\EvaluationItem", inversedBy="responses")
	 *
	 * @var \Amisure\P4SApiBundle\Entity\EvaluationItem
	 */
	private $item;

	/**
	 *
	 * @param array|string $value
	 *        	Array of id and value, or directly a value string
	 * @param string $id
	 *        	Id of this response (if the first parameter is not an array)
	 */
	public function __construct($value = '', $id = -1)
	{
		if (null != $value && is_array($value) && ! empty($value)) {
			$this->setId(@$value['id']);
			$this->setValue(@$value['value']);
		}
		else {
			$this->setId($id);
			$this->setValue($value);
		}
	}

	public function getId()
	{
		return $this->id;
	}

	/**
	 *
	 * @return \Amisure\P4SApiBundle\Entity\EvaluationItemResponse
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	public function getValue()
	{
		return $this->value;
	}

	/**
	 *
	 * @return \Amisure\P4SApiBundle\Entity\EvaluationItemResponse
	 */
	public function setValue($value)
	{
		$this->value = $value;
		return $this;
	}

	public function getItem()
	{
		return $this->item;
	}

	/**
	 *
	 * @return \Amisure\P4SApiBundle\Entity\EvaluationItemResponse
	 */
	public function setItem($item)
	{
		$this->item = $item;
		return $this;
	}
}
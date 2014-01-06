<?php
namespace Amisure\P4SApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table(name="s1_evaluationelementcategory")
 * @ORM\Entity()
 */
class EvaluationElementCategory
{

	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $name;

	/**
	 *
	 * @var Collection @ORM\OneToMany(targetEntity="Amisure\P4SApiBundle\Entity\EvaluationElement", mappedBy="category", cascade={"persist"})
	 */
	private $elements;

	public function __construct($name = '')
	{
		$this->setName($name);
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

	public function getName()
	{
		return $this->name;
	}

	public function setName($name)
	{
		$this->name = $name;
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
		$evaluationElement->setCategory($this);
		$this->elements->add($evaluationElement);
		return $this;
	}

	public function removeElement(\Amisure\P4SApiBundle\Entity\EvaluationElement $element)
	{
		$this->elements->removeElement($element);
		return $this;
	}
}

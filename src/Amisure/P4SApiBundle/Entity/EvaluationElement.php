<?php
namespace Amisure\P4SApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table(name="s1_evaluationelement")
 * @ORM\Entity()
 */
class EvaluationElement
{

	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\Column(type="text")
	 */
	private $description;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $value;

	/**
	 * @ORM\ManyToOne(targetEntity="Amisure\P4SApiBundle\Entity\Evaluation", inversedBy="elements")
	 * @ORM\JoinColumn(nullable=false)
	 *
	 * @var Evaluation
	 */
	private $evaluation;

	/**
	 * @ORM\ManyToOne(targetEntity="Amisure\P4SApiBundle\Entity\EvaluationElementCategory", inversedBy="elements")
	 * @ORM\JoinColumn(nullable=false)
	 *
	 * @var EvaluationElementCategory
	 */
	private $category;

	public function __construct($cat = '', $description = '', $value = '')
	{
		$this->setDescription($description);
		$this->setValue($value);
		if ($cat instanceof EvaluationElementCategory) {
			$this->setCategory($cat);
			$cat->addElement($this);
		}
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

	public function getDescription()
	{
		return $this->description;
	}

	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}

	public function getValue()
	{
		return $this->value;
	}

	public function setValue($value)
	{
		$this->value = $value;
		return $this;
	}

	public function getEvaluation()
	{
		return $this->evaluation;
	}

	public function setEvaluation(\Amisure\P4SApiBundle\Entity\Evaluation $evaluation)
	{
		$this->evaluation = $evaluation;
		return $this;
	}

	public function getCategory()
	{
		return $this->category;
	}

	public function setCategory(EvaluationElementCategory $category)
	{
		$this->category = $category;
		return $this;
	}
}

<?php
namespace Amisure\P4SApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table(name="p4s_evaluationmodelcategory")
 * @ORM\Entity()
 */
class EvaluationModelCategory
{

	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 *
	 * @var integer
	 */
	private $id;

	/**
	 *
	 * @var string @ORM\Column(name="category_id", type="string", length=255, nullable=true)
	 */
	private $categoryId;

	/**
	 * Logical name : VAR_DISCR
	 *
	 * @ORM\Column(type="string", length=255)
	 *
	 * @var string
	 */
	private $code;

	/**
	 * @ORM\Column(type="text")
	 *
	 * @var string
	 */
	private $label;

	/**
	 * @ORM\OneToMany(targetEntity="Amisure\P4SApiBundle\Entity\EvaluationModelItem", mappedBy="category", cascade={"persist", "remove"})
	 *
	 * @var Collection
	 */
	private $items;

	/**
	 * @ORM\ManyToOne(targetEntity="Amisure\P4SApiBundle\Entity\EvaluationModel", inversedBy="categories")
	 * @ORM\JoinColumn(nullable=false)
	 *
	 * @var \Amisure\P4SApiBundle\Entity\Evaluation
	 */
	private $evaluation;

	public function __construct($label = '', $code = '', $categoryId = -1, $id = -1)
	{
		$this->setId($id);
		$this->setLabel($label);
		$this->setCategoryId($categoryId);
		$this->setCode($code);
		$this->items = new ArrayCollection();
	}

	public function getId()
	{
		return $this->id;
	}

	/**
	 *
	 * @param integer $id        	
	 * @return \Amisure\P4SApiBundle\Entity\EvaluationModelCategory
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	public function getCategoryId()
	{
		return $this->categoryId;
	}

	public function setCategoryId($categoryId)
	{
		$this->categoryId = $categoryId;
		return $this;
	}

	public function getCode()
	{
		return $this->code;
	}

	/**
	 *
	 * @param string $code        	
	 * @return \Amisure\P4SApiBundle\Entity\EvaluationModelCategory
	 */
	public function setCode($code)
	{
		if (empty($code) && ! empty($this->label)) {
			$code = mb_strtolower($this->label);
			$code = preg_replace('!(?:^|\s|[_-])(le|la|les|un|une|des|de|à|sa|son|ses|ces)(?:$|\s|[_-])!i', '-', $code);
			$code = preg_replace('!([àâä])!i', 'a', $code);
			$code = preg_replace('!([éèêë])!i', 'e', $code);
			$code = preg_replace('!([îï])!i', 'i', $code);
			$code = preg_replace('!([ôö])!i', 'o', $code);
			$code = preg_replace('!([ùüû])!i', 'u', $code);
			$code = preg_replace('!ÿ!i', 'y', $code);
			$code = preg_replace('!ç!i', 'c', $code);
			$code = preg_replace('![/@\'=_ -]!i', '-', $code);
			$code = preg_replace('![&~"#|`^()+{}[\]$£¤*µ%§\!:;\\\.,?°]!i', '', $code);
			$code = preg_replace('!-(d|l|m|qu|t)-!i', '-', $code);
			$code = preg_replace('!^(d|l|m|qu|t)-!i', '-', $code);
			$code = preg_replace('!-(d|l|m|qu|t)&!i', '-', $code);
			$code = preg_replace('!-{2,}!i', '-', $code);
			$code = preg_replace('!^-!i', '', $code);
			$code = preg_replace('!-$!i', '', $code);
		}
		$this->code = $code;
		return $this;
	}

	public function getLabel()
	{
		return $this->label;
	}

	public function setLabel($label)
	{
		$this->label = $label;
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

	/**
	 *
	 * @param ArrayCollection $items        	
	 * @return \Amisure\P4SApiBundle\Entity\EvaluationModelCategory
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
	 * Add a new item
	 *
	 * @param \Amisure\P4SApiBundle\Entity\EvaluationModelItem|string $item
	 *        	Evaluation item, or description of the evaluation item
	 * @param string $value
	 *        	Value of the evaluation item if the evaluation item itself is not previsously provided
	 * @see \Amisure\P4SApiBundle\Entity\EvaluationModelItem
	 * @return \Amisure\P4SApiBundle\Entity\EvaluationModelCategory
	 */
	public function addItem($item, $responseType = '')
	{
		$evaluationItem = $item;
		if (is_string($item)) {
			$evaluationItem = new EvaluationModelItem($item, $responseType);
		}
		$evaluationItem->setCategory($this);
		if (- 1 == $evaluationItem->getItemId()) {
			$evaluationItem->setItemId($this->items->count());
		}
		$this->items->add($evaluationItem);
		return $this;
	}

	/**
	 *
	 * @param \Amisure\P4SApiBundle\Entity\EvaluationItem $item        	
	 * @return \Amisure\P4SApiBundle\Entity\EvaluationModelCategory
	 */
	public function removeItem(EvaluationModelItem $item)
	{
		$this->items->removeElement($item);
		return $this;
	}

	public function getItemById($itemId)
	{
		foreach ($this->items as $item) {
			if ($item->getItemId() == $itemId) {
				return $item;
			}
		}
		return null;
	}

	public function getResponseById($itemId, $responseId)
	{
		foreach ($this->items as $item) {
			if ($item->getItemId() == $itemId) {
				foreach ($item->getResponses() as $response) {
					if ($response->getResponseId() == $responseId) {
						return $response;
					}
				}
			}
		}
		return null;
	}

	public function getEvaluation()
	{
		return $this->evaluation;
	}

	/**
	 *
	 * @param \Amisure\P4SApiBundle\Entity\EvaluationModel $evaluation        	
	 * @return \Amisure\P4SApiBundle\Entity\EvaluationModelCategory
	 */
	public function setEvaluation(EvaluationModel $evaluation)
	{
		$this->evaluation = $evaluation;
		return $this;
	}
}

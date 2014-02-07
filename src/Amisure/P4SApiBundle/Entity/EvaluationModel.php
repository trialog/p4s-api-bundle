<?php
namespace Amisure\P4SApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Evaluation
 *
 * @ORM\Table(name="p4s_evaluationmodel")
 * @ORM\Entity(repositoryClass="Amisure\P4SApiBundle\Entity\EvaluationModelRepository")
 */
class EvaluationModel
{

	/**
	 *
	 * @var integer @ORM\Column(name="id", type="integer")
	 *      @ORM\Id
	 *      @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * Logical name : AGGIR, MNA
	 *
	 * @ORM\Column(type="string", length=255, unique=true)
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
	 * @var string @ORM\Column(type="text")
	 */
	private $object;
	
	/**
	 * Label of this evaluation model
	 * @var string @ORM\Column(type="text")
	 */
	private $label;

	/**
	 * Description of this evaluation model
	 * @var string @ORM\Column(type="text", nullable=true)
	 */
	private $description;

	/**
	 *
	 * @var Collection @ORM\OneToMany(targetEntity="Amisure\P4SApiBundle\Entity\EvaluationModelCategory", mappedBy="evaluation", cascade={"persist"})
	 */
	private $categories;

	public function __construct($code = '', $label = '', $description = '', $id = -1)
	{
		$this->setId($id);
		$this->setCode($code);
		$this->setLabel($label);
		$this->setDescription($description);
		$this->date = new \DateTime();
		$this->lastUpdate = new \DateTime();
		$this->categories = new ArrayCollection();
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

	public function getLabel()
	{
		return $this->label;
	}

	public function setLabel($label)
	{
		$this->label = $label;
		return $this;
	}

	public function setDescription($description)
	{
		$this->description = $description;
		
		return $this;
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function getCategories()
	{
		return $this->categories->toArray();
	}

	public function getCategoryCollection()
	{
		return $this->categories;
	}

	/**
	 *
	 * @param ArrayCollection $categories        	
	 * @return \Amisure\P4SApiBundle\Entity\Evaluation
	 */
	public function setCategories($categories)
	{
		if (is_array($categories)) {
			$categories = new ArrayCollection($categories);
		}
		$this->categories = $categories;
		return $this;
	}

	/**
	 * Add a new category that list items
	 *
	 * @param \Amisure\P4SApiBundle\Entity\EvaluationItemCategory|string $category
	 *        	Evaluation item category, or name of the evaluation item category
	 * @see \Amisure\P4SApiBundle\Entity\EvaluationItemCategory
	 * @return \Amisure\P4SApiBundle\Entity\Evaluation
	 */
	public function addCategory($category)
	{
		$evaluationCategory = $category;
		if (is_string($category)) {
			$evaluationCategory = new EvaluationModelCategory($category);
		}
		$evaluationCategory->setEvaluation($this);
		if (- 1 == $evaluationCategory->getCategoryId()) {
			$evaluationCategory->setCategoryId($this->categories->count());
		}
		$this->categories->add($evaluationCategory);
		return $this;
	}

	public function removeCategory(EvaluationModelCategory $category)
	{
		$this->categories->removeElement($category);
		return $this;
	}

	public function getCategoryById($categoryId)
	{
		foreach ($this->categories as $cat) {
			if ($cat->getCategoryId() == $categoryId) {
				return $cat;
			}
		}
		return null;
	}

	public function getItemById($categoryId, $itemId)
	{
		foreach ($this->categories as $cat) {
			if ($cat->getCategoryId() == $categoryId) {
				foreach ($cat->getItems() as $item) {
					if ($item->getItemId() == $itemId) {
						return $item;
					}
				}
			}
		}
		return null;
	}

	public function getResponseById($categoryId, $itemId, $responseId)
	{
		foreach ($this->categories as $cat) {
			if ($cat->getCategoryId() == $categoryId) {
				foreach ($cat->getItems() as $item) {
					if ($item->getItemId() == $itemId) {
						foreach ($item->getResponses() as $response) {
							if ($response->getResponseId() == $responseId) {
								return $response;
							}
						}
					}
				}
			}
		}
		return null;
	}

	/**
	 *
	 * @param Evaluation $evaluation        	
	 * @return \Amisure\DataBrokerBundle\Entity\EvaluationModel
	 */
	public function mergeWithEvaluation(Evaluation $evaluation)
	{
		$this->folderSectionId = $evaluation->getFolderSectionId();
		$this->state = $evaluation->getState();
		$this->beneficiaryId = $evaluation->getBeneficiaryId();
		$this->evaluatorId = $evaluation->getEvaluatorId();
		$this->appId = $evaluation->getAppId();
		$this->date = $evaluation->getDate();
		$this->lastUpdate = $evaluation->getLastUpdate();
		$cats = $this->getCategories();
		$newCats = array();
		$this->setCategories($newCats);
		$nbOfCat = count($cats);
		for ($i = 0; $i < $nbOfCat; $i ++) {
			$newCat = new EvaluationModelCategory($cats[$i]->getLabel(), $cats[$i]->getCode(), $cats[$i]->getCategoryId(), $cats[$i]->getId());
			$items = $cats[$i]->getItems();
			$nbOfItem = count($items);
			for ($j = 0; $j < $nbOfItem; $j ++) {
				$newItem = new EvaluationModelItem($items[$j]->getLabel(), $items[$j]->getResponseType(), $items[$j]->getItemId(), $items[$j]->getId());
				$responses = $items[$j]->getResponses();
				$nbOfResponse = count($responses);
				$relatedItem = $evaluation->getItemById($cats[$i]->getCategoryId(), $items[$j]->getItemId());
				for ($k = 0; $k < $nbOfResponse; $k ++) {
					$newResponse = new EvaluationModelItemResponse($responses[$k]->getValue(), $responses[$k]->getLabel(), $responses[$k]->getType(), $responses[$k]->getResponseId(), $responses[$k]->getId());
					if (null != $relatedItem) {
						$relatedResponse = $relatedItem->getResponseById($responses[$k]->getResponseId());
						if (null != $relatedResponse) {
							$newResponse->setValue($relatedResponse->getValue());
							$newResponse->selected = $relatedResponse->getSelected();
						}
					}
					$newItem->addResponse($newResponse);
				}
				$newCat->addItem($newItem);
			}
			$newCats[] = $newCat;
		}
		$this->setCategories($newCats);
		$this->setId($evaluation->getId());
		return $this;
	}

	public static function fromJson($data)
	{
		$element = new EvaluationModel(@$data['code'], @$data['label'], @$data['description'], @$data['id']);
		$element->setFolderSectionId(@$data['folderSectionId']);
		$element->setBeneficiaryId(@$data['beneficiaryId']);
		$element->setEvaluatorId(@$data['evaluatorId']);
		$element->setAppId(@$data['appId']);
		$element->setState(@$data['state']);
		$element->setDate(@$data['date']);
		$element->setLastUpdate(@$data['lastUpdate']);
		if (array_key_exists('categories', $data) && is_array($data['categories']) && ! empty($data['categories'])) {
			foreach ($data['categories'] as $cat) {
				$newCategory = new EvaluationModelCategory(@$cat['label'], @$cat['code'], @$cat['categoryId'], @$cat['id']);
				if (array_key_exists('items', $cat) && is_array($cat['items']) && ! empty($cat['items'])) {
					foreach ($cat['items'] as $item) {
						$newItem = new EvaluationModelItem(@$item['label'], @$item['responseType'], @$item['itemId'], @$item['id']);
						if (array_key_exists('responses', $item) && is_array($item['responses']) && ! empty($item['responses'])) {
							foreach ($item['responses'] as $response) {
								$newResponse = new EvaluationModelItemResponse(@$response['value'], @$response['label'], @$response['type'], @$response['responseId'], @$response['id'], @$response['selected']);
								$newItem->addResponse($newResponse);
							}
						}
						$newCategory->addItem($newItem);
					}
				}
				$element->addCategory($newCategory);
			}
		}
		return $element;
	}

	public function getObject()
	{
		return $this->object;
	}

	public function setObject($object)
	{
		$this->object = $object;
		return $this;
	}
	
}

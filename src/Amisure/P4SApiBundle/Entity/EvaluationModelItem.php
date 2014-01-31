<?php
namespace Amisure\P4SApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * EvaluationModelItems
 *
 * @ORM\Table(name="p4s_evaluationmodelitem")
 * @ORM\Entity
 */
class EvaluationModelItem
{

	const ResponseTypeMultiple = 'MULTIPLE_CHOICE';

	const ResponseTypeSingle = 'SINGLE_CHOICE';

	const ResponseTypeInput = 'INPUT_CHOICE';

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
	 * @var string @ORM\Column(name="item_id", type="string", length=255, nullable=true)
	 */
	private $itemId;

	/**
	 *
	 * @var string @ORM\Column(type="text")
	 */
	private $label;

	/**
	 *
	 * @var string @ORM\Column(name="response_type", type="string", length=255)
	 */
	private $responseType;

	/**
	 * @ORM\ManyToOne(targetEntity="Amisure\P4SApiBundle\Entity\EvaluationModelCategory", inversedBy="items")
	 * @ORM\JoinColumn(nullable=false)
	 *
	 * @var \Amisure\P4SApiBundle\Entity\EvaluationModelCategory
	 */
	private $category;

	/**
	 * @ORM\OneToMany(targetEntity="Amisure\P4SApiBundle\Entity\EvaluationModelItemResponse", mappedBy="item", cascade={"persist", "remove"})
	 *
	 * @var Collection
	 */
	private $responses;

	public function __construct($label = '', $responseType = EvaluationModelItem::ResponseTypeInput, $itemId = -1, $id = -1)
	{
		$this->setLabel($label);
		$this->setResponseType($responseType);
		$this->setItemId($itemId);
		$this->setId($id);
		$this->responses = new ArrayCollection();
	}

	public function getId()
	{
		return $this->id;
	}

	/**
	 *
	 * @return \Amisure\P4SApiBundle\Entity\EvaluationModelItem
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 *
	 * @return \Amisure\DataBrokerBundle\Entity\EvaluationModelItem
	 */
	public function setItemId($itemId)
	{
		$this->itemId = $itemId;
		return $this;
	}
	
	/**
	 *
	 * @return string
	 */
	public function getItemId()
	{
		return $this->itemId;
	}

	/**
	 *
	 * @return \Amisure\P4SApiBundle\Entity\EvaluationModelItem
	 */
	public function setLabel($label)
	{
		$this->label = $label;
		return $this;
	}

	/**
	 *
	 * @param string $label        	
	 * @return \Amisure\P4SApiBundle\Entity\EvaluationModelItem
	 */
	public function setItemText($label)
	{
		return $this->setLabel($label);
	}

	public function getLabel()
	{
		return $this->label;
	}

	/**
	 *
	 * @return string
	 */
	public function getItemText()
	{
		return $this->getLabel();
	}

	/**
	 *
	 * @return \Amisure\P4SApiBundle\Entity\EvaluationModelItem
	 */
	public function setResponseType($responseType)
	{
		$this->responseType = $responseType;
		return $this;
	}

	/**
	 *
	 * @return \Amisure\P4SApiBundle\Entity\EvaluationModelItem
	 */
	public function setItemType($responseType)
	{
		return $this->setResponseType($responseType);
		return $this;
	}

	public function getResponseType()
	{
		return $this->responseType;
	}

	/**
	 *
	 * @return string
	 */
	public function getItemType()
	{
		return $this->getResponseType();
	}

	public function getCategory()
	{
		return $this->category;
	}

	/**
	 *
	 * @param \Amisure\P4SApiBundle\Entity\EvaluationModelCategory $evaluation        	
	 * @return \Amisure\P4SApiBundle\Entity\EvaluationModelItem
	 */
	public function setCategory(EvaluationModelCategory $category)
	{
		$this->category = $category;
		return $this;
	}

	public function getResponses()
	{
		return $this->responses->toArray();
	}

	public function getSelectedResponses()
	{
		if (null == $this->responses || $this->responses->isEmpty()) {
			return null;
		}
		$responses = array();
		foreach ($this->responses as $response) {
			if (null != $response && true === $response->getSelected()) {
				$responses[] = $response;
			}
		}
		return $responses;
	}

	public function getResponseById($responseId)
	{
		foreach ($this->responses as $response) {
			if ($response->getResponseId() == $responseId) {
				return $response;
			}
		}
		return null;
	}

	public function getResponseCollection()
	{
		return $this->responses;
	}

	public function setResponses($responses)
	{
		if (is_array($responses)) {
			$responses = new ArrayCollection($responses);
		}
		$this->responses = $responses;
		return $this;
	}

	/**
	 * Add a new item response
	 *
	 * @param \Amisure\P4SApiBundle\Entity\EvaluationModelItemResponse|string $item
	 *        	Evaluation item, or description of the evaluation item
	 * @param string $label
	 *        	Label of the evaluation item response if the evaluation item response itself is not previsously provided
	 * @param string $type
	 *        	Type of the evaluation item response if the evaluation item response itself is not previsously provided
	 * @see \Amisure\P4SApiBundle\Entity\EvaluationModelItem
	 * @return \Amisure\P4SApiBundle\Entity\EvaluationModelItem
	 */
	public function addResponse($response, $label = '', $type = EvaluationModelItemResponse::TypeString)
	{
		$evaluationResponse = $response;
		if (is_string($response)) {
			$evaluationResponse = new EvaluationModelItem($response, $label, $type);
		}
		$evaluationResponse->setItem($this);
		if (- 1 == $evaluationResponse->getResponseId()) {
			$evaluationResponse->setResponseId($this->responses->count());
		}
		$this->responses->add($evaluationResponse);
		return $this;
	}

	/**
	 *
	 * @param \Amisure\P4SApiBundle\Entity\EvaluationModelItemResponse $item        	
	 * @return \Amisure\P4SApiBundle\Entity\EvaluationModelItem
	 */
	public function removeResponse(\Amisure\P4SApiBundle\Entity\EvaluationModelItemResponse $response)
	{
		$this->responses->removeElement($response);
		return $this;
	}
}
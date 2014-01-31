<?php
namespace Amisure\P4SApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * EvaluationItem
 *
 * @ORM\Table(name="p4s_evaluationitem")
 * @ORM\Entity
 */
class EvaluationItem
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
	 * @ORM\ManyToOne(targetEntity="Amisure\P4SApiBundle\Entity\Evaluation", inversedBy="items")
	 *
	 * @var \Amisure\P4SApiBundle\Entity\Evaluation
	 */
	private $evaluation;

	/**
	 * @ORM\OneToMany(targetEntity="Amisure\P4SApiBundle\Entity\EvaluationItemResponse", mappedBy="item", cascade={"persist", "remove"})
	 *
	 * @var Collection
	 */
	private $responses;

	public function __construct($values = array(), $id=-1)
	{
		$this->setId($id);
		$this->responses = new ArrayCollection();
		if (null != $values) {
			if (is_array($values) && ! empty($values)) {
				foreach ($values as $value) {
					$this->addResponse(new EvaluationItemResponse($value));
				}
			}
			elseif ('' != $values) {
				$this->addResponse(new EvaluationItemResponse($values));
			}
		}
	}

	public function getId()
	{
		return $this->id;
	}

	/**
	 *
	 * @return \Amisure\P4SApiBundle\Entity\EvaluationItem
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	public function getEvaluation()
	{
		return $this->evaluation;
	}

	/**
	 *
	 * @return \Amisure\P4SApiBundle\Entity\EvaluationItem
	 */
	public function setEvaluation($evaluation)
	{
		$this->evaluation = $evaluation;
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
		foreach($this->responses AS $response) {
			if (null != $response && true == $response->selected) {
				$responses[] = $response;
			}
		}
		return $responses;
	}

	public function getResponseCollection()
	{
		return $this->responses;
	}

	public function getResponseById($id)
	{
		foreach ($this->responses as $response) {
			if ($response->getId() == $id) {
				return $response;
			}
		}
		return null;
	}

	/**
	 *
	 * @return \Amisure\P4SApiBundle\Entity\EvaluationItem
	 */
	public function setResponses($responses)
	{
		if (is_array($responses)) {
			$responses = new ArrayCollection($responses);
		}
		$this->responses = $responses;
		return $this;
	}

	/**
	 * Add a new response to that item
	 *
	 * @param \Amisure\P4SApiBundle\Entity\EvaluationItemResponse|string $response
	 *        	Item response, or value of the item response
	 * @see \Amisure\P4SApiBundle\Entity\EvaluationItemResponse
	 * @return \Amisure\P4SApiBundle\Entity\EvaluationItem
	 */
	public function addResponse($response)
	{
		$element = $response;
		if (is_string($response)) {
			$element = new EvaluationItemResponse($response);
		}
		$element->setItem($this);
		$this->responses->add($element);
		return $this;
	}

	public function removeResponse(EvaluationItemResponse $response)
	{
		$this->responses->removeElement($response);
		return $this;
	}
}
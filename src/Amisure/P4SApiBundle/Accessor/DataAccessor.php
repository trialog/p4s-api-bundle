<?php
namespace Amisure\P4SApiBundle\Accessor;

use Amisure\P4SApiBundle\Entity\User\BeneficiaryUser;
use Amisure\P4SApiBundle\Entity\User\OrganizationUser;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Session\Session;
use Amisure\P4SApiBundle\Entity\User\UserConstants;
use Amisure\P4SApiBundle\Entity\Event;
use Amisure\P4SApiBundle\Accessor\Api\ADataAccessor;
use Amisure\P4SApiBundle\Entity\EventRecurrence;

/**
 * Accessor for the P4S data
 *
 * @author Olivier Maridat (Trialog)
 */
class DataAccessor extends ADataAccessor
{

	private $client;

	private $securityCtx;

	private $em;

	private $beneficiaryList;

	public function __construct($client, $securityCtx, EntityManager $em)
	{
		$this->client = $client;
		$this->securityCtx = $securityCtx;
		$this->em = $em;
		$this->beneficiaryList = null;
	}

	public function getBeneficiaryList()
	{
		$this->beneficiaryList = $this->em->getRepository('Amisure\P4SApiBundle\Entity\User\BeneficiaryUser')->findByRelatedBeneficiaries($this->securityCtx->getToken()
			->getUser());
		return $this->beneficiaryList;
	}

	public function getBeneficiarySmallProfile($beneficiaryId)
	{
		if (null == $this->beneficiaryList) {
			echo 'Reload it!';
			$this->getBeneficiaryList();
		}
		foreach ($this->beneficiaryList as $k => $profile) {
			if ($beneficiaryId == $profile->getId()) {
				return $this->beneficiaryList[$k];
			}
		}
		return null;
	}

	public function getBeneficiaryProfile($beneficiaryId)
	{
		if (empty($beneficiaryId)) {
			throw new \Exception('Unknown beneficiary\'s profile with these given criteria');
		}
		$profile = $this->em->getRepository('Amisure\P4SApiBundle\Entity\User\BeneficiaryUser')->find($beneficiaryId);
		return $profile;
	}

	public function getOrganizationUserProfile($criteria = array())
	{
		if (empty($criteria)) {
			throw new \Exception('Unknown beneficiary\'s contact with these empty criteria');
		}
		if (array_key_exists('organizationType', $criteria) && array_key_exists('beneficiaryId', $criteria)) {
			$profile = $this->em->getRepository('Amisure\P4SApiBundle\Entity\User\OrganizationUser')->findOrganizationBy($criteria['beneficiaryId'], $criteria['organizationType']);
		}
		elseif (array_key_exists('id', $criteria)) {
			$profile = $this->em->getRepository('Amisure\P4SApiBundle\Entity\User\OrganizationUser')->find($criteria['id']);
		}
		else {
			throw new \Exception('Unknown beneficiary\'s contact with these given criteria');
		}
		return $profile;
	}

	public function getBeneficiaryEvent($criteria = array())
	{
		if (empty($criteria) || (! array_key_exists('id', $criteria) && ! array_key_exists('beneficiaryId', $criteria))) {
			throw new \Exception('Unknown beneficiary\'s event with these given criteria');
		}
		$event = null;
		// Find most recent evaluation of this beneficiary
		if (array_key_exists('id', $criteria) && - 1 != $criteria['id']) {
			$event = $this->em->getRepository('AmisureP4SApiBundle:Event')->find($criteria['id']);
		}
		return $event;
	}

	public function getBeneficiaryEvents($criteria = array())
	{
		if (empty($criteria) || ! array_key_exists('beneficiaryId', $criteria)) {
			throw new \Exception('Unknown beneficiary\'s event list with these given criteria');
		}
		$events = $this->em->getRepository('AmisureP4SApiBundle:Event')->findByBeneficiary($criteria['beneficiaryId'], @$criteria['endDate'], @$criteria['startDate']);
		return $events;
	}

	public function updateBeneficiaryEvent($event)
	{
		// Create sub-events
		$recurrence = $event->getRecurrence();
		if (null != $recurrence && $recurrence->getNb() > 1) {
			// Remove previous events
			$childs = $event->getChilds();
			if (null == $childs && $childs->count() <= 0 && null != $event->getParent() && null != $event->getParent()->getChilds() && $event->getParent()
				->getChilds()
				->count() >= 0) {
				$childs = $parent->getChilds();
			}
			$childsSize = $childs->count();
			$stillSomeChild = true;
			$participants = $event->getParticipants();
			$nbEvent = $event->getRecurrence()->getNb();
			for ($i = 0; $i < ($nbEvent-1); $i ++) {
				// Update childs (if any)
				if ($stillSomeChild && $i < $childsSize) {
					$subEvent = $childs->get($i);
					$subEvent->setObject('[' . $event->getCategory() . '] ' . $event->getObject());
				}
				// Create new childs
				else {
					$stillSomeChild = false;
					$subEvent = new Event('[' . $event->getCategory() . '] ' . $event->getObject());
				}
				foreach ($participants as $participant) {
					$subEvent->addParticipant($participant);
				}
				$diff = '+' . (($i+1) * $event->getRecurrence()->getFrequency()) . ' ' . $event->getRecurrence()->getType();
				$dateStart = clone $event->getDateStart();
				$dateEnd = clone $event->getDateEnd();
				$subEvent->setDateStart($dateStart->modify($diff));
				$subEvent->setDateEnd($dateEnd->modify($diff));
				$subEvent->setRecurrence($recurrence);
				$event->addChild($subEvent);
			}
			// Remove remaining childs (if any)
			if ($stillSomeChild) {
				for ($i=$i; $i < $childsSize; $i ++) {
					$child = $childs->get($i);
					$event->removeChild($i);
					$this->em->remove($child);
					$childsSize--;
					$i--;
				}
			}
		}
		$this->em->persist($event);
		$this->em->flush();
		return $event->getId();
	}

	public function removeBeneficiaryEvent(Event $event)
	{
		$this->em->remove($event);
		$this->em->flush();
		return true;
	}

	public function getBeneficiaryEvaluation($criteria = array())
	{
		if (empty($criteria) || (! array_key_exists('id', $criteria) && ! array_key_exists('beneficiaryId', $criteria))) {
			throw new \Exception('Unknown beneficiary\'s evaluation with these given criteria');
		}
		$evaluation = null;
		$params = array();
		$orderBy = array();
		if (UserConstants::CG != $this->securityCtx->getToken()
			->getUser()
			->getOrganizationType()) {
			$params['finished'] = true;
		}
		// Find most recent evaluation of this beneficiary
		if (array_key_exists('beneficiaryId', $criteria) && (! array_key_exists('id', $criteria) || - 1 == $criteria['id'])) {
			$params['beneficiaryId'] = $criteria['beneficiaryId'];
			$orderBy['evaluationDate'] = 'DESC';
		}
		// Given evaluation
		elseif (array_key_exists('id', $criteria) && - 1 != $criteria['id']) {
			$params['id'] = $criteria['id'];
		}
		$evaluation = $this->em->getRepository('AmisureP4SApiBundle:Evaluation')->findOneBy($params, $orderBy);
		return $evaluation;
	}

	public function getBeneficiaryEvaluations($criteria = array())
	{
		if (empty($criteria) || ! array_key_exists('beneficiaryId', $criteria)) {
			throw new \Exception('Unknown beneficiary\'s evaluation list with these given criteria');
		}
		$evaluations = null;
		$params = array();
		$params['beneficiaryId'] = $criteria['beneficiaryId'];
		if (UserConstants::CG != $this->securityCtx->getToken()
			->getUser()
			->getOrganizationType()) {
			$params['finished'] = true;
		}
		$evaluations = $this->em->getRepository('AmisureP4SApiBundle:Evaluation')->findBy($params, array(
			'evaluationDate' => 'DESC'
		));
		return $evaluations;
	}

	public function updateBeneficiaryEvaluation($evaluation)
	{
		$this->em->persist($evaluation);
		$this->em->flush();
		return $evaluation->getId();
	}

	public function findOrganizations($criteria = array())
	{
		if (empty($criteria) || (! array_key_exists('organizationType', $criteria))) {
			throw new \Exception('Unknown organisations with these given criteria');
		}
		$organizations = null;
		if (array_key_exists('beneficiaryId', $criteria)) {
			$organizations = $this->em->getRepository('AmisureP4SApiBundle:Organization')->findByBeneficiary($criteria['beneficiaryId'], $criteria['organizationType']);
		}
		else 
			if (array_key_exists('departementCode', $criteria)) {
				$organizations = $this->em->getRepository('AmisureP4SApiBundle:Organization')->findByDepartement($criteria['organizationType'], $criteria['departementCode']);
			}
			else {
				$organizations = $this->em->getRepository('AmisureP4SApiBundle:Organization')->findBy(array(
					'type' => $criteria['organizationType']
				), array(
					'name' => 'ASC'
				));
			}
		return $organizations;
	}
}
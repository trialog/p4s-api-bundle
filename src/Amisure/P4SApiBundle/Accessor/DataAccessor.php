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
use Amisure\P4SApiBundle\Accessor\Api\ResponseHelper;
use Guzzle\Http\Client;
use Amisure\P4SApiBundle\Entity\Organization;

/**
 * Accessor for the P4S data
 *
 * @author Olivier Maridat (Trialog)
 */
class DataAccessor extends ADataAccessor
{

	private $client;

	private $securityCtx;

	private $session;

	private $em;

	private $beneficiaryList;

	public function __construct(Client $client, Session $session, EntityManager $em)
	{
		$this->client = $client;
		$this->session = $session;
		$this->em = $em;
		
		$this->beneficiaryList = null;
		$this->client->setDefaultOption('query/access_token', $this->session->get('access_token'));
	}

	public function updateConfig()
	{
		$this->client->setDefaultOption('query/access_token', $this->session->get('access_token'));
	}

	public function getBeneficiaryList($criteria = array(), $filter = array())
	{
		if (null == $filter) {
			$filter = array();
		}
		if (! array_key_exists('profile_type', $filter)) {
			$filter['profile_type'] = 'FULL';
		}
		if (! array_key_exists('exception', $filter)) {
			$filter['exception'] = true;
		}
		$data = array();
		try {
			$request = $this->client->get('beneficiaries', array(), array(
				'query' => array(
					'profile_type' => $filter['profile_type']
				)
			));
			$response = $request->send()->json();
			if (ResponseHelper::OK == $response['status']) {
				foreach ($response['beneficiaries'] as $beneficiary) {
					$data[] = BeneficiaryUser::fromJson($beneficiary);
				}
			}
			else {
				if ($filter['exception']) {
					throw new \Exception(@$response['message'] . @$response['data']['message'], ResponseHelper::toCode($response['status']));
				}
				else {
					return null;
				}
			}
		} catch (\Exception $e) {
			if ($filter['exception']) {
				throw new \Exception('Erreur lors de l\'appel au P4S : getBeneficiaryList()', ResponseHelper::toCode(ResponseHelper::UNKNOWN_ISSUE), $e);
			}
			else {
				return null;
			}
		}
		$this->beneficiaryList = $data;
		return $this->beneficiaryList;
	}

	public function getBeneficiarySmallProfile($beneficiaryId)
	{
		$data = null;
		try {
			$data = $this->getBeneficiary($beneficiaryId, 'MINIMAL');
		} catch (\Exception $e) {
			$data = new BeneficiaryUser(); // empty
		}
		return $data;
	}

	public function getBeneficiary($beneficiaryId, $profileType = 'FULL')
	{
		if (empty($beneficiaryId)) {
			throw new \Exception('Unknown beneficiary\'s profile with these given criteria');
		}
		
		$data = null;
		try {
			$request = $this->client->get('beneficiaries/' . $beneficiaryId, array(), array(
				'query' => array(
					'profile_type' => $profileType
				)
			));
			$response = $request->send()->json();
			if (ResponseHelper::OK == $response['status']) {
				$data = BeneficiaryUser::fromJson($response['beneficiary']);
			}
			else {
				throw new \Exception(@$response['message'] . @$response['data']['message'], ResponseHelper::toCode($response['status']));
			}
		} catch (\Exception $e) {
			throw new \Exception('Erreur lors de l\'appel au P4S : getBeneficiary()', ResponseHelper::toCode(ResponseHelper::UNKNOWN_ISSUE), $e);
		}
		return $data;
	}

	public function getBeneficiaryProfile($beneficiaryId)
	{
		return $this->getBeneficiary($beneficiaryId);
	}

	public function getOrganizationUserProfile($criteria = array())
	{
		if (empty($criteria)) {
			throw new \Exception('Unknown beneficiary\'s contact with these empty criteria');
		}
		
		$data = null;
		try {
			$request = null;
			if (array_key_exists('organizationType', $criteria) || array_key_exists('beneficiaryId', $criteria) || array_key_exists('id', $criteria)) {
				$request = $this->client->get('organizations/users', array(), array(
					'query' => $criteria
				));
			}
			else {
				throw new \Exception('Unknown beneficiary\'s contact with these given criteria');
			}
			$response = $request->send()->json();
			if (ResponseHelper::OK == $response['status']) {
				if (empty($response['data'])) {
					return null;
				}
				$data = array();
				foreach ($response['data'] as $user) {
					$data[] = OrganizationUser::fromJson($user);
				}
			}
			else {
				throw new \Exception($response['message'], ResponseHelper::toCode($response['status']));
			}
		} catch (\Exception $e) {
			throw new \Exception('Erreur lors de l\'appel au P4S : getOrganizationUserProfile()', ResponseHelper::toCode(ResponseHelper::UNKNOWN_ISSUE), $e);
		}
		return $data;
	}

	public function findOrganizations($criteria = array())
	{
		if (empty($criteria) || (! array_key_exists('organizationType', $criteria))) {
			throw new \Exception('Unknown organisations with these given criteria');
		}
		if (array_key_exists('departementCode', $criteria)) {
			$criteria['zipcode'] = $criteria['departementCode'] . '*';
		}
		
		$data = null;
		try {
			$request = $this->client->get('organizations', array(), array(
				'query' => $criteria
			));
			$response = $request->send()->json();
			if (ResponseHelper::OK == $response['status']) {
				$data = array();
				foreach ($response['data'] as $org) {
					$data[] = Organization::fromJson($org);
				}
			}
			else {
				throw new \Exception($response['message'], ResponseHelper::toCode($response['status']));
			}
		} catch (\Exception $e) {
			throw new \Exception('Erreur lors de l\'appel au P4S : findOrganizations()', ResponseHelper::toCode(ResponseHelper::UNKNOWN_ISSUE), $e);
		}
		return $data;
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
			for ($i = 0; $i < ($nbEvent - 1); $i ++) {
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
				$diff = '+' . (($i + 1) * $event->getRecurrence()->getFrequency()) . ' ' . $event->getRecurrence()->getType();
				$dateStart = clone $event->getDateStart();
				$dateEnd = clone $event->getDateEnd();
				$subEvent->setDateStart($dateStart->modify($diff));
				$subEvent->setDateEnd($dateEnd->modify($diff));
				$subEvent->setRecurrence($recurrence);
				$event->addChild($subEvent);
			}
			// Remove remaining childs (if any)
			if ($stillSomeChild) {
				for ($i = $i; $i < $childsSize; $i ++) {
					$child = $childs->get($i);
					$event->removeChild($i);
					$this->em->remove($child);
					$childsSize --;
					$i --;
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
		$params['finished'] = true;
		if (array_key_exists('finished', $criteria)) {
			$params['finished'] = $criteria['finished'];
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
		$params['finished'] = true;
		if (array_key_exists('finished', $criteria)) {
			$params['finished'] = $criteria['finished'];
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
}
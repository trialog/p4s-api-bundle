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
use Amisure\P4SApiBundle\Entity\Evaluation;
use Amisure\P4SApiBundle\Entity\EvaluationModel;
use Zumba\Util\JsonSerializer;

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

	public function findBeneficiaries($criteria = array(), $filter = array())
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

	public function getBeneficiaryList($criteria = array(), $filter = array())
	{
		return $this->findBeneficiaries($criteria, $filter);
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

	public function getBeneficiaryEvaluation($criteria = array(), $filter = array())
	{
		if (empty($criteria) || (! array_key_exists('id', $criteria)) && - 1 != $criteria['id']) {
			throw new \Exception('Unknown beneficiary\'s evaluation with these given criteria');
		}
		
		// -- Adapt criteria
		$id = $criteria['id'];
		$model = 'merge';
		if (! empty($filter)) {
			$criteria = array_merge($criteria, array(
				'filter' => $filter
			));
			if (array_key_exists('model', $filter)) {
				$model = $filter['model'];
			}
		}
		$data = null;
		try {
			// -- Find data
			$request = $this->client->get('evaluations/' . $id, array(), array(
				'query' => $criteria
			));
			$response = $request->send()->json();
			// - Wrong result
			if (ResponseHelper::OK != $response['status']) {
				throw new \Exception($response['message'], ResponseHelper::toCode($response['status']));
			}
			if (empty($response['data']) || ! array_key_exists('evaluation', $response['data']) || ! is_array($response['data']['evaluation']) || empty($response['data']['evaluation'])) {
				return null;
			}
			// - Good result
			$data = array();
			// Model available and requested
			if (array_key_exists('model', $response['data']) && (true === $model || 'separate' === $model)) {
				$data['model'] = EvaluationModel::fromJson($response['data']['model']);
			}
			// Evaluations available
			if ('merge' == $model || false === $model) {
				var_dump($response['data']['evaluation']['categories'][1]['items'][6]['responses']);
				echo __FILE__;
				$data = EvaluationModel::fromJson($response['data']['evaluation']);
			}
			elseif (false === $model) {
				$data = Evaluation::fromJson($response['data']['evaluation']);
			}
			else {
				$data['evaluation'] = Evaluation::fromJson($response['data']['evaluation']);
			}
		} catch (\Exception $e) {
			throw new \Exception('Erreur lors de l\'appel au P4S : getBeneficiaryEvaluation()', ResponseHelper::toCode(ResponseHelper::UNKNOWN_ISSUE), $e);
		}
		return $data;
	}

	public function findBeneficiaryEvaluations($criteria = array(), $filter = array())
	{
		if (empty($criteria) || ! array_key_exists('beneficiaryId', $criteria)) {
			throw new \Exception('Unknown beneficiary\'s evaluation list with these given criteria');
		}
		$model = true;
		if (! empty($filter)) {
			$criteria = array_merge($criteria, array(
				'filter' => $filter
			));
			if (array_key_exists('model', $filter)) {
				$model = $filter['model'];
			}
		}
		$data = null;
		try {
			// -- Find data
			$request = $this->client->get('evaluations', array(), array(
				'query' => $criteria
			));
			$response = $request->send()->json();
			// - Wrong result
			if (ResponseHelper::OK != $response['status']) {
				throw new \Exception($response['message'], ResponseHelper::toCode($response['status']));
			}
			if (empty($response['data']) || ! array_key_exists('evaluations', $response['data']) || ! is_array($response['data']['evaluations']) || empty($response['data']['evaluations'])) {
				return null;
			}
			// - Good result
			$data = array();
			// Model available and requested
			if (array_key_exists('model', $response['data']) && (true === $model || 'separate' === $model)) {
				$data['model'] = EvaluationModel::fromJson($response['data']['model']);
			}
			// Evaluations available
			$evaluations = array();
			$receivedEvaluations = $response['data']['evaluations'];
			foreach ($receivedEvaluations as $element) {
				if ('merge' == $model) {
					$evaluations[] = EvaluationModel::fromJson($element);
				}
				else {
					$evaluations[] = Evaluation::fromJson($element);
				}
			}
			$data['evaluations'] = $evaluations;
		} catch (\Exception $e) {
			throw new \Exception('Erreur lors de l\'appel au P4S : findBeneficiaryEvaluations()', ResponseHelper::toCode(ResponseHelper::UNKNOWN_ISSUE), $e);
		}
		return $data;
	}

	public function getBeneficiaryEvaluations($criteria = array())
	{
		return $this->findBeneficiaryEvaluations($criteria);
	}

	public function updateBeneficiaryEvaluation($evaluation)
	{
		$data = - 1;
		try {
			// -- Find data
// 			$serializer = new JsonSerializer();
			$evaluationStr = $evaluation; //$serializer->serialize($evaluation, false);
			$request = $this->client->post('evaluations', array(), $evaluationStr);
			$response = $request->send()->json();
			if (ResponseHelper::OK == $response['status']) {
				if (! array_key_exists('data', $response)) {
					return $data;
				}
				$data = $response['data'];
			}
			else {
				throw new \Exception($response['message'], ResponseHelper::toCode($response['status']));
			}
		} catch (\Exception $e) {
			throw new \Exception('Erreur lors de l\'appel au P4S : updateBeneficiaryEvaluation()', ResponseHelper::toCode(ResponseHelper::UNKNOWN_ISSUE), $e);
		}
		return $data;
	}
}
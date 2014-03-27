<?php
namespace Amisure\P4SApiBundle\Accessor;

use Amisure\P4SApiBundle\Entity\User\BeneficiaryUser;
use Amisure\P4SApiBundle\Entity\User\OrganizationUser;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Session\Session;
use Amisure\P4SApiBundle\Entity\User\UserConstants;
use Amisure\P4SApiBundle\Entity\Event;
use Amisure\P4SApiBundle\Entity\EventRecurrence;
use Amisure\P4SApiBundle\Accessor\Api\ADataAccessor;
use Amisure\P4SApiBundle\Accessor\Api\StatusConstants;
use Guzzle\Http\Client;
use Amisure\P4SApiBundle\Entity\Organization;
use Amisure\P4SApiBundle\Entity\Evaluation;
use Amisure\P4SApiBundle\Entity\EvaluationModel;
use Zumba\Util\JsonSerializer;
use Amisure\P4SApiBundle\Accessor\Api\LinkConstants;

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
		if (! isset($filter['exception'])) {
			$filter['exception'] = false;
		}
		$data = array();
		try {
			$request = $this->client->get('beneficiaries', array(), array(
				'query' => array(
					'profile_type' => $filter['profile_type']
				)
			));
			$response = $request->send()->json();
			if (StatusConstants::OK == $response['status']) {
				foreach ($response['beneficiaries'] as $beneficiary) {
					$data[] = BeneficiaryUser::fromJson($beneficiary);
				}
			}
			else {
				if ($filter['exception']) {
					throw new \Exception(@$response['message'] . @$response['data']['message'], StatusConstants::toCode($response['status']));
				}
				else {
					return null;
				}
			}
		} catch (\Exception $e) {
			if ($filter['exception']) {
				throw new \Exception('Erreur lors de la recherche de bénéficiaires.', StatusConstants::toCode($e->getCode()), $e);
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
			// - Wrong result
			if (null == $response || ! isset($response['status'])) {
				throw new \Exception(@$response['message'] . @$response['data']['message'], StatusConstants::UNKNWON_ERROR);
			}
			if (StatusConstants::OK != $response['status']) {
				throw new \Exception($response['message'], StatusConstants::toCode($response['status']));
			}
			$data = BeneficiaryUser::fromJson($response['beneficiary']);
		} catch (\Exception $e) {
			throw new \Exception('Erreur lors de la recherche du profil du bénéficiaire.', StatusConstants::toCode($e->getCode()), $e);
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
			if (StatusConstants::OK == $response['status']) {
				if (empty($response['data'])) {
					return null;
				}
				$data = array();
				foreach ($response['data'] as $user) {
					$data[] = OrganizationUser::fromJson($user);
				}
			}
			else {
				throw new \Exception($response['message'], StatusConstants::toCode($response['status']));
			}
		} catch (\Exception $e) {
			throw new \Exception('Erreur lors de l\'appel au P4S : getOrganizationUserProfile()', StatusConstants::toCode(StatusConstants::UNKNOWN_ERROR), $e);
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
			if (StatusConstants::OK == $response['status']) {
				$data = array();
				if (isset($response['data']) && ! empty($response['data'])) {
					foreach ($response['data'] as $org) {
						$data[] = Organization::fromJson($org);
					}
				}
			}
			else {
				throw new \Exception($response['message'], StatusConstants::toCode($response['status']));
			}
		} catch (\Exception $e) {
			throw new \Exception('Erreur lors de l\'appel au P4S : findOrganizations()', StatusConstants::toCode(StatusConstants::UNKNOWN_ERROR), $e);
		}
		return $data;
	}

	public function getBeneficiaryEvent($beneficiaryId, $eventId)
	{
		$data = null;
		try {
			$request = $this->client->get('beneficiaries/' . $beneficiaryId . '/agenda/' . $eventId);
			$response = $request->send()->json();
			if (null == $response || ! isset($response['status'])) {
				throw new \Exception($response['message'], StatusConstants::UNKNWON_ERROR);
			}
			if (StatusConstants::OK != $response['status']) {
				throw new \Exception($response['message'], StatusConstants::toCode($response['status']));
			}
			// - Got it!
			if (isset($response['agenda'])) {
				$result = $response['agenda'];
				$beneficiary = null;
				if (isset($result['beneficiary']) && is_array($result['beneficiary'])) {
					$beneficiaryData = $result['beneficiary'];
					$beneficiary = new BeneficiaryUser($beneficiaryData['id'], $beneficiaryData['title'], $beneficiaryData['first_name'], $beneficiaryData['last_name']);
				}
				if (isset($result['event']) && is_array($result['event'])) {
					$eventData = $result['event'];
					$event = new Event(@$eventData['object'], @$eventData['description'], @$eventData['place']);
					$event->setId(@$eventData['id']);
					$event->setDateStart(@$eventData['start_date']);
					$event->setDateEnd(@$eventData['end_date']);
					$event->addParticipant($beneficiary);
					if (isset($eventData['participants']) && is_array($eventData['participants'])) {
						foreach ($eventData['participants'] as $participantData) {
							$event->addParticipant(new OrganizationUser(@$participantData['id'], @$participantData['title'], @$participantData['first_name'], @$participantData['last_name'], '', '', '', '', '', @$participantData['org_type'], @$participantData['role'], @$participantData['sub_role']));
						}
					}
					$data = $event;
				}
			}
		} catch (\Exception $e) {
			throw new \Exception('Erreur lors de la recherche de l\'événement.', StatusConstants::toCode($e->getCode()), $e);
		}
		return $data;
	}

	public function getBeneficiaryEvents($criteria = array())
	{
		if (empty($criteria) || ! array_key_exists('beneficiaryId', $criteria)) {
			throw new \Exception('Unknown beneficiary\'s event list with these given criteria');
		}
		
		$data = null;
		try {
			$params = array();
			if (isset($criteria['startDate'])) {
				if ($criteria['startDate'] instanceof \DateTime) {
					$criteria['startDate'] = $criteria['startDate']->getTimestamp();
				}
				$params['start_date'] = $criteria['startDate'];
			}
			else {
				$params['start_date'] = time()-(60*60*24);
			}
			if (isset($criteria['endDate'])) {
				if ($criteria['endDate'] instanceof \DateTime) {
					$criteria['endDate'] = $criteria['endDate']->getTimestamp();
				}
				$params['end_date'] = $criteria['endDate'];
			}
			if (isset($criteria['participants'])) {
				$params['participants'] = $criteria['participants'];
			}
			
			$request = $this->client->get('beneficiaries/' . $criteria['beneficiaryId'] . '/agenda', array(), array(
				'query' => $params
			));
			$response = $request->send()->json();
			if (null == $response || ! isset($response['status'])) {
				throw new \Exception($response['message'], StatusConstants::UNKNWON_ERROR);
			}
			if (StatusConstants::OK != $response['status']) {
				throw new \Exception($response['message'], StatusConstants::toCode($response['status']));
			}
			// - Got it!
			if (isset($response['agenda'])) {
				$result = $response['agenda'];
				$beneficiary = null;
				if (isset($result['beneficiary']) && is_array($result['beneficiary'])) {
					$beneficiaryData = $result['beneficiary'];
					$beneficiary = new BeneficiaryUser(@$beneficiaryData['id'], @$beneficiaryData['title'], @$beneficiaryData['first_name'], @$beneficiaryData['last_name']);
				}
				$participants = array();
				if (isset($result['participants']) && is_array($result['participants'])) {
					foreach ($result['participants'] as $participantData) {
						if (! isset($participantData['id'])) {
							continue;
						}
						$participants[$participantData['id']] = new OrganizationUser($participantData['id'], @$participantData['title'], @$participantData['first_name'], @$participantData['last_name'], '', '', '', '', '', @$participantData['org_type'], @$participantData['role'], @$participantData['sub_role']);
					}
				}
				if (isset($result['events']) && is_array($result['events'])) {
					$data = array();
					foreach ($result['events'] as $eventData) {
						$event = new Event(@$eventData['object'], @$eventData['description'], @$eventData['place']);
						$event->setId(@$eventData['id']);
						$event->setDateStart(@$eventData['start_date']);
						$event->setDateEnd(@$eventData['end_date']);
						$event->addParticipant($beneficiary);
						if (isset($eventData['participants']) && is_array($eventData['participants'])) {
							foreach ($eventData['participants'] as $participant) {
								if (! isset($participant['id']) || ! isset($participants[$participant['id']])) {
									continue;
								}
								$event->addParticipant($participants[$participant['id']]);
							}
						}
						$data[] = $event;
					}
				}
			}
		} catch (\Exception $e) {
			throw new \Exception('Erreur lors de la recherche d\'événements.', StatusConstants::toCode($e->getCode()), $e);
		}
		return $data;
	}

	public function updateBeneficiaryEvent($beneficiaryId, $event)
	{
		$data = false;
		try {
			// -- Create params
			$params = $event->toArray();
			
			// -- Find data
			$request = $this->client->post('beneficiaries/' . $beneficiaryId . '/agenda', array(), $params);
			$response = $request->send()->json();
			// - Wrong result
			if (null == $response || ! isset($response['status'])) {
				throw new \Exception($response['message'], StatusConstants::UNKNWON_ERROR);
			}
			if (StatusConstants::OK != $response['status']) {
				throw new \Exception($response['message'], StatusConstants::toCode($response['status']));
			}
			// - Got it!
			$data = @$response['data'];
		} catch (\Exception $e) {
			throw new \Exception('Erreur lors de la création de l\'événement.', StatusConstants::toCode($e->getCode()), $e);
		}
		return $data;
	}

	public function removeBeneficiaryEvent(Event $event)
	{
		// $this->em->remove($event);
		// $this->em->flush();
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
			if (StatusConstants::OK != $response['status']) {
				throw new \Exception($response['message'], StatusConstants::toCode($response['status']));
			}
			if (empty($response['data']) || ! array_key_exists('evaluation', $response['data']) || ! is_array($response['data']['evaluation']) || empty($response['data']['evaluation'])) {
				return null;
			}
			// - Good result
			$data = array();
			$result = $response['data'];
			// Model available and requested
			if (array_key_exists('model', $result) && (true === $model || 'separate' === $model)) {
				$data['model'] = EvaluationModel::fromJson($result['model']);
			}
			// Evaluations available
			if ('merge' == $model || false === $model) {
				$data = EvaluationModel::fromJson($result['evaluation']);
			}
			elseif (false === $model) {
				$data = Evaluation::fromJson($result['evaluation']);
			}
			else {
				$data['evaluation'] = Evaluation::fromJson($result['evaluation']);
			}
		} catch (\Exception $e) {
			throw new \Exception('Erreur lors de l\'appel au P4S : getBeneficiaryEvaluation()', StatusConstants::toCode(StatusConstants::UNKNOWN_ERROR), $e);
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
			if (StatusConstants::OK != $response['status']) {
				throw new \Exception($response['message'], StatusConstants::toCode($response['status']));
			}
			if (empty($response['data']) || ! isset($response['data']['evaluations']) || ! is_array($response['data']['evaluations']) || empty($response['data']['evaluations'])) {
				return null;
			}
			// - Good result
			$data = array();
			$result = $response['data'];
			// Model available and requested
			if (array_key_exists('model', $response['data']) && (true === $model || 'separate' === $model)) {
				$data['model'] = EvaluationModel::fromJson($result['model']);
			}
			// Evaluations available
			$evaluations = array();
			$receivedEvaluations = $result['evaluations'];
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
			throw new \Exception('Erreur lors de l\'appel au P4S : findBeneficiaryEvaluations()', StatusConstants::toCode(StatusConstants::UNKNOWN_ERROR), $e);
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
			$serializer = new \Zumba\Util\JsonSerializer();
			$evaluationStr = $serializer->serialize($evaluation, false);
			$request = $this->client->post('evaluations', array(), $evaluationStr);
			$response = $request->send()->json();
			if (StatusConstants::OK == $response['status']) {
				if (! array_key_exists('data', $response)) {
					return $data;
				}
				$data = $response['data'];
			}
			else {
				throw new \Exception($response['message'], StatusConstants::toCode($response['status']));
			}
		} catch (\Exception $e) {
			throw new \Exception('Erreur lors de l\'appel au P4S : updateBeneficiaryEvaluation()', StatusConstants::toCode(StatusConstants::UNKNOWN_ERROR), $e);
		}
		return $data;
	}

	public function getEvaluationModel($code)
	{
		if (empty($code)) {
			throw new \Exception('Unknown beneficiary\'s evaluation model with these given criteria');
		}
		
		$data = null;
		try {
			// -- Find data
			$request = $this->client->get('evaluations/model/' . $code);
			$response = $request->send()->json();
			// - Wrong result
			if (StatusConstants::OK != $response['status']) {
				throw new \Exception($response['message'], StatusConstants::toCode($response['status']));
			}
			if (! array_key_exists('data', $response) || empty($response['data']) || ! is_array($response['data'])) {
				return null;
			}
			// - Good result
			$data = EvaluationModel::fromJson($response['data']);
		} catch (\Exception $e) {
			throw new \Exception('Erreur lors de la recherche du modèle d\'évaluation.', StatusConstants::toCode($e->getCode()), $e);
		}
		return $data;
	}

	public function getEvaluationModelCodes()
	{
		$data = null;
		try {
			// -- Find data
			$request = $this->client->get('evaluations/codes');
			$response = $request->send()->json();
			if (StatusConstants::OK == $response['status']) {
				if (! array_key_exists('data', $response)) {
					return $data;
				}
				$data = $response['data'];
			}
			else {
				throw new \Exception($response['message'], StatusConstants::toCode($response['status']));
			}
		} catch (\Exception $e) {
			throw new \Exception('Erreur lors la récupération de la liste des modèles d\'évaluation.', StatusConstants::toCode($e->getCode()), $e);
		}
		return $data;
	}

	public function createLink($linkType, $beneficiaryId, $linkedElementId)
	{
		$data = false;
		try {
			// -- Create params
			$params = array(
				'link_type' => $linkType
			);
			if (LinkConstants::WITH_ORGANIZATION == $linkType) {
				$params['organization_id'] = $linkedElementId;
			}
			elseif (LinkConstants::WITH_HOME_HELPER == $linkType) {
				$params['home_helper_id'] = $linkedElementId;
			}
			
			// -- Find data
			$request = $this->client->post('beneficiaries/' . $beneficiaryId . '/links', array(), $params);
			$response = $request->send()->json();
			// - Wrong result
			if (null == $response || ! isset($response['status'])) {
				throw new \Exception($response['message'], StatusConstants::UNKNWON_ERROR);
			}
			if (StatusConstants::OK != $response['status'] && StatusConstants::LINK_ALREADY_CREATED != $response['status']) {
				throw new \Exception($response['message'], StatusConstants::toCode($response['status']));
			}
			$data = true;
		} catch (\Exception $e) {
			throw new \Exception('Erreur lors de la création du lien.', StatusConstants::toCode($e->getCode()), $e);
		}
		return $data;
	}

	public function removeLink($linkType, $beneficiaryId, $linkedElementId)
	{
		$data = false;
		try {
			// -- Create params
			$params = array(
				'link_type' => $linkType
			);
			if (LinkConstants::WITH_ORGANIZATION == $linkType) {
				$params['organization_id'] = $linkedElementId;
			}
			elseif (LinkConstants::WITH_HOME_HELPER == $linkType) {
				$params['home_helper_id'] = $linkedElementId;
			}
			
			// -- Find data
			$request = $this->client->delete('beneficiaries/' . $beneficiaryId . '/links', array(), $params);
			$response = $request->send()->json();
			if (isset($response['status']) && (StatusConstants::OK == $response['status'] || StatusConstants::NOT_FOUND == $response['status'])) {
				$data = true;
			}
			else {
				throw new \Exception($response['message'], StatusConstants::toCode($response['status']));
			}
		} catch (\Exception $e) {
			throw new \Exception('Erreur lors de la suppression lien.', StatusConstants::toCode($e->getCode()), $e);
		}
		return $data;
	}
}
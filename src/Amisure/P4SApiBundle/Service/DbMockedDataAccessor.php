<?php
namespace Amisure\P4SApiBundle\Service;

use Amisure\Service1Bundle\Entity\User\BeneficiaryUser;
use Amisure\Service1Bundle\Entity\User\OrganizationUser;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Session\Session;
use Amisure\P4SApiBundle\Entity\UserConstants;

/**
 * Accessor for the P4S data
 * Mocked version, not linked to the P4S, but to the current application database
 *
 * @author Olivier Maridat (Trialog)
 */
class DbMockedDataAccessor extends ADataAccessor
{

	private $em;

	private $session;

	private $securityCtx;

	private $host;

	private $beneficiaryList;

	public function __construct(EntityManager $em, Session $session, $securityCtx, $host)
	{
		$this->em = $em;
		$this->session = $session;
		$this->securityCtx = $securityCtx;
		$this->host = $host;
		$this->beneficiaryList = null;
	}

	public function getBeneficiaryList()
	{
		$this->beneficiaryList = $this->em->getRepository('Amisure\Service1Bundle\Entity\User\BeneficiaryUser')->findByRelatedBeneficiaries($this->securityCtx->getToken()
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
		$profile = $this->em->getRepository('Amisure\Service1Bundle\Entity\User\BeneficiaryUser')->find($beneficiaryId);
		return $profile;
	}

	public function getOrganizationUserProfile($criteria = array())
	{
		if (empty($criteria) || (! array_key_exists('organizationType', $criteria) && ! array_key_exists('beneficiaryId', $criteria))) {
			throw new \Exception('Unknown beneficiary\'s contact with these given criteria');
		}
		$profile = $this->em->getRepository('Amisure\Service1Bundle\Entity\User\OrganizationUser')->findOrganizationBy($criteria['beneficiaryId'], $criteria['organizationType']);
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
			$event = $this->em->getRepository('AmisureService1Bundle:Event')->find($criteria['id']);
		}
		return $event;
	}

	public function getBeneficiaryEvents($criteria = array())
	{
		if (empty($criteria) || ! array_key_exists('beneficiaryId', $criteria)) {
			throw new \Exception('Unknown beneficiary\'s event list with these given criteria');
		}
		$events = $this->em->getRepository('AmisureService1Bundle:Event')->findByBeneficiary($criteria['beneficiaryId']);
		return $events;
	}

	public function updateBeneficiaryEvent($event)
	{
		$this->em->persist($event);
		$this->em->flush();
		return $event->getId();
	}

	public function getBeneficiaryEvaluation($criteria = array())
	{
		if (empty($criteria) || (! array_key_exists('id', $criteria) && ! array_key_exists('beneficiaryId', $criteria))) {
			throw new \Exception('Unknown beneficiary\'s evaluation with these given criteria');
		}
		$evaluation = null;
		$params = array();
		$orderBy = array();
		if (UserConstants::CG != $this->securityCtx->getToken()->getUser()->getOrganizationType()) {
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
		$evaluation = $this->em->getRepository('AmisureService1Bundle:Evaluation')->findOneBy($params, $orderBy);
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
		if (UserConstants::CG != $this->securityCtx->getToken()->getUser()->getOrganizationType()) {
			$params['finished'] = true;
		}
		$evaluations = $this->em->getRepository('AmisureService1Bundle:Evaluation')->findBy($params, array(
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
			$organizations = $this->em->getRepository('AmisureService1Bundle:Organization')->findByBeneficiary($criteria['beneficiaryId'], $criteria['organizationType']);
		}
		else if (array_key_exists('departementCode', $criteria)) {
			$organizations = $this->em->getRepository('AmisureService1Bundle:Organization')->findByDepartement($criteria['organizationType'], $criteria['departementCode']);
		}
		else {
			$organizations = $this->em->getRepository('AmisureService1Bundle:Organization')->findBy(array(
				'type' => $criteria['organizationType']
			), array(
				'name' => 'ASC'
			));
		}
		return $organizations;
	}
}
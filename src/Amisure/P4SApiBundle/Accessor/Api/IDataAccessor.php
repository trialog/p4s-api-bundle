<?php
namespace Amisure\P4SApiBundle\Accessor\Api;

use Amisure\P4SApiBundle\Entity\Event;

/**
 *
 * @author Olivier Maridat (Trialog)
 */
interface IDataAccessor
{

	public function findBeneficiaries($criteria = array(), $filter = array());

	/**
	 * deprecated
	 *
	 * @see Amisure\P4SApiBundle\Accessor\Api\IDataAccessor::findBeneficiaries
	 */
	public function getBeneficiaryList($criteria = array(), $filter = array());

	public function getBeneficiarySmallProfile($beneficiaryId);

	public function getBeneficiary($beneficiaryId, $profileType = 'FULL');

	/**
	 * deprecated
	 *
	 * @see Amisure\P4SApiBundle\Accessor\Api\IDataAccessor::getBeneficiary
	 */
	public function getBeneficiaryProfile($beneficiaryId);

	public function getBeneficiaryEvent($criteria = array());

	public function getBeneficiaryEvents($criteria = array());

	public function updateBeneficiaryEvent($event);

	public function removeBeneficiaryEvent(Event $event);

	public function getEvaluationModel($code);

	public function getEvaluationModelCodes();

	public function getBeneficiaryEvaluation($criteria = array());

	public function findBeneficiaryEvaluations($criteria = array(), $filter = array());

	/**
	 * deprecated
	 *
	 * @see Amisure\P4SApiBundle\Accessor\Api\IDataAccessor::findBeneficiaryEvaluations
	 */
	public function getBeneficiaryEvaluations($criteria = array());

	public function updateBeneficiaryEvaluation($evaluation);

	public function getOrganizationUserProfile($userId);

	public function findOrganizations($criteria = array());

	/**
	 * Create a link between a beneficiary and an organization or a home helper
	 *
	 * @param string $linkType
	 *        	WITH_ORGANIZATION or WITH_HOME_HELPER
	 * @param string $beneficiaryId
	 *        	Id of the beneficiary
	 * @param string $linkedElementId
	 *        	Id of the organization or the home helper, depending of $linkType value
	 * @return True in case of success, false otherwize. Actually, it will certainly throw an exception if it failed for good reasons
	 * @throws \Exception if it failed for a reason
	 */
	public function createLink($linkType, $beneficiaryId, $linkedElementId);

	/**
	 * Remove a link between a beneficiary and an organization or a home helper
	 *
	 * @param string $linkType
	 *        	WITH_ORGANIZATION or WITH_HOME_HELPER
	 * @param string $beneficiaryId
	 *        	Id of the beneficiary
	 * @param string $linkedElementId
	 *        	Id of the organization or the home helper, depending of $linkType value
	 * @return True in case of success, false otherwize. Actually, it will certainly throw an exception if it failed for good reasons
	 * @throws \Exception if it failed for a reason
	 */
	public function removeLink($linkType, $beneficiaryId, $linkedElementId);
}

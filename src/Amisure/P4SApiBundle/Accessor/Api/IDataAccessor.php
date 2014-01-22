<?php
namespace Amisure\P4SApiBundle\Accessor\Api;

use Amisure\P4SApiBundle\Entity\Event;

/**
 *
 * @author Olivier Maridat (Trialog)
 */
interface IDataAccessor
{
	public function getBeneficiaryList($criteria=array(), $filter=array());

	public function getBeneficiarySmallProfile($beneficiaryId);

	public function getBeneficiary($beneficiaryId, $profileType='FULL');
	/**
	 * @deprecated
	 * @see Amisure\P4SApiBundle\Accessor\Api\IDataAccessor::getBeneficiary
	 */
	public function getBeneficiaryProfile($beneficiaryId);

	public function getBeneficiaryEvent($criteria = array());

	public function getBeneficiaryEvents($criteria = array());

	public function updateBeneficiaryEvent($event);

	public function removeBeneficiaryEvent(Event $event);

	public function getBeneficiaryEvaluation($criteria = array());

	public function getBeneficiaryEvaluations($criteria = array());

	public function updateBeneficiaryEvaluation($evaluation);

	public function getOrganizationUserProfile($userId);

	public function findOrganizations($criteria = array());
}

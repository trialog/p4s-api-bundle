<?php
namespace Amisure\P4SApiBundle\Service;

/**
 *
 * @author Olivier Maridat (Trialog)
 */
interface IDataAccessor
{

	public function getBeneficiaryList();

	public function getBeneficiarySmallProfile($beneficiaryId);

	public function getBeneficiaryProfile($beneficiaryId);

	public function getBeneficiaryEvent($criteria = array());

	public function getBeneficiaryEvents($criteria = array());

	public function updateBeneficiaryEvent($event);

	public function getBeneficiaryEvaluation($criteria = array());

	public function getBeneficiaryEvaluations($criteria = array());

	public function updateBeneficiaryEvaluation($evaluation);

	public function getOrganizationUserProfile($userId);

	public function findOrganizations($criteria = array());
}

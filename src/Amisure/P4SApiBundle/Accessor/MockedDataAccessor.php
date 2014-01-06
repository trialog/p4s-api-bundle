<?php
namespace Amisure\P4SApiBundle\Service;

use Amisure\P4SApiBundle\Entity\User\BeneficiaryUser;
use Amisure\P4SApiBundle\Entity\User\OrganizationUser;
use Amisure\P4SApiBundle\Entity\Event;
use Amisure\P4SApiBundle\Entity\Evaluation;
use Amisure\P4SApiBundle\Entity\Role;
use Amisure\P4SApiBundle\Entity\Organization;
use Amisure\P4SApiBundle\Accessor\Api\ADataAccessor;
use Amisure\P4SApiBundle\Entity\EvaluationElementCategory;
use Amisure\P4SApiBundle\Entity\EvaluationElement;

/**
 * Accessor for the P4S data
 * Mocked version, not linked to the P4S, but return hardcoded values
 *
 * @author Olivier Maridat (Trialog)
 */
class MockedDataAccessor extends ADataAccessor
{

	private $user1Mini;

	private $user2Mini;

	private $user1;

	private $user2;

	private $user3;

	private $user4;

	private $event1;

	private $event2;

	private $evaluation1;

	private $evaluation2;

	public function __construct()
	{
		// Roles
		$this->roleUser = new Role('Utilisateur', 'ROLE_USER');
		$this->roleAdmin = new Role('Administrateur', 'ROLE_ADMIN');
		$this->roleBeneficiary = new Role('Bénécifiaire', 'BENEFICIARY');
		$this->roleOrgUser = new Role('Utilisateur d\'une organisation', 'ORG_USER');
		
		// Beneficiaries
		$this->user1Mini = new BeneficiaryUser('jeanne.dupont', 'Mme', 'Jeanne', 'Dupont', 'Lambert', '94100');
		$this->user1Mini->setId(1);
		$this->user1 = new BeneficiaryUser('jeanne.dupont', 'Mme', 'Jeanne', 'DUPONT', 'Lambert', '25, rue du Rocher, 92000 Nanterre', 'j.dupont@amisure.fr', '01 40 22 33 35', '07 07 00 00 18', '', '1 juillet 1925', 'La Courneuve (93120)');
		$this->user1->setId(1);
		$this->user1->addRole($this->roleUser);
		$this->user1->addRole($this->roleBeneficiary);
		$this->user2Mini = new BeneficiaryUser('marc.henry', 'M.', 'Marc', 'Henry', '', '94150');
		$this->user2Mini->setId(2);
		$this->user2 = new BeneficiaryUser('marc.henry', 'M.', 'Marc', 'Henry', '', '12, rue du Sanglier, 92000 Nanterre', 'm.henry@amisure.fr', '01 42 11 33 35', '07 14 00 00 18', '', '23 août 1932', 'La Courneuve (93120)');
		$this->user2->setId(2);
		$this->user2->addRole($this->roleUser);
		$this->user2->addRole($this->roleBeneficiary);
		
		// Organization Users
		$this->user3 = new OrganizationUser(0, 'Mme', 'Carole', 'DUPUIS', '10, avenue de l\'arc en ciel, 02540 Rocam', 'c.dupuis@amisure.fr', '03 11 22 33 35', '', '03 11 22 33 01', 'CG');
		$this->user3->setId(3);
		$this->user3->addRole($this->roleUser);
		$this->user3->addRole($this->roleOrgUser);
		$this->user4 = new OrganizationUser(1, 'Mme', 'Chloé', 'BRUNE', '130, rue du Général Dde Gaulle, 02158 Parotour', 'c.brune@amisure.fr', '03 81 22 33 35', '', '03 81 22 33 01', 'SAAD');
		$this->user4->setId(4);
		$this->user4->addRole($this->roleUser);
		$this->user4->addRole($this->roleOrgUser);
		
		// Organization
		$this->org1 = new Organization("UNA94", "SAAD");
		$this->org1->setContact($user4);
		$this->org2 = new Organization("CG94", "CG");
		$this->org2->setContact($user3);
		
		// Links
		$this->user1->addOrganization($this->user3);
		$this->user1->addOrganization($this->user4);
		$this->user2->addOrganization($this->user3);
		$this->user2->addOrganization($this->user4);
		
		// Event
		$this->event1 = new Event('Rendez-vous avec votre Conseil Général pour une évaluation.');
		$this->event1->setId(0);
		$this->event1->addParticipant($this->user1);
		$this->event1->addParticipant($this->user3);
		
		$this->event2 = new Event('Rendez-vous avec votre Service d\'Aide à la Personne afin de déterminer avec vous un plan d\'intervention.');
		$this->event2->setId(0);
		$this->event2->addParticipant($this->user1);
		$this->event2->addParticipant($this->user4);
		
		// Evaluation
		$this->evaluation1 = new Evaluation('Première évaluation du Conseil Général');
		$this->evaluation1 = $this->createDefaultMockedEvaluation($this->evaluation1);
		for ($i = 0; $i < $this->evaluation1->getElements()->count(); $i ++) {
			$this->evaluation1->getElements()
				->get($i)
				->setValue(mt_rand(0, 1) ? 'Oui' : 'Non');
		}
		$this->evaluation1->setBeneficiaryId($this->user1->getId());
		$this->evaluation1->setServiceId(1);
		
		$this->evaluation2 = new Evaluation('Deuxième évaluation du Conseil Général');
		$this->evaluation2 = $this->createDefaultMockedEvaluation($this->evaluation2);
		for ($i = 0; $i < $this->evaluation2->getElements()->count(); $i ++) {
			$this->evaluation2->getElements()
				->get($i)
				->setValue(mt_rand(0, 1) ? 'Oui' : 'Non');
		}
		$this->evaluation2->setBeneficiaryId($this->user1->getId());
		$this->evaluation2->setServiceId(1);
	}

	public function getBeneficiaryList()
	{
		return array(
			$this->user1Mini,
			$this->user2Mini
		);
	}

	public function getBeneficiarySmallProfile($beneficiaryId)
	{
		$profile = $this->user2Mini;
		if (1 == $beneficiaryId) {
			$profile = $this->user1Mini;
		}
		return $profile;
	}

	public function getBeneficiaryProfile($beneficiaryId)
	{
		$profile = $this->user2;
		if (1 == $beneficiaryId) {
			$profile = $this->user1;
		}
		return $profile;
	}

	public function getOrganizationUserProfile($criteria = array())
	{
		if ('CG' == $criteria['organizationType']) {
			$profile = $this->user3;
		}
		elseif ('SAAD' == $criteria['organizationType']) {
			$profile = $this->user4;
		}
		else {
			throw new \Exception('Unknown organization user ' . print_r($criteria, true));
		}
		return $profile;
	}

	public function getBeneficiaryEvent($criteria = array())
	{
		return $this->event1;
	}

	public function getBeneficiaryEvents($criteria = array())
	{
		return array(
			$this->event1,
			$this->event2
		);
	}

	public function updateBeneficiaryEvent($event)
	{
		return 0;
	}

	public function getBeneficiaryEvaluation($criteria = array())
	{
		return $this->evaluation1;
	}

	public function getBeneficiaryEvaluations($criteria = array())
	{
		return array(
			$this->evaluation1,
			$this->evaluation2
		);
	}

	public function updateBeneficiaryEvaluation($evaluation)
	{
		return 0;
	}
	

	public function findOrganizations($criteria = array())
	{
		if (empty($criteria) || (! array_key_exists('organizationType', $criteria))) {
			throw new \Exception('Unknown organization with these given criteria');
		}
		$organizations = null;
		if ('SAAD' == $criteria['organisationType']) {
			$organizations = array($this->org1);
		}
		elseif ('CG' == $criteria['organisationType']) {
			$organizations = array($this->org2);
		}
		return $organizations;
	}
	
	public function createDefaultMockedEvaluation($evaluation)
	{
		$cat0 = new EvaluationElementCategory('Variables discriminantes : autonomie physique et psychique');
		$cat0->setId(0);
		$cat1 = new EvaluationElementCategory('Variables discriminantes : autonomie domestique et sociale');
		$cat1->setId(1);
		$evaluation->addElement(new EvaluationElement($cat0, 'Cohérence : converser et/ou se composer de façon sensée'));
		$evaluation->addElement(new EvaluationElement($cat0, 'Orientation : se repérer dans le temps, les moments de la journée, les lieux'));
		$evaluation->addElement(new EvaluationElement($cat1, 'Gestion : gérer ses propres affaires, son budget, ses biens'));
		$evaluation->addElement(new EvaluationElement($cat1, 'Cuisine : préparer ses repas et les conditions pour être servis'));
		return $evaluation;
	}
}
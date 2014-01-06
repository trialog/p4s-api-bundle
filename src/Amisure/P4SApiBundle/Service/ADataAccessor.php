<?php
namespace Amisure\P4SApiBundle\Service;

use Amisure\Service1Bundle\Entity\User\BeneficiaryUser;
use Amisure\Service1Bundle\Entity\User\OrganizationUser;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Accessor for the P4S data
 * Abstract version to provide this service as a Twig extension
 *
 * @author Olivier Maridat (Trialog)
 */
abstract class ADataAccessor extends \Twig_Extension implements IDataAccessor
{
	public function getFunctions()
	{
		return array(
			'getBeneficiaryList' => new \Twig_Function_Method($this, 'getBeneficiaryList'),
			'getBeneficiarySmallProfile' => new \Twig_Function_Method($this, 'getBeneficiarySmallProfile')
		);
	}

	public function getName()
	{
		return 'DataAccessor';
	}
}
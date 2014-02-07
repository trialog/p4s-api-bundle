<?php
namespace Amisure\P4SApiBundle\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Amisure\P4SApiBundle\Accessor\Api\IDataAccessor;

/**
 *
 * @author Olivier Maridat (Trialog)
 *        
 */
class UserParamConverter implements ParamConverterInterface
{

	protected $class;

	protected $accessor;

	protected $em;

	public function __construct(IDataAccessor $accessor, $em)
	{
		$this->class = 'Amisure\\P4SApiBundle\\Entity\\User\\SessionUser';
		$this->accessor = $accessor;
		$this->em = $em;
	}

	function supports(ConfigurationInterface $configuration)
	{
		return $configuration->getClass() == $this->class || is_subclass_of($configuration->getClass(), $this->class);
	}

	function apply(Request $request, ConfigurationInterface $configuration)
	{
		if ($configuration->getClass() == 'Amisure\\P4SApiBundle\\Entity\\User\\BeneficiaryUser') {
			$user = $this->accessor->getBeneficiaryProfile($request->get('beneficiaryId'));
			if (null == $user) {
				throw new NotFoundResourceException('BeneficiaryUser[id=' . $request->get('beneficiaryId') . '] inexistant');
			}
		}
		else {
			$user = $this->em->find('Amisure\\P4SApiBundle\\Entity\\User\\SessionUser', $request->get('beneficiaryId'));
			if (null == $user) {
				throw new NotFoundResourceException('SessionUser[id=' . $request->get('beneficiaryId') . '] inexistant');
			}
		}
		$request->attributes->set($configuration->getName(), $user);
		return true; // Don't use an other ParamConverter after this one
	}
}
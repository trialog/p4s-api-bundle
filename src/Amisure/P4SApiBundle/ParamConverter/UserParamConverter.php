<?php
namespace Amisure\P4SApiBundle\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface;
use Symfony\Component\HttpFoundation\Request;
use Amisure\P4SApiBundle\Service\IDataAccessor;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

/**
 *
 * @author Olivier Maridat (Trialog)
 *        
 */
class UserParamConverter implements ParamConverterInterface
{

	protected $class;

	protected $accessor;

	public function __construct(IDataAccessor $accessor)
	{
		$this->class = 'Amisure\\Service1Bundle\\Entity\\User\\SessionUser';
		$this->accessor = $accessor;
	}

	function supports(ConfigurationInterface $configuration)
	{
		return $configuration->getClass() == $this->class || is_subclass_of($configuration->getClass(), $this->class);
	}

	function apply(Request $request, ConfigurationInterface $configuration)
	{
		$user = $this->accessor->getBeneficiaryProfile($request->get('beneficiaryId'));
		if (null == $user) {
			throw new NotFoundResourceException('BeneficiaryUser[id=' . $request->get('beneficiaryId') . '] inexistant');
		}
		$request->attributes->set($configuration->getName(), $user);
		return true; // Don't use an other ParamConverter after this one
	}
}
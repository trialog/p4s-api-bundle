<?php
namespace Amisure\P4SApiBundle\Entity;

class RdvEvent extends Event
{

	public function __construct($object = '')
	{
		parent::__construct($object);
		$dateEnd = \DateTime::createFromFormat('!H', '2');
		parent::setDateEnd($dateEnd);
	}
	
	/*
	 * Add the duration in hour
	 * (non-PHPdoc) @see \Amisure\P4SApiBundle\Entity\Event::setDateEnd()
	 */
	public function setDateEnd(\DateTime $hourDuration)
	{
		$dateEnd = $this->getDateStart();
		$dateEnd->add(\DateInterval::createFromDateString($hourDuration->format('\P\TH\:i\S')));
		parent::setDateEnd($dateEnd);
		return $this;
	}
}
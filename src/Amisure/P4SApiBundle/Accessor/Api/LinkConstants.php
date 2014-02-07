<?php
namespace Amisure\P4SApiBundle\Accessor\Api;

/**
 *
 * @author Olivier Maridat (Trialog)
 */
class StatusConstants
{

	const UNKNWON_ERROR = 'UNKNWON_ERROR';

	const OK = 'OK';

	const ACCESS_TOKEN_MISSING = 'MISSING_ACCESS_TOKEN';

	const ACCESS_TOKEN_UNKNOWN = 'UNKNOWN_ACCESS_TOKEN';

	const ACCESS_TOKEN_EXPIRED = 'EXPIRED_ACCESS_TOKEN';

	const UNKNOWN_USER = 'UNKNOWN_USER';

	const UNAUTHORIZED = 'UNAUTHORIZED';

	const NOT_EXIST = 'NOT_EXIST';

	const MISSING_IMPLEMENTATION = 'MISSING_IMPLEMENTATION';

	const BAD_PARAMETERS = 'BAD_PARAMETERS';

	const LINK_ALREADY_CREATED = 'LINK_ALREADY_CREATED';

	private static $values = array(
		StatusConstants::UNKNWON_ERROR,
		StatusConstants::OK,
		StatusConstants::ACCESS_TOKEN_MISSING,
		StatusConstants::ACCESS_TOKEN_UNKNOWN,
		StatusConstants::ACCESS_TOKEN_EXPIRED,
		StatusConstants::UNKNOWN_USER,
		StatusConstants::UNAUTHORIZED,
		StatusConstants::NOT_EXIST,
		StatusConstants::UNKNWON_ERROR,
		StatusConstants::MISSING_IMPLEMENTATION,
		StatusConstants::BAD_PARAMETERS,
		StatusConstants::LINK_ALREADY_CREATED
	);

	public static function toCode($value)
	{
		if (in_array($value, self::$values)) {
			$values = array_keys(self::$values, $value);
			if (! empty($values)) {
				return $values[0];
			}
		}
		return 0;
	}

	public static function toValue($code)
	{
		if (array_key_exists($code, self::$values)) {
			return self::$values[$code];
		}
		return StatusConstants::UNKNWON_ERROR;
	}
}
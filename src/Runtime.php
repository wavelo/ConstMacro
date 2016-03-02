<?php

namespace ConstMacro;


class Runtime
{


	/**
	 * @param array
	 * @param mixed
	 * @return array
	 * @throws ConstMacro\RuntimeException
	 **/
	public static function toArray(array $tree, $props)
	{
		$isRest = end($tree)===0;
		$rest = self::propsToArray($props, $isRest);

		$ret = [];
		$index = 0;
		foreach ($tree as $token) {
			if ($token===0) {
				if (is_array($props) && array_keys($props)===range(0, count($props)-1)) {
					$ret[] = array_values($rest);

				} else {
					$ret[] = $rest;
				}

				$key = NULL;

			} elseif (empty($token)) {
				$key = $index++;

			} else {
				$key = $token[0]===0 ? $index++ : $token[0];

				if ($props instanceof \ArrayAccess && isset($props[$key])) {
					$value = $props[$key];

				} elseif (is_object($props) && isset($props->$key)) {
					$value = $props->$key;

				} elseif (is_array($rest) && isset($rest[$key])) {
					$value = $rest[$key];

				} elseif (isset($token[2])) {
					$value = self::value($token[2]);

				} else {
					$value = NULL;
				}

				$value = $token[1]===0 ? [$value] : self::toArray($token[1], $value);

				foreach ($value as $val) {
					$ret[] = $val;
				}
			}

			if ($isRest && isset($key)) {
				if (is_array($rest)) {
					unset($rest[$key]);

				} else {
					unset($rest->$key);
				}
			}

		}

		return $ret;
	}


	/**
	 * @param mixed
	 * @return mixed
	 */
	public static function value($value)
	{
		if ($value instanceof Lazy) {
			return $value->invoke();

		} elseif (is_array($value)) {
			foreach ($value as $key => $val) {
				$value[$key] = self::value($val);
			}
		}

		return $value;
	}


	/**
	 * @param mixed
	 * @param bool
	 * @return array|object
	 * @throws ConstMacro\RuntimeException
	 **/
	private static function propsToArray($props, $need)
	{
		if ($need && is_array($props)) {
			return $props;

		} elseif ($need) {
			if ($props instanceof \stdClass) {
				return (object) (array) $props;

			} elseif ($props instanceof \Traversable) {
				return iterator_to_array($props);

			} elseif ($vars = get_object_vars($props)) { // instanceof =
				return (object) $vars;
			}

			throw new RuntimeException("Rest ...operator expects \$props to be array, Traversable object or object with public properties.");

		} elseif (is_array($props) || is_object($props)) {
			return $props;
		}

		throw new RuntimeException("Invalid \$props given, expected array or object.");
	}

}

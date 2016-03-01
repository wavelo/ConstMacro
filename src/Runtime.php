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
	public static function toArray(array $tree, $original)
	{
		$rest = end($tree)===0;
		$assoc = !is_array($original) || array_keys($original)!==range(0, count($original)-1);
		$props = self::propsToArray($original, $rest);

		$ret = [];
		$index = 0;
		foreach ($tree as $token) {
			if ($token===0) {
				$ret[] = is_object($original) ? (object) $props : ($assoc ? $props : array_values($props));

			} elseif (empty($token)) {
				$key = $index++;

				if ($rest) {
					unset($props[$key]);
				}

			} else {
				if ($token[0]!==0) {
					$key = $token[0];
					$assoc = TRUE;

				} else {
					$key = $index++;
				}

				if ($original instanceof \ArrayAccess && isset($original[$key])) {
					$value = $original[$key];

				} elseif (is_object($original) && isset($original->$key)) {
					$value = $original->$key;

				} elseif (is_array($props) && isset($props[$key])) {
					$value = $props[$key];

				} elseif (isset($token[2])) {
					$value = self::value($token[2]);

				} else {
					$value = NULL;
				}

				if ($token[1]===0) {
					$ret[] = $value;

				} else {
					foreach (self::toArray($token[1], $value) as $val) {
						$ret[] = $val;
					}
				}

				if ($rest) {
					unset($props[$key]);
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
	 * @return array|Iterator|ArrayAccess
	 * @throws ConstMacro\RuntimeException
	 **/
	private static function propsToArray($props, $need)
	{
		if (is_array($props)) {
			return $props;

		} elseif ($props instanceof \stdClass) {
			return (array) $props;

		} elseif ($props instanceof \ArrayAccess && empty($need)) {
			return $props;

		} elseif ($props instanceof \Traversable) {
			return iterator_to_array($props);

		} elseif (is_object($props) && empty($need)) {
			return $props;
		}

		if ($need) {
			throw new RuntimeException("Rest ...operator expects \$props to be array or Traversable object.");

		} else {
			throw new RuntimeException("Invalid \$props given, expected array, Traversable or ArrayAccess.");
		}
	}

}

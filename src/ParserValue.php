<?php

namespace ConstMacro;

use Latte\MacroTokens;
use Latte\PhpWriter;


class ParserValue
{

	/** @var string */
	public $value;

	/** @var string */
	public $input;

	/** @var array */
	private $tokens;

	/** @var int */
	private $level = 0;

	/** @var Latte\PhpWriter */
	private $writer;


	/**
	 * @param string
	 * @return ConstMacro\Parser
	 * @throws ConstMacro\ParserException
	 **/
	public static function parse($str)
	{
		$parser = new ParserValue;
		$parser->tokens = array_slice(token_get_all("<?php $str"), 1);
		$parser->writer = new PhpWriter(new MacroTokens(''));
		$parser->process();

		if ($parser->level) {
			throw new ParserException('Malformed syntax: missing ) or ] level');
		}

		$parser->input = implode('', array_map(function($token) {
			return is_array($token) ? $token[1] : $token;
		}, $parser->tokens));

		return $parser;
	}


	private function process()
	{
		while (isset($this->tokens[0])) {
			$token = $this->tokens[0];

			if ($this->level===0 && $token===',') {
				break;

			} elseif ($this->level===0 && $token===']') {
				break;

			} elseif ($token==='(') {
				throw new ParserException('Malformed syntax: unsupported function call');

			} elseif ($token===']') {
				$this->level--;

			} elseif ($token==='[') {
				$this->level++;

			} elseif ($token===')') {
				$this->level--;

				if (substr($this->value, -2)===', ') {
					$this->value = substr($this->value, 0, -2);
				}

			} elseif (is_array($token) && $token[0]===T_STRING) {
				if ($this->processCall()) {
					$this->level++;
					continue;

				} else {
					$token[1] = $this->writer->formatWord($token[1]);
				}

			} elseif (is_array($token) && $token[0]===T_VARIABLE) {
				if ($this->processCall()) {
					$this->level++;
					continue;
				}
			}

			$this->value .= is_array($token) ? $token[1] : $token;
			array_shift($this->tokens);
		}
	}


	private function processCall()
	{
		$lazy = '';
		if ($tokens = $this->tokenSequence(T_STRING, T_DOUBLE_COLON, T_STRING, '(')) {
			$lazy = "\"$tokens[0]::$tokens[2]\"";

		} elseif ($tokens = $this->tokenSequence(T_VARIABLE, T_DOUBLE_COLON, T_STRING, '(')) {
			$lazy = "get_class($tokens[0]) . \"::$tokens[2]\"";

		} elseif ($tokens = $this->tokenSequence(T_VARIABLE, T_OBJECT_OPERATOR, T_STRING, '(')) {
			$lazy = "[$tokens[0], \"$tokens[2]\"]";

		} elseif ($tokens = $this->tokenSequence(T_STRING, '(')) {
			$lazy = "\"$tokens[0]\"";

		} elseif ($tokens = $this->tokenSequence(T_VARIABLE, '(')) {
			$lazy = "$tokens[0]";
		}

		if ($lazy) {
			$this->value .= "new ConstMacro\Lazy($lazy, ";
			return TRUE;
		}

		return FALSE;
	}


	/**
	 * @param mixed...
	 * @return bool
	 **/
	private function tokenSequence()
	{
		$args = func_get_args();

		$tokens = [];
		for ($i=0; $i<count($args); $i++) {
			if (empty($this->tokens[$i])) {
				return FALSE;
			}

			$token = $this->tokens[$i];

			$value = is_array($token) ? $token[1] : $token;
			$token = is_array($token) ? $token[0] : $token;

			if ($token!==$args[$i]) {
				return FALSE;
			}

			$tokens[] = $value;
		}

		$this->tokens = array_slice($this->tokens, count($args));
		return $tokens;
	}

}

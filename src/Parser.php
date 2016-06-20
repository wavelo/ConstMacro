<?php

namespace ConstMacro;

use Latte\MacroTokens;
use Latte\PhpWriter;


class Parser
{

	/** @var string */
	public $compact = [];

	/** @var array */
	public $assign = [];

	/** @var string */
	public $tree = '';

	/** @var string */
	public $var = '';

	/** @var string */
	public $expr = '';

	/** @var string */
	private $input;

	/** @var string[] */
	private $level = [];

	/** @var bool */
	private $first = TRUE;

	/** @var string */
	private $context;

	/** @var string */
	private $token;


	const CONTEXT_BEGIN = 'begin';
	const CONTEXT_ARRAY = 'array';
	const CONTEXT_ARRAYEND = 'arrayend';
	const CONTEXT_RESTEND = 'restend';
	const CONTEXT_TOKEN = 'token';
	const CONTEXT_TOKENEND = 'tokenend';
	const CONTEXT_ARRAYVALUE = 'arrayvalue';
	const CONTEXT_TOKENVALUE = 'tokenvalue';
	const CONTEXT_END = 'end';


	/**
	 * @param string
	 * @return string
	 **/
	public static function parse($str)
	{
		if (substr($str, 0, 3) === "\xEF\xBB\xBF") { // BOM
			$str = substr($str, 3);
		}

		if (!preg_match('##u', $str)) {
			throw new \InvalidArgumentException('Input is not valid UTF-8 stream.');
		}

		$parser = new self;
		$parser->input = str_replace("\r\n", "\n", $str);
		$parser->writer = new PhpWriter(new MacroTokens(''));
		$parser->process();

		foreach (array_reverse($parser->assign, TRUE) as $key => $token) {
			if ($token===NULL) {
				array_splice($parser->assign, $key, 1);
				continue;

			} elseif (isset($parser->compact[$token])) {
				$parser->assign[$key] = NULL;
				continue;
			}

			$parser->assign[$key] =  "\$$token";
			$parser->compact[$token] = $token;
		}

		if ($parser->compact) {
			$parser->compact = array_values($parser->compact);
			$parser->assign = "list(" . implode(',', $parser->assign) . ")";
			$parser->expr = "{$parser->assign} = ";

		} else {
			$parser->assign = "";
		}

		$parser->expr .= "\ConstMacro\Runtime::toArray({$parser->tree}, $parser->var);";
		return $parser;
	}


	public function process()
	{
		$this->setContext(self::CONTEXT_BEGIN);

		while ($this->context && $this->input) {
			$this->{'context' . $this->context}();
		}

		if ($this->context || $this->level) {
			throw new ParserException('Malformed syntax: invalid destructuring definition');
		}
	}


	private function contextBegin()
	{
		$matches = $this->match('~^\s*
			(?P<start>\[) ## start of array context
		~xsi');

		if (!empty($matches['start'])) {
			$this->open(self::CONTEXT_END);

		} else {
			throw new ParserException('Malformed syntax: missing beginning of destructuring');
		}
	}


	private function contextArray()
	{
		$matches = $this->match('~^\s*(?:
			(?P<start>\[)|	 				## start of array context
			(?P<end>\])| 					## end of array context
			(?P<skip>,)|					## skip
			(?P<rest>\.{3}[^\s:,\]\[=]+)|	## rest
			(?P<token>[^\s:,\]=\[]+)		## start of token
		)~xsi');

		if (!empty($matches['end'])) {
			return $this->close();
		}

		if (!$this->first) {
			$this->tree .= ',';
		}

		$this->first = FALSE;

		if (!empty($matches['start'])) {
			$this->tree .= "[0,";
			$this->open(self::CONTEXT_ARRAYEND);

		} elseif (!empty($matches['skip'])) {
			$this->assign[] = NULL;
			$this->tree .= '[]';

		} elseif (!empty($matches['rest'])) {
			$this->assign[] = mb_substr($matches['rest'], 3);
			$this->tree .= "0";
			$this->setContext(self::CONTEXT_RESTEND);

		} elseif (!empty($matches['token'])) {
			$this->tree .= '[';
			$this->token = $matches['token'];
			$this->setContext(self::CONTEXT_TOKEN);

		} else {
			throw new ParserException('Malformed syntax: invalid array context');
		}
	}


	private function contextArrayEnd()
	{
		$this->contextTokenEnd(self::CONTEXT_ARRAYVALUE);
	}


	private function contextArrayValue()
	{
		$this->contextTokenValue(self::CONTEXT_ARRAYEND);
	}


	private function contextToken()
	{
		$matches = $this->match('~^\s*(?:
			(?P<ntoken>:\s*(?P<name>[^\s:,=\[\]]+))|	## named-token
			(?P<ttoken>:\s*\[)|							## subtree key-token
			(?P<ktoken>:)								## key-token
		)~xsi');

		if (!empty($matches['ntoken'])) {
			$this->assign[] = $matches['name'];
			$this->tree .= $this->writer->formatWord($this->token) . ",0";
			$this->setContext(self::CONTEXT_TOKENEND);

		} elseif (!empty($matches['ttoken'])) {
			$this->tree .= $this->writer->formatWord($this->token) . ",";
			$this->open(self::CONTEXT_TOKENEND);

		} elseif (!empty($matches['ktoken'])) {
			$this->assign[] = $this->token;
			$this->tree .= $this->writer->formatWord($this->token) . ",0";
			$this->setContext(self::CONTEXT_TOKENEND);

		} else {
			$this->assign[] = $this->token;
			$this->tree .= "0,0";
			$this->setContext(self::CONTEXT_TOKENEND);
		}
	}


	private function contextTokenEnd($contextValue=self::CONTEXT_TOKENVALUE)
	{
		$matches = $this->match('~^\s*(?:
			(?P<value>=)|		## value of token
			(?P<endtoken>,)|	## end of token
			(?P<endarray>\])	## end of array context
		)~xsi');

		if (!empty($matches['value'])) {
			$this->setContext($contextValue);

		} elseif (!empty($matches['endtoken'])) {
			$this->tree .= "]";
			$this->setContext(self::CONTEXT_ARRAY);

		} elseif (!empty($matches['endarray'])) {
			$this->tree .= "]";
			$this->close();

		} else {
			throw new ParserException('Malformed syntax: missing end of token');
		}
	}


	private function contextTokenValue($contextNext=self::CONTEXT_TOKENEND)
	{
		$parser = ParserValue::parse($this->input);

		$this->tree .= isset($parser->value) ? ",$parser->value" : ",NULL";
		$this->input = $parser->input;
		$this->setContext($contextNext);
	}


	private function contextRestEnd()
	{
		$matches = $this->match('~^\s*
			(?P<end>\])	## end of array context
		~xsi');

		if (!empty($matches['end'])) {
			$this->close();

		} else {
			throw new ParserException('Malformed syntax: rest operator in invalid position');
		}
	}


	private function contextEnd()
	{
		$matches = $this->match('~^\s*(?:
			(?P<assign>=\s*)| ## assign
		)~xsi');

		if (!empty($matches['assign'])) {
			$this->context = NULL;
			$this->var = trim($this->input);

		} else {
			throw new ParserException('Malformed syntax: missing assigned value');
		}
	}


	private function open($context)
	{
		$this->tree .= '[';
		$this->first = TRUE;
		$this->level[] = $context;
		$this->setContext(self::CONTEXT_ARRAY);
	}


	private function close()
	{
		$this->tree .= ']';
		$this->first = FALSE;
		$this->setContext(array_pop($this->level));
	}


	/**
	 * @param string
	 * @return array
	 **/
	private function match($re)
	{
		if (preg_match($re, $this->input, $matches, PREG_OFFSET_CAPTURE)) {
			$this->input = substr($this->input, strlen($matches[0][0]));

			return array_map(function($v) { return $v[0]; }, $matches);

		} elseif (preg_last_error()) {
			throw new RegexpException(NULL, preg_last_error());
		}

		return [];
	}


	/**
	 * @param string
	 **/
	private function setContext($context)
	{
		$this->context = $context;
	}

}

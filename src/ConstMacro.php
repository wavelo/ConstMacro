<?php

use Latte\MacroNode;
use Latte\PhpWriter;
use Latte\Macros\MacroSet;
use Latte\Macros\CoreMacros;
use ConstMacro\Parser;


/**
 * - {const ? /}
 * - {const ?}{/const}
 * - {foreach ? => []}{/foreach}
 */
class ConstMacro extends MacroSet
{

	/** @var Latte\Macros\ConstMacro */
	private static $coreMacros;


	public static function install(Latte\Compiler $compiler)
	{
		$me = new static($compiler);
		$me::$coreMacros = new CoreMacros($compiler);

		$me->addMacro('const', [$me, 'macroConst'], [$me, 'macroEndConst']);
		$me->addMacro('foreach', NULL, [$me, 'macroEndForeach']);
	}


	/**
	 * {const ...}
	 */
	public function macroConst(MacroNode $node, PhpWriter $writer)
	{
		if ($node->modifiers) {
			trigger_error("Modifiers are not allowed in {{$node->name}}", E_USER_WARNING);
		}

		$node->data->parser = Parser::parse($node->args);
	}


	/**
	 * {/const}
	 */
	public function macroEndConst(MacroNode $node, PhpWriter $writer)
	{
		$parser = $node->data->parser;

		if ($node->content===NULL) {
			return $parser->expr;
		}

		$node->openingCode .= $writer->write("<?php \$_l->compacts[] = call_user_func_array('compact', %var) + %var; %raw ?>",
			$parser->compact,
			array_fill_keys($parser->compact, NULL),
			$parser->expr
		);

		$node->closingCode .= "<?php extract(array_pop(\$_l->compacts)); ?>";
	}


	/**
	 * {foreach ...}
	 */
	public function macroEndForeach(MacroNode $node, PhpWriter $writer)
	{
		$result = preg_match('#^\s*
			(?P<iterator>.*)
			\s+as\s+
			(?P<key>\\$[a-z][a-z0-9_]*\s*=>\s*)?
			((?P<props>\\$[a-z][a-z0-9_]*),\s*)?
			(?P<const>\[.*\])
		\s*$#xsi', $node->args, $matches);

		if (empty($result)) {
			return self::$coreMacros->macroEndForeach($node, $writer);
		}

		$props = $matches['props'] ?: uniqid('$tmp_');
		$parser = Parser::parse("$matches[const] = $props");

		$node->openingCode = $writer->write(
			'<?php $_l->compacts[] = call_user_func_array("compact", %var) + %var;'
			. '$iterations = 0; foreach ($iterator = $_l->its[] = '
			. 'new Latte\Runtime\CachingIterator(%raw) as %raw %raw) { %raw ?>',
			$parser->compact,
			array_fill_keys($parser->compact, NULL),
			$matches['iterator'], $matches['key'], $props,
			$parser->expr
		);

		$node->closingCode = '<?php $iterations++; } extract(array_pop($_l->compacts)); array_pop($_l->its); $iterator = end($_l->its) ?>';
	}

}

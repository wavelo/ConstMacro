<?php

use Latte\Engine;
use Latte\MacroNode;
use Latte\PhpWriter;
use Latte\Macros\MacroSet;
use ConstMacro\Parser;


/**
 * - {const ? /}
 * - {const ?}{/const}
 * - n:const="?"
 */
class ConstMacro extends MacroSet
{


	public static function install(Engine $latte)
	{
		$me = new static($latte->getCompiler());

		$me->addMacro('const', [$me, 'macroConst'], [$me, 'macroEndConst']);
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

}

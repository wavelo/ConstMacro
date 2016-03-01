<?php

include 'bootstrap.php';

$me = new class {
	public function now() { return now(); }
};

Assert::same(now(), (new ConstMacro\Lazy("now"))->invoke());
Assert::same(now(), (new ConstMacro\Lazy([$me, "now"]))->invoke());

<?php

include 'bootstrap.php';


class PropsArrayAccess implements ArrayAccess
{
	public $data = [];

	public function offsetExists($key) { return isset($this->data[$key]); }
	public function offsetGet($key) { return $this->data[$key]; }
	public function offsetSet($key, $val) { $this->data[$key] = $val; }
	public function offsetUnset($key) { unset($this->data[$key]); }
}


$props = new PropsArrayAccess;

$props->data = [];
Assert::equal(["a"=>NULL], evaluate("[a]", $props));
Assert::equal(["a"=>TRUE], evaluate("[a=true]", $props));

$props->data = [FALSE];
Assert::equal(["a"=>FALSE], evaluate("[a=true]", $props));

$props->data = [];
Assert::equal(["a"=>NULL], evaluate("[a:]", $props));
Assert::equal(["a"=>TRUE], evaluate("[a:=true]", $props));

$props->data = [FALSE];
Assert::equal(["a"=>TRUE], evaluate("[a:=true]", $props));

$props->data = ["a"=>FALSE];
Assert::equal(["a"=>FALSE], evaluate("[a:=true]", $props));
Assert::equal(["b"=>FALSE], evaluate("[a:b=true]", $props));

$props->data = [0, 1, 2];
Assert::equal(["a"=>0, "b"=>1, "c"=>2], evaluate("[a,b,c]", $props));

$props->data = [NULL, 1, 2];
Assert::equal(["a"=>NULL, "b"=>1, "c"=>2], evaluate("[[a,b]=[],b,c]", $props));


class PropsIteratorArrayAccess extends PropsArrayAccess implements IteratorAggregate
{
	public function getIterator() { return new \ArrayIterator(['a' => 3, 'd' => 4]); }
}

$props = new PropsIteratorArrayAccess;

$props->data = ['a'=>1, 'b'=>1, 'c'=>2];
Assert::equal(["a"=>1, "b"=>1, "c"=>2], evaluate("[a:,b:,c:]", $props));

$props->data = ['a'=>1, 'b'=>1, 'c'=>2];
Assert::equal(["a"=>1, 'b'=>1, "c"=>["d"=>4]], evaluate("[a:, b:, ...c]", $props));

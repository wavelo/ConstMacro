<?php

include 'bootstrap.php';

Assert::same(now(), now());
Assert::same(2, nowTimes());

Assert::equal(["a"=>now()], evaluate("[a:=now()]", []));
Assert::same(2, nowTimes());

Assert::equal(["a"=>FALSE], evaluate("[a:=now()]", ["a"=>FALSE]));
Assert::same(0, nowTimes());

Assert::equal(["a"=>[now(), "foo", now()], "b"=>[]], evaluate("[a:=[now(), foo, now()], ...b]", []));
Assert::same(4, nowTimes());

$me = new class { public function now() { return now(); }};
Assert::equal(["a"=>[now(), "foo", now()], "b"=>[]], evaluate("[a:=[\$me->now(), foo, now()], ...b]", [], compact('me')));
Assert::same(4, nowTimes());

$me = new class { public static function now() { return now(); }};
Assert::equal(["a"=>[now(), "foo", now()], "b"=>[]], evaluate("[a:=[\$me::now(), foo, now()], ...b]", [], compact('me')));
Assert::same(4, nowTimes());

$me = new class { public function __invoke() { return now(); }};
Assert::equal(["a"=>[now(), "foo", now()], "b"=>[]], evaluate("[a:=[\$me(), foo, now()], ...b]", [], compact('me')));
Assert::same(4, nowTimes());

$me = function() { return now(); };
Assert::equal(["a"=>[now(), "foo", now()], "b"=>[]], evaluate("[a:=[\$me(), foo, now()], ...b]", [], compact('me')));
Assert::same(4, nowTimes());

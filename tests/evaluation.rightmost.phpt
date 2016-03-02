<?php

include 'bootstrap.php';

list($a, $a) = [0, 0];

Assert::equal(["a"=>3], evaluate("[a,[a],a]", [1,[2],3]));
Assert::equal(["a"=>1, "d"=>2, "c"=>1, "b"=>NULL], evaluate("[a:,a:b,a:c,b:d,d:b]", ["a"=>1, "b"=>2, "c"=>3]));

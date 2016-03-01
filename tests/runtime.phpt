<?php

include 'bootstrap.php';


// [] = $props
$tree = [];
Assert::equal(...tree($tree, [], []));

// [, []] = $props
$tree = [[],[0,[]]];
Assert::equal(...tree($tree, [0, []], []));

// [,,, ...a] = $props
$tree = [[],[],[],0];
Assert::equal(...tree($tree, [0, 1, 2, 3, 4], [[3, 4]]));

// [a:b=1, c] = $props
$tree = [["a",0,1],[0,0]];
Assert::equal(...tree($tree, [], 				[1, NULL]));
Assert::equal(...tree($tree, ["b"=>2], 		[1, NULL]));
Assert::equal(...tree($tree, ["a"=>2], 		[2, NULL]));
Assert::equal(...tree($tree, ["a"=>2, "c"=>1], [2, NULL]));
Assert::equal(...tree($tree, ["a"=>[1, 2], 3], [[1, 2], 3]));


// [a, , b] = $props
$tree = [[0,0],[],[0,0]];
Assert::equal(...tree($tree, [], [NULL, NULL]));
Assert::equal(...tree($tree, [1, 1, 2], [1, 2]));


// [a, b, ...c] = $props
$tree = [[0,0],[0,0],0];
Assert::equal(...tree($tree, [], [NULL, NULL, []]));
Assert::equal(...tree($tree, [1, 1, 2], [1, 1, [2]]));
Assert::equal(...tree($tree, [1, 1, 2, 3], [1, 1, [2, 3]]));


// [a:, b:, ...c] = $props
$tree = [["a",0],["b",0],0];
Assert::equal(...tree($tree, [], [NULL, NULL, []]));
Assert::equal(...tree($tree, [1, 2], [NULL, NULL, [1, 2]]));
Assert::equal(...tree($tree, ["a"=>1, "b"=>1, 2], [1, 1, [2]]));
Assert::equal(...tree($tree, ["a"=>1, "b"=>1, "c"=>2, 3], [1, 1, ["c"=>2, 3]]));


// [[a:[c:=true, d:], [b]]] = $props
$tree = [[0,[["a",[["c",0,TRUE],["d",0]]],[0,[[0,0]]]]]];
Assert::equal(...tree($tree, [["a"=>[], []]], [TRUE, NULL, NULL]));


// [[a, [b]=[]]=[]] = $props
$tree = [[0,[[0,0],[0,[[0,0]],[]]],[]]];
Assert::equal(...tree($tree, [], [NULL, NULL]));


function tree(array $tree, $props, array $expected) {
	return [$expected, ConstMacro\Runtime::toArray($tree, $props)];
}

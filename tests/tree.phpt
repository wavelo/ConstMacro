<?php

include 'bootstrap.php';


Assert::equal('[]', parse('[] = $props')->tree);
Assert::equal('[[0,[]]]', parse('[[]] = $props')->tree);
Assert::equal('[[],[0,[]]]', parse('[, []] = $props')->tree);
Assert::equal('[[],[],[],0]', parse('[,,, ...a] = $props')->tree);

Assert::equal('[[0,0]]', parse('[a] = $props')->tree);
Assert::equal('[["a",0]]', parse('[a:] = $props')->tree);
Assert::equal('[["a",0]]', parse('[a:b] = $props')->tree);

Assert::equal('[[0,0,1]]', parse('[a=1] = $props')->tree);
Assert::equal('[["a",0,1]]', parse('[a:b=1] = $props')->tree);
Assert::equal('[["a",0,1],[0,0]]', parse('[a:b=1, c] = $props')->tree);
Assert::equal('[["a",0,"okay"],[0,0]]', parse('[a:b=okay, c] = $props')->tree);

Assert::equal('[[0,0],[0,0]]', parse('[a, b] = $props')->tree);
Assert::equal('[[0,0],[],[0,0]]', parse('[a, , b] = $props')->tree);

Assert::equal('[[0,0],[0,0],0]', parse('[a, b, ...c] = $props')->tree);
Assert::equal('[["a",0],["b",0],0]', parse('[a:, b:, ...c] = $props')->tree);

Assert::equal('[[0,[[0,0],[0,[[0,0]]]]]]', parse('[[a, [b]]] = $props')->tree);
Assert::equal('[[0,[[0,[[0,0],[0,[[0,0]]]]]]]]', parse('[[[a, [b]]]] = $props')->tree);
Assert::equal('[[0,0],[0,[[0,0],[0,0]]],0]', parse('[a, [b, c], ...d] = $props')->tree);
Assert::equal('[[0,0],[0,[["b",0],["c",0]]],0]', parse('[a, [b:, c:], ...d] = $props')->tree);
Assert::equal('[[0,0],[0,[["b",0],["d",0]]],0]', parse('[a, [b:c, d:], ...e] = $props')->tree);

Assert::equal('[[0,[[0,0],[0,[[0,0]],[]]],[]]]', parse('[[a, [b]=[]]=[]] = $props')->tree);

Assert::equal('[[0,[["a",[["c",0],["d",0]]],[0,[[0,0]]]]]]', parse('[[a:[c:, d:], [b]]] = $props')->tree);
Assert::equal('[[0,[["a",[["c",0,true],["d",0]]],[0,[[0,0]]]]]]', parse('[[a:[c:=true, d:], [b]]] = $props')->tree);

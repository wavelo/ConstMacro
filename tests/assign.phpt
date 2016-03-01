<?php

include 'bootstrap.php';

Assert::equal('', parse('[] = $props')->assign);
Assert::equal('', parse('[[]] = $props')->assign);
Assert::equal('', parse('[, []] = $props')->assign);
Assert::equal('', parse('[,] = $props')->assign);
Assert::equal('list($a)', parse('[,,, ...a] = $props')->assign);
Assert::equal('list($b)', parse('[[], b, [[]]] = $props')->assign);

Assert::equal('list($a)', parse('[a] = $props')->assign);
Assert::equal('list($a)', parse('[a:] = $props')->assign);
Assert::equal('list($b)', parse('[a:b] = $props')->assign);

Assert::equal('list(,,$a)', parse('[a, a, a] = $props')->assign);
Assert::equal('list(,$b,$a)', parse('[a, b, a] = $props')->assign);
Assert::equal('list(,,$a,$b)', parse('[b, a, [a, b]] = $props')->assign);
Assert::equal('list(,,$a,$b)', parse('[b, a, , [a, b]] = $props')->assign);

Assert::equal('list($a)', parse('[a=1] = $props')->assign);
Assert::equal('list($b)', parse('[a:b=1] = $props')->assign);
Assert::equal('list($b,$c)', parse('[a:b=1, c] = $props')->assign);

Assert::equal('list($a,$b)', parse('[a, b] = $props')->assign);
Assert::equal('list($a,$b)', parse('[a, , b] = $props')->assign);

Assert::equal('list($a,$b,$c)', parse('[a, b, ...c] = $props')->assign);
Assert::equal('list($a,$b,$c)', parse('[a:, b:, ...c] = $props')->assign);

Assert::equal('list($a,$b)', parse('[[[a, [b]]]] = $props')->assign);
Assert::equal('list($a,$b,$c,$d)', parse('[a, [b, c], ...d] = $props')->assign);
Assert::equal('list($a,$b,$c,$d)', parse('[a, [b:, c:], ...d] = $props')->assign);
Assert::equal('list($a,$c,$d,$e)', parse('[a, [b:c, d:], ...e] = $props')->assign);

Assert::equal('list($c,$d,$b)', parse('[[a:[c:, d:], [b]]] = $props')->assign);

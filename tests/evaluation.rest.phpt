<?php

include 'bootstrap.php';


Assert::equal(["a"=>NULL, "b"=>NULL, "c"=>[]], evaluate("[a, b, ...c]", []));
Assert::equal(["a"=>FALSE, "b"=>NULL, "c"=>[]], evaluate("[a=false, b, ...c]", []));
Assert::equal(["a"=>1, "b"=>2, "c"=>[]], evaluate("[a=false, b, ...c]", [1, 2]));
Assert::equal(["a"=>1, "b"=>2, "c"=>[3, 4]], evaluate("[a=false, b, ...c]", [1, 2, 3, 4]));
Assert::equal(["a"=>1, "b"=>2, "c"=>[4]], evaluate("[a=false, b, , ...c]", [1, 2, 3, 4]));

Assert::equal(["a"=>1, "b"=>2, "c"=>(object)['c'=>3, 'd'=>4]], evaluate("[a:=false, b:, ...c]", (object)['a'=>1, 'b'=>2, 'c'=>3, 'd'=>4]));

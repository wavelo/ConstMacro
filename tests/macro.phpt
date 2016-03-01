<?php

include 'bootstrap.php';


assertTemplate('inline', ['title' => 'Hello World!']);
assertTemplate('pair', ['title' => 'Hello World!']);
assertTemplate('n_const', ['title' => 'Hello World!']);
assertTemplate('scope');
assertTemplate('foreach');

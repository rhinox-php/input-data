<?php
$date = date('Y-m-d_Hm.s');
$runPath = __DIR__ . "/../.test-output/run_$date";
$currentPath = __DIR__ . "/../.test-output/run_$date";
mkdir($runPath, 0777, true);
mkdir($currentPath, 0777, true);
$cwd = getcwd();
chdir(__DIR__ . '/..');
try {
    passthru(__DIR__ . "/../vendor/bin/phpunit --order-by=random --coverage-xml $runPath/coverage-xml --coverage-html $runPath/report.xml -d memory_limit=-1");
} finally {
    chdir($cwd);
}

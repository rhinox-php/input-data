pushd %~dp0\..\
vendor\bin\phpunit --coverage-html .test-output/coverage %*
popd

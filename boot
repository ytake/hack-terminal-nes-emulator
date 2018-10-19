#!/usr/bin/env hhvm
<?hh // strict

require_once(__DIR__.'/vendor/hh_autoload.php');

use type Ytake\Nes\Application;

<<__Entrypoint>>
function run(): void {
  Application::main();
}

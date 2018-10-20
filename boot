#!/usr/bin/env hhvm
<?hh // strict
require_once(__DIR__.'/vendor/hh_autoload.php');

use type Hes\Application;

<<__Entrypoint>>
function main(): void {
  Application::main();
}

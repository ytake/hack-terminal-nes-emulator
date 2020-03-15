#!/usr/bin/env hhvm
require_once(__DIR__.'/vendor/hh_autoload.php');

use type Hes\Application;

<<__EntryPoint>>
async function main(): Awaitable<noreturn> {
  $code = await Application::runAsync();
  exit($code);
}

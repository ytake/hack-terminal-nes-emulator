<?hh // strict

namespace Hes\Cpu;

use namespace Hes\Cpu\Registers;

trait FieldAssert {
  require implements ProcessingInterface;

  <<__Rx>>
  public function resolveStatus(dynamic $t): Registers\Status {
    invariant($t is Registers\Status, 'type error');
    return $t;
  }
}

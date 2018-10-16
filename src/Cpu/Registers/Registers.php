<?hh // strict

namespace Ytake\Nes\Cpu\Registers;

final class Registers {

  public int $a = 0;
  public int $x = 0;
  public int $y = 0;
  public ?Status $p;
  public int $sp = 0;
  public int $pc = 0;

  public static function getDefault(): Registers {
    $instance = new self();
    $instance->a = 0x00;
    $instance->x = 0x00;
    $instance->y = 0x00;
    $instance->p = new Status(
      false,
      false,
      true,
      true,
      false,
      true,
      false,
      false
    );
    $instance->sp = 0x01fd;
    $instance->pc = 0x0000;
    return $instance;
  }
}

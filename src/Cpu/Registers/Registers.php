<?hh // strict

namespace Hes\Cpu\Registers;

final class Registers {

  public int $a = 0x00;
  public int $x = 0x00;
  public int $y = 0x00;
  public ?Status $p;
  public int $sp = 0x01fd;
  public int $pc = 0x0000;

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

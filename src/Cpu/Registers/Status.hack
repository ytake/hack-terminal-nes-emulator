<?hh // strict

namespace Hes\Cpu\Registers;

final class Status {

  public function __construct(
    public bool $negative,
    public bool $overflow,
    public bool $reserved,
    public bool $break_mode,
    public bool $decimal_mode,
    public bool $interrupt,
    public bool $zero,
    public bool $carry
  ) {}
}

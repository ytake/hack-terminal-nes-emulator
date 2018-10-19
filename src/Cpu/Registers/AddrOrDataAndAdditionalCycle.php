<?hh // strict

namespace Hes\Cpu\Registers;

class AddrOrDataAndAdditionalCycle {

  public function __construct(
    public int $addrOrData,
    public int $additionalCycle
  ) {}
}

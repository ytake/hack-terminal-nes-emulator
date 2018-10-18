<?hh // strict

namespace Ytake\Nes\Cpu\Registers;

class AddrOrDataAndAdditionalCycle {

  public function __construct(
    public int $addrOrData,
    public int $additionalCycle
  ) {}
}

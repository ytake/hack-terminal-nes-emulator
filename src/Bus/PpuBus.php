<?hh // strict

namespace Hes\Bus;

class PpuBus {

  public function __construct(
    public Ram $characterRam
  ) { }

  <<__Rx>>
  public function readByPpu(int $addr): int {
    return $this->characterRam->read($addr);
  }

  public function writeByPpu(int $addr, int $data): void {
    $this->characterRam->write($addr, $data);
  }
}

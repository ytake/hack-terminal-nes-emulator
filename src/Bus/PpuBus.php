<?hh // strict

namespace Ytake\Nes\Bus;

class PpuBus {

  public function __construct(
    public Ram $characterRam
  ) { }

  public function readByPpu(int $addr): int {
    return $this->characterRam->read($addr);
  }

  public function writeByPpu(int $addr, int $data): void {
    $this->characterRam->write($addr, $data);
  }
}

<?hh // strict

namespace Ytake\Nes\Bus;

use function array_fill;
use function count;

class Ram {

  public vec<int> $ram = vec[];

  public function __construct(
    int $size
  ) {
    $this->ram = vec(array_fill(0, $size, 0));
  }

  public function reset(): void {
    $this->ram = vec(array_fill(0, count($this->ram) - 1, 0));
  }
  
  <<__Rx>>
  public function read(int $addr): int {
    return $this->ram[$addr];
  }

  public function write(int $addr, int $val): void {
    $this->ram[$addr] = $val;
  }
}

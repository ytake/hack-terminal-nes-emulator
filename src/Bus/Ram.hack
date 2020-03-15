namespace Hes\Bus;

use namespace HH\Lib\C;

use function array_fill;

class Ram {

  private vec<int> $ram = vec[];

  public function __construct(
    int $size
  ) {
    $this->ram = vec(array_fill(0, $size, 0));
  }

  public function reset(): void {
    $this->ram = vec(array_fill(0, C\count($this->ram) - 1, 0));
  }

  <<__Rx>>
  public function read(int $addr): int {
    return $this->ram[$addr];
  }

  public function write(int $addr, int $val): void {
    $this->ram[$addr] = $val;
  }

  <<__Rx>>
  public function every(): vec<int> {
    return $this->ram;
  }
}

<?hh // strict

namespace Ytake\Nes\Bus;

use function count;
use function array_key_exists;
use function sprintf;
use function dechex;

class Rom {

  public vec<int> $rom = vec[];

  public function __construct(
    vec<int> $data
  ) {
    $this->rom = $data;
  }

  public function size(): int {
    return count($this->rom);
  }

  public function read(int $addr): int {
    if (! array_key_exists($addr, $this->rom)) {
      throw new \RuntimeException(
        sprintf(
          "Invalid address on rom read. Address: 0x%s Rom: 0x0000 - 0x%s",
          dechex($addr),
          dechex(count($this->rom))
        )
      );
    }
    return $this->rom[$addr];
  }
}

<?hh // strict

namespace Ytake\Nes\Bus;

use namespace HH\Lib\C;

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
  
  <<__Rx>>
  public function size(): int {
    return C\count($this->rom);
  }
  
  <<__Rx>>
  public function read(int $addr): int {
    if (!array_key_exists($addr, $this->rom)) {
      throw new \RuntimeException(
        sprintf(
          "Invalid address on rom read. Address: 0x%s Rom: 0x0000 - 0x%s",
          dechex($addr),
          dechex(C\count($this->rom))
        )
      );
    }
    return $this->rom[$addr];
  }
}

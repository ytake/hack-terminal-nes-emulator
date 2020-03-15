namespace Hes\Bus;

use namespace HH\Lib\Str;
use function dechex;

class Rom {

  public function __construct(
    protected Vector<int> $rom
  ) {}

  <<__Rx>>
  public function size(): int {
    return $this->rom->count();
  }

  <<__Rx>>
  public function read(int $addr): int {
    if (!$this->rom->containsKey($addr)) {
      throw new \RuntimeException(
        Str\format(
          "Invalid address on rom read. Address: 0x%s Rom: 0x0000 - 0x%s",
          dechex($addr),
          dechex($this->rom->count())
        )
      );
    }
    return $this->rom[$addr];
  }
}

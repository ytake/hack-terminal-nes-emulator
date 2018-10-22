<?hh // strict

namespace Hes\Ppu;

use type Hes\Bus\Ram;
use namespace HH\Lib\Dict;

class Palette {

  public Ram $paletteRam;

  public function __construct() {
    $this->paletteRam = new Ram(0x20);
  }

  <<__Rx>>
  public function isSpriteMirror(int $addr): bool {
    return ($addr === 0x10)
    |> ($$ === true) ? true : ($addr === 0x14)
    |> ($$ === true) ? true : ($addr === 0x18)
    |> ($$ === true) ? true : ($addr === 0x1c)
    |> ($$ === true) ? true : false;
  }

  <<__Rx>>
  public function isBackgroundMirror(int $addr): bool {
    return ($addr === 0x04)
    |> ($$ === true) ? true : ($addr === 0x08)
    |> ($$ === true) ? true : ($addr === 0x0c)
    |> ($$ === true) ? true : false;
  }

  public function read(): dict<int, int> {
    return Dict\map_with_key(
      $this->paletteRam->every(), ($k, $v) ==> {
      if($this->isSpriteMirror($k)) {
        $v = $this->paletteRam->read($k - 0x10);
      } elseif ($this->isBackgroundMirror($k)) {
        $v = $this->paletteRam->read(0x00);
      }
      return $v;
    });
  }

  <<__Rx>>
  public function getPaletteAddr(int $addr): int {
    //NOTE: 0x3f10, 0x3f14, 0x3f18, 0x3f1c is mirror of 0x3f00, 0x3f04, 0x3f08, 0x3f0c
    return (($addr & 0xFF) % 0x20)
    |> $this->isSpriteMirror($$) ? $$ - 0x10 : $$;
  }

  public function write(int $addr, int $data): void {
    $this->paletteRam->write($this->getPaletteAddr($addr), $data);
  }
}

<?hh // strict

namespace Ytake\Nes\Ppu;

use type Ytake\Nes\Bus\Ram;

class Palette {

  public Ram $paletteRam;

  public function __construct() {
    $this->paletteRam = new Ram(0x20);
  }

  public function isSpriteMirror(int $addr): bool {
    if($addr === 0x10) {
      return true;
    }
    if($addr === 0x14) {
      return true;
    }
    if ($addr === 0x18) {
      return true;
    }
    if ($addr === 0x1c) {
      return true;
    }
    return false;
  }

  public function isBackgroundMirror(int $addr): bool {
    return ($addr === 0x04)
    |> ($$ === true) ? true : ($addr === 0x08)
    |> ($$ === true) ? true : ($addr === 0x0c)
    |> ($$ === true) ? true : false;
  }

  public function read(): dict<int, int> {
    $return = dict[];
    foreach ($this->paletteRam->ram as $i => $value) {
      $return[$i] = $value;
      if ($this->isSpriteMirror($i)) {
        $return[$i] = $this->paletteRam->read($i - 0x10);
      } elseif ($this->isBackgroundMirror($i)) {
        $return[$i] = $this->paletteRam->read(0x00);
      }
    }
    return $return;
  }

  public function getPaletteAddr(int $addr): int {
    $mirrorDowned = (($addr & 0xFF) % 0x20);
    //NOTE: 0x3f10, 0x3f14, 0x3f18, 0x3f1c is mirror of 0x3f00, 0x3f04, 0x3f08, 0x3f0c
    return $this->isSpriteMirror($mirrorDowned) ? $mirrorDowned - 0x10 : $mirrorDowned;
  }

  public function write(int $addr, int $data): void {
    $this->paletteRam->write($this->getPaletteAddr($addr), $data);
  }
}

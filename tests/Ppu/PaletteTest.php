<?hh // strict

namespace HesTest\Ppu;

use type Hes\Ppu\Palette;
use type Facebook\HackTest\HackTest;
use function Facebook\FBExpect\expect;

final class PaletteTest extends HackTest {

  private ?Palette $palette;

  <<__Override>>
  public async function beforeEachTestAsync(): Awaitable<void> {
    $this->palette = new Palette();
  }

  public function testIsBackgroundMirror(): void {
    expect($this->palette?->isBackgroundMirror(0x04))->toBeTrue();
    expect($this->palette?->isBackgroundMirror(0x08))->toBeTrue();
    expect($this->palette?->isBackgroundMirror(0x0c))->toBeTrue();
    expect($this->palette?->isBackgroundMirror(314))->toBeFalse();
  }
}

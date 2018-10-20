<?hh // strict

namespace Hes\Ppu\Canvas;

use function imagecreatetruecolor;
use function imagecolorallocate;
use function imagesetpixel;
use function is_dir;
use function mkdir;
use function imagepng;
use function sprintf;

class PngCanvas extends AbstractDisposeCanvas {
  private int $serial = 0;

  <<__Memoize>>
  private function imageColor(): resource {
    return imagecreatetruecolor(256, 224);
  }

  <<__Override>>
  public function draw(
    Map<int, int> $frameBuffer
  ): void {
    $image = $this->imageColor();
    for ($y = 0; $y < 224; $y++) {
      for ($x = 0; $x < 256; $x++) {
        $index = ($x + ($y * 0x100)) * 4;
        $color = imagecolorallocate(
          $image,
          $frameBuffer[$index],
          $frameBuffer[$index + 1],
          $frameBuffer[$index + 2]
        );
        imagesetpixel($image, $x, $y, $color);
      }
    }
    if (! is_dir('screen')) {
      mkdir('screen');
    }
    imagepng($image, sprintf("screen/%08d.png", $this->serial++));
  }

  <<__Override>>
  public function __dispose(): void {

  }
}

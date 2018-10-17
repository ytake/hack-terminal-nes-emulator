<?hh // strict

namespace Ytake\Nes\Ppu\Canvas;

use function imagecreatetruecolor;
use function imagecolorallocate;
use function imagesetpixel;
use function is_dir;
use function mkdir;
use function imagepng;
use function sprintf;

class PngCanvas implements CanvasInterface {
  private int $serial = 0;

  public function draw(
    vec<int> $frameBuffer
  ): void {
    $image = imagecreatetruecolor(256, 224);
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
}

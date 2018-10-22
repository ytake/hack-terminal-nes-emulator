<?hh // strict

namespace Hes\Ppu\Canvas;

use type Facebook\CLILib\OutputInterface;

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
  public async function drawAsync(
    Map<int, int> $canvasBuffer,
    OutputInterface $_output
  ): Awaitable<void> {
    $image = $this->imageColor();
    for ($y = 0; $y < CanvasInterface::screenHeight; $y++) {
      for ($x = 0; $x < CanvasInterface::screenWidth; $x++) {
        $index = ($x + ($y * 0x100)) * 4;
        $color = imagecolorallocate(
          $image,
          $canvasBuffer[$index],
          $canvasBuffer[$index + 1],
          $canvasBuffer[$index + 2]
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

<?hh // strict

namespace Hes\Ppu\Canvas;

use namespace HH\Lib\Str;

use function floor;
use function time;
use function html_entity_decode;
use function array_fill;
use function intval;

use const ENT_NOQUOTES;
use const PHP_EOL;

final class TerminalCanvas extends AbstractDisposeCanvas {

  protected string $brailleCharOffset = '';
  protected int $currentSecond = 0;
  protected int $framesInSecond = 0;
  protected int $fps = 0;
  protected int $height = 0;
  protected string $lastFrame = '';
  protected Map<int, int> $lastFrameCanvasBuffer = Map{};
  protected ImmMap<int, ImmVector<string>> $pixelMap;
  protected int $width = 0;
  public int $threshold = 127;
  public int $frameSkip = 0;

  public function __construct() {
    $this->brailleCharOffset = html_entity_decode('&#' . 0x2800 . ';', ENT_NOQUOTES, 'UTF-8');
    $this->pixelMap = $this->matrix();
  }

  <<__Override>>
  public function draw(
    Map<int, int> $canvasBuffer
  ): void {
    if ($this->currentSecond !== time()) {
      $this->fps = $this->framesInSecond;
      $this->currentSecond = time();
      $this->framesInSecond = 1;
    } else {
      ++$this->framesInSecond;
    }
    $screenWidth = CanvasInterface::screenWidth;
    $screenHeight = CanvasInterface::screenHeight;
    $charWidth = intval($screenWidth / 2);
    $charHeight = intval($screenHeight / 4);

    if ($canvasBuffer !== $this->lastFrameCanvasBuffer) {
      $chars = $this->fill($screenWidth, $screenHeight);
      $frame = '';
      $fa = '';
      for ($y = 0; $y < $screenHeight; $y++) {
        for ($x = 0; $x < $screenWidth; $x++) {
          $pixelCanvasNumber = ($x + ($screenWidth * $y)) * 4;
          $charPosition = intval(floor($x / 2) + (floor($y / 4) * $charWidth));
          $pixelAvg = (
            $canvasBuffer[$pixelCanvasNumber] +
            $canvasBuffer[$pixelCanvasNumber + 1] +
            $canvasBuffer[$pixelCanvasNumber + 2]
          ) / 3;
          if ($pixelAvg > $this->threshold) {
            $row = $this->pixelMap->get($y % 4);
            if ($row is ImmVector<_> && $row->containsKey($x % 2)) {
              if ($row->containsKey($x % 2)) {
                /* HH_IGNORE_ERROR[4110] ignore */
                $chars[$charPosition] |= $row->at($x % 2);
              }
            }
          }
          if ($x % 2 === 1 && $y % 4 === 3) {
            $frame .= $chars[$charPosition];
            if ($x % ($screenWidth - 1) === 0) {
              $frame .= PHP_EOL;
            }
          }
        }
      }

      $this->lastFrame = $frame;
      $this->lastFrameCanvasBuffer = $canvasBuffer;
      $content = "\e[H\e[2J";
      if ($this->height > 0 && $this->width > 0) {
        $content = Str\format("\e[%dA\e[%dD", $this->height, $this->width);
      }
      $content = Str\format("FPS: %3d - Frame Skip: %3d\n", $this->fps, $this->framesInSecond) . $frame;

      echo $content;

      $this->height = $charHeight + 1;
      $this->width = $charWidth;
    }
  }

  private function fill(int $w, int $h): Map<int, string> {
    return new Map(array_fill(0, $w * $h, $this->brailleCharOffset));
  }

  <<__Memoize>>
  private function matrix(): ImmMap<int, ImmVector<string>> {
    return ImmMap{
      0 => ImmVector{
        html_entity_decode('&#' . 0x2801 . ';', ENT_NOQUOTES, 'UTF-8'),
        html_entity_decode('&#' . 0x2808 . ';', ENT_NOQUOTES, 'UTF-8')
      },
      1 => ImmVector{
        html_entity_decode('&#' . 0x2802 . ';', ENT_NOQUOTES, 'UTF-8'),
        html_entity_decode('&#' . 0x2810 . ';', ENT_NOQUOTES, 'UTF-8')
      },
      2 => ImmVector{
        html_entity_decode('&#' . 0x2804 . ';', ENT_NOQUOTES, 'UTF-8'),
        html_entity_decode('&#' . 0x2820 . ';', ENT_NOQUOTES, 'UTF-8')
      },
      3 => ImmVector{
        html_entity_decode('&#' . 0x2840 . ';', ENT_NOQUOTES, 'UTF-8'),
        html_entity_decode('&#' . 0x2880 . ';', ENT_NOQUOTES, 'UTF-8')
      },
    };
  }
}

<?hh // strict

namespace Ytake\Nes\Ppu\Canvas;

use function sprintf;
use function floor;
use function time;
use function html_entity_decode;
use function array_fill;
use function intval;
use function is_null;

use const ENT_NOQUOTES;
use const PHP_EOL;

newtype EntityDecodeTuple = ImmVector<string>;

class TerminalCanvas extends AbstractDisposeCanvas {
    protected string $brailleCharOffset;

    protected int $currentSecond = 0;
    protected int $framesInSecond = 0;
    protected int $fps = 0;
    protected int $height = 0;
    protected string $lastFrame = '';
    protected vec<int> $lastFrameCanvasBuffer = vec[];
    /**
     * Braille Pixel Matrix
     *   ,___,
     *   |1 4|
     *   |2 5|
     *   |3 6|
     *   |7 8|
     *   `````
     * @var array
     */
    protected ImmVector<ImmVector<string>> $pixelMap = ImmVector{};
    protected int $width = 0;

    public int $threshold = 127;
    public int $frameSkip = 0;

  public function __construct() {
    $this->brailleCharOffset = html_entity_decode('&#' . (0x2800) . ';', ENT_NOQUOTES, 'UTF-8');
    $this->pixelMap = new ImmVector([
      ImmVector{
        html_entity_decode('&#' . (0x2801) . ';', ENT_NOQUOTES, 'UTF-8'),
        html_entity_decode('&#' . (0x2808) . ';', ENT_NOQUOTES, 'UTF-8')
      },
      ImmVector{
        html_entity_decode('&#' . (0x2802) . ';', ENT_NOQUOTES, 'UTF-8'),
        html_entity_decode('&#' . (0x2810) . ';', ENT_NOQUOTES, 'UTF-8')
      },
      ImmVector{
        html_entity_decode('&#' . (0x2804) . ';', ENT_NOQUOTES, 'UTF-8'),
        html_entity_decode('&#' . (0x2820) . ';', ENT_NOQUOTES, 'UTF-8')
      },
      ImmVector{
        html_entity_decode('&#' . (0x2840) . ';', ENT_NOQUOTES, 'UTF-8'),
        html_entity_decode('&#' . (0x2880) . ';', ENT_NOQUOTES, 'UTF-8')
      },
    ]);
  }

  public function draw(
    vec<int> $canvasBuffer
  ): void {
    //Calculate current FPS
    if ($this->currentSecond !== time()) {
      $this->fps = $this->framesInSecond;
      $this->currentSecond = time();
      $this->framesInSecond = 1;
    } else {
      ++$this->framesInSecond;
    }
    $screenWidth = 256;
    $screenHeight = 224;
    $charWidth = intval($screenWidth / 2);
    $charHeight = intval($screenHeight / 4);

    if ($canvasBuffer !== $this->lastFrameCanvasBuffer) {
      $chars = array_fill(0, $screenWidth * $screenHeight, $this->brailleCharOffset);
      $frame = '';
      for ($y = 0; $y < $screenHeight; $y++) {
        for ($x = 0; $x < $screenWidth; $x++) {
          $pixelCanvasNumber = ($x + ($screenWidth * $y)) * 4;
          $charPosition = floor($x / 2) + (floor($y / 4) * $charWidth);
          $pixelAvg = (
            $canvasBuffer[$pixelCanvasNumber] +
            $canvasBuffer[$pixelCanvasNumber + 1] +
            $canvasBuffer[$pixelCanvasNumber + 2]
          ) / 3;
          if ($pixelAvg > $this->threshold) {
            if ($this->pixelMap->containsKey($y % 4)) {
              $row = $this->pixelMap->get($y % 4);
              if ($row instanceof ImmVector) {
                $v = $row->get($x % 2);
                if(!is_null($v)) {
                  $chars[intval($charPosition)] |= $v;
                }
              }
            }
          }
          if ($x % 2 === 1 && $y % 4 === 3) {
            $frame .= $chars[intval($charPosition)];
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
        $content = "\e[".$this->height."A\e[".$this->width."D";
      }
      $content .= sprintf(
        "FPS: %3d - Frame Skip: %3d\n",
        $this->fps,
        $this->framesInSecond
      ) . $frame;
      echo $content;
      $this->height = $charHeight + 1;
      $this->width = $charWidth;
    }
  }

  public function __dispose(): void {

  }
}

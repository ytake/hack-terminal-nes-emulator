namespace Hes\Ppu;

use namespace HH\Lib\C;
use namespace Hes\Ppu;
use type HH\Lib\Experimental\IO\WriteHandle;

use function array_fill;
use function intval;

class Renderer {

  public Map<int, int> $frameBuffer = Map{};
  public Vector<Tile> $background = Vector{};
  public int $serial = 0;

  private ImmVector<ImmVector<int>> $immColor = ImmVector{
    ImmVector{0x80, 0x80, 0x80},
    ImmVector{0x00, 0x3D, 0xA6},
    ImmVector{0x00, 0x12, 0xB0},
    ImmVector{0x44, 0x00, 0x96},
    ImmVector{0xA1, 0x00, 0x5E},
    ImmVector{0xC7, 0x00, 0x28},
    ImmVector{0xBA, 0x06, 0x00},
    ImmVector{0x8C, 0x17, 0x00},
    ImmVector{0x5C, 0x2F, 0x00},
    ImmVector{0x10, 0x45, 0x00},
    ImmVector{0x05, 0x4A, 0x00},
    ImmVector{0x00, 0x47, 0x2E},
    ImmVector{0x00, 0x41, 0x66},
    ImmVector{0x00, 0x00, 0x00},
    ImmVector{0x05, 0x05, 0x05},
    ImmVector{0x05, 0x05, 0x05},
    ImmVector{0xC7, 0xC7, 0xC7},
    ImmVector{0x00, 0x77, 0xFF},
    ImmVector{0x21, 0x55, 0xFF},
    ImmVector{0x82, 0x37, 0xFA},
    ImmVector{0xEB, 0x2F, 0xB5},
    ImmVector{0xFF, 0x29, 0x50},
    ImmVector{0xFF, 0x22, 0x00},
    ImmVector{0xD6, 0x32, 0x00},
    ImmVector{0xC4, 0x62, 0x00},
    ImmVector{0x35, 0x80, 0x00},
    ImmVector{0x05, 0x8F, 0x00},
    ImmVector{0x00, 0x8A, 0x55},
    ImmVector{0x00, 0x99, 0xCC},
    ImmVector{0x21, 0x21, 0x21},
    ImmVector{0x09, 0x09, 0x09},
    ImmVector{0x09, 0x09, 0x09},
    ImmVector{0xFF, 0xFF, 0xFF},
    ImmVector{0x0F, 0xD7, 0xFF},
    ImmVector{0x69, 0xA2, 0xFF},
    ImmVector{0xD4, 0x80, 0xFF},
    ImmVector{0xFF, 0x45, 0xF3},
    ImmVector{0xFF, 0x61, 0x8B},
    ImmVector{0xFF, 0x88, 0x33},
    ImmVector{0xFF, 0x9C, 0x12},
    ImmVector{0xFA, 0xBC, 0x20},
    ImmVector{0x9F, 0xE3, 0x0E},
    ImmVector{0x2B, 0xF0, 0x35},
    ImmVector{0x0C, 0xF0, 0xA4},
    ImmVector{0x05, 0xFB, 0xFF},
    ImmVector{0x5E, 0x5E, 0x5E},
    ImmVector{0x0D, 0x0D, 0x0D},
    ImmVector{0x0D, 0x0D, 0x0D},
    ImmVector{0xFF, 0xFF, 0xFF},
    ImmVector{0xA6, 0xFC, 0xFF},
    ImmVector{0xB3, 0xEC, 0xFF},
    ImmVector{0xDA, 0xAB, 0xEB},
    ImmVector{0xFF, 0xA8, 0xF9},
    ImmVector{0xFF, 0xAB, 0xB3},
    ImmVector{0xFF, 0xD2, 0xB0},
    ImmVector{0xFF, 0xEF, 0xA6},
    ImmVector{0xFF, 0xF7, 0x9C},
    ImmVector{0xD7, 0xE8, 0x95},
    ImmVector{0xA6, 0xED, 0xAF},
    ImmVector{0xA2, 0xF2, 0xDA},
    ImmVector{0x99, 0xFF, 0xFC},
    ImmVector{0xDD, 0xDD, 0xDD},
    ImmVector{0x11, 0x11, 0x11},
    ImmVector{0x11, 0x11, 0x11},
  };

  private dict<Canvas, classname<Canvas\AbstractDisposeCanvas>> $dic = dict[
    Canvas::TERMINAL => Canvas\TerminalCanvas::class,
    Canvas::NULL =>  Canvas\NullCanvas::class,
    Canvas::PNG => Canvas\PngCanvas::class,
  ];

  public function __construct(
  ) {
    $this->frameBuffer = new Map(array_fill(0, 256 * 256 * 4, 0)); // 256 x 240
  }

  <<__Rx>>
  public function shouldPixelHide(int $x, int $y): bool {
    $tileX = ~~intval($x / 8);
    $tileY = ~~intval($y / 8);
    $backgroundIndex = $tileY * 33 + $tileX;
    $sprite = dict[];
    if(C\contains_key($this->background, $backgroundIndex)) {
      $sprite = $this->background[$backgroundIndex]->pattern;
    }
    if (!C\count($sprite)) {
      return true;
    }
    return !($sprite[$y % 8] && $sprite[$y % 8][$x % 8] % 4 === 0);
  }

  public function render(
    RenderingData $data,
    Ppu\Canvas $canvas,
    WriteHandle $output
  ): void {
    if ($data->background is Vector<_>) {
      $this->renderBackground($data->background, $data->palette);
    }
    if ($data->sprites is dict<_, _>) {
      $this->renderSprites($data->sprites, $data->palette);
    }
    $cv = $this->resolveCanvas($canvas);
    \HH\Asio\join(
      $cv->drawAsync($this->frameBuffer, $output)
    );
    $cv->close();
  }

  <<__Memoize>>
  private function resolveCanvas(Canvas $canvas): Canvas\AbstractDisposeCanvas {
    $cn = $this->dic[$canvas];
    return new $cn();
  }

  public function renderBackground(
    Vector<Tile> $background,
    dict<int, mixed> $palette
  ): void {
    $this->background = $background;
    for ($i = 0; $i < $background->count(); $i += 1 | 0) {
      $x = ($i % 33) * 8;
      $y = ~~intval($i / 33) * 8;
      $this->renderTile($background->at($i), $x, $y, $palette);
    }
  }

  public function renderSprites(
    dict<int, SpriteWithAttribute> $sprites,
    dict<int, mixed> $palette
  ): void {
    foreach ($sprites as $sprite) {
      if ($sprite) {
        $this->renderSprite($sprite, $palette);
      }
    }
  }

  public function renderTile(
    Tile $tile,
    int $tileX,
    int $tileY,
    dict<int, mixed> $palette
  ): void {
    $offsetX = $tile->scrollX % 8;
    $offsetY = $tile->scrollY % 8;
    for ($i = 0; $i < 8; $i = ($i + 1) | 0) {
      for ($j = 0; $j < 8; $j = ($j + 1) | 0) {
        $paletteIndex = $tile->paletteId * 4 + $tile->pattern[$i][$j];
        $colorId = $palette[$paletteIndex];
        $color = $this->immColor->get(intval($colorId));
        if ($color is ImmVector<_>) {
          $x = $tileX + $j - $offsetX;
          $y = $tileY + $i - $offsetY;
          if ($x >= 0 && 0xFF >= $x && $y >= 0 && $y < 224) {
            $index = ($x + ($y * 0x100)) * 4;
            $this->frameBuffer->addAll(vec[
              Pair{$index, $color[0]},
              Pair{$index + 1, $color[1]},
              Pair{$index + 2, $color[2]},
              Pair{$index + 3, 0xFF}
            ]);
          }
        }
      }
    }
  }

  public function renderSprite(
    SpriteWithAttribute $sprite,
    dict<int, mixed> $palette
  ): void {
    $isVerticalReverse = !!($sprite->attribute & 0x80);
    $isHorizontalReverse = !!($sprite->attribute & 0x40);
    $isLowPriority = !!($sprite->attribute & 0x20);
    $paletteId = $sprite->attribute & 0x03;
    for ($i = 0; $i < 8; $i = ($i + 1) | 0) {
      for ($j = 0; $j < 8; $j = ($j + 1) | 0) {
        $x = $sprite->x + ($isHorizontalReverse ? 7 - $j : $j);
        $y = $sprite->y + ($isVerticalReverse ? 7 - $i : $i);
        if ($isLowPriority && $this->shouldPixelHide($x, $y)) {
          continue;
        }
        if ($sprite->sprite[$i][$j]) {
          $v = $palette[$paletteId * 4 + $sprite->sprite[$i][$j] + 0x10];
          if($v is int) {
            $color = $this->immColor->at($v);
            $index = ($x + $y * 0x100) * 4;
            $this->frameBuffer->addAll(vec[
              Pair{$index, $color[0]},
              Pair{$index + 1, $color[1]},
              Pair{$index + 2, $color[2]},
            ]);
          }
        }
      }
    }
  }
}

<?hh // strict

namespace Ytake\Nes\Ppu\Canvas;

<<__Sealed(AbstractDisposeCanvas::class)>>
interface CanvasInterface {

  public function draw(
    vec<int> $frameBuffer
  ): void;
}

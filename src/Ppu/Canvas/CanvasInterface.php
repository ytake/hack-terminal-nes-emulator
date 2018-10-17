<?hh // strict

namespace Ytake\Nes\Ppu\Canvas;

<<__Sealed(NullCanvas::class, PngCanvas::class, TerminalCanvas::class)>>
interface CanvasInterface {

  public function draw(
    vec<int> $frameBuffer
  ): void;
}

<?hh // strict

namespace Hes\Ppu\Canvas;

<<__Sealed(AbstractDisposeCanvas::class)>>
interface CanvasInterface {

  const int screenWidth =  256;
  const int screenHeight = 224;

  public function draw(
    Map<int, int> $frameBuffer
  ): void;
}

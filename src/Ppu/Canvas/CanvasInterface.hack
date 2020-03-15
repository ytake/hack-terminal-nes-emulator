namespace Hes\Ppu\Canvas;

use type HH\Lib\Experimental\IO\WriteHandle;

<<__Sealed(AbstractDisposeCanvas::class)>>
interface CanvasInterface {

  const int screenWidth =  256;
  const int screenHeight = 224;

  public function drawAsync(
    Map<int, int> $canvasBuffer,
    WriteHandle $output
  ): Awaitable<void>;
}

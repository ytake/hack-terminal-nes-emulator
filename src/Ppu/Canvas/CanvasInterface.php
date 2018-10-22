<?hh // strict

namespace Hes\Ppu\Canvas;

use type Facebook\CLILib\OutputInterface;

<<__Sealed(AbstractDisposeCanvas::class)>>
interface CanvasInterface {

  const int screenWidth =  256;
  const int screenHeight = 224;

  public function drawAsync(
    Map<int, int> $canvasBuffer,
    OutputInterface $output
  ): Awaitable<void>;
}

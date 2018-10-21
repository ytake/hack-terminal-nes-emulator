<?hh // strict

namespace Hes\Ppu\Canvas;

use function is_dir;
use function mkdir;
use function fopen;
use function time;
use function fclose;
use function microtime;
use function floor;
use function printf;
use function fprintf;

class NullCanvas extends AbstractDisposeCanvas {

  private ?resource $fp;
  private int $frame = 0;
  private float $last = -1.0;
  private int $initial;
  private string $dir = '';

  public function __construct() {
    $dir = 'tmp';
    if (! is_dir(($dir))) {
      mkdir($dir);
    }
    $this->dir = $dir;
    $this->initial = time();
  }

  <<__Override>>
  public function __dispose(): void {
    fclose($this->fp);
  }

  <<__Memoize>>
  private function fileOpen(): resource {
    return fopen($this->dir.'/nes.log', 'w');
  }

  <<__Override>>
  public function draw(
    Map<int, int> $_frameBuffer
  ): void {
    $this->fp = $this->fileOpen();
    $microTime = microtime(true);
    $second = floor($microTime);
    if ($second !== $this->last) {
      printf("%6d %dfps\n", $second - $this->initial, $this->frame);
      $this->frame = 0;
    }
    fprintf($this->fp, "%-8.2f frame %d\n", $microTime, $this->frame++);
    $this->last = floor($microTime);
  }
}

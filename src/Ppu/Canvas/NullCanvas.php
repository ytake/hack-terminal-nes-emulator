<?hh // strict

namespace Ytake\Nes\Ppu\Canvas;

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

  private resource $fp;
  private int $frame = 0;
  private float $last = -1.0;
  private int $initial;

  public function __construct() {
    $dir = 'tmp';
    if (! is_dir(($dir))) {
      mkdir($dir);
    }
    $this->fp = fopen($dir.'/nes.log', 'w');
    $this->initial = time();
  }

  <<__Override>>
  public function __dispose(): void {
    fclose($this->fp);
  }

  <<__Override>>
  public function draw(
    vec<int> $_frameBuffer
  ): void {
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

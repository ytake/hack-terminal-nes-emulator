<?hh // strict

namespace Hes\Ppu\Canvas;

use type Facebook\CLILib\OutputInterface;
use namespace namespace HH\Lib\Str;

use function is_dir;
use function mkdir;
use function fopen;
use function time;
use function fclose;
use function microtime;
use function floor;
use function printf;
use function fprintf;
use function intval;

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
  public function close(): void {
    fclose($this->fp);
  }

  private function fileOpen(): resource {
    return fopen($this->dir.'/nes.log', 'w');
  }

  <<__Override>>
  public async function drawAsync(
    Map<int, int> $_frameBuffer,
    OutputInterface $output
  ): Awaitable<void> {
    $this->fp = $this->fileOpen();
    $microTime = microtime(true);
    $second = floor($microTime);
    $content = '';
    if ($second !== $this->last) {
      await $output->writeAsync(
        Str\format("%6d %dfps\n", intval($second - $this->initial), $this->frame)
      );
      $this->frame = 0;
    }
    fprintf($this->fp, "%-8.2f frame %d\n", $microTime, $this->frame++);
    $this->last = floor($microTime);
  }
}

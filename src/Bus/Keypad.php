<?hh // strict

namespace Ytake\Nes\Bus;

use function exec;
use function fopen;
use function stream_set_blocking;
use function array_fill;
use function fread;
use function array_search;

class Keypad
{

  public mixed $file;

  public string $keyPressing = '';

  public vec<bool> $keyBuffer = vec[];

  public vec<bool> $keyRegistors = vec[];

  public bool $isSet = false;

  public int $index = 0;

  public function __construct() {
    exec('stty -icanon -echo');
    $this->file = fopen('php://stdin', 'r');
    stream_set_blocking($this->file, false);
    $this->keyBuffer = vec(array_fill(0, 8, false));
  }

  public function fetch(): void {
    $key = fread($this->file, 1);
    if ($key !== false || $key === '') {
      $this->keyDown($key);
    } elseif ($this->keyPressing !== '') {
      $this->keyUp($this->keyPressing);
    } 
    $this->keyPressing = $key;
  }

  public function keyDown(string $key): void {
    $keyIndex = $this->matchKey($key);
    if ($keyIndex > -1) {
      $this->keyBuffer[$keyIndex] = true;
    }
  }

  public function keyUp(string $key): void {
    $keyIndex = $this->matchKey($key);
    if ($keyIndex > -1) {
      $this->keyBuffer[$keyIndex] = false;
    }
  }

  public function matchKey(string $key): int {
    //Maps a keyboard key to a nes key.
    // A, B, SELECT, START, ↑, ↓, ←, →
    $keyIndex = array_search($key, ['.', ',', 'n', 'm', 'w', 's', 'a', 'd']);
    if ($keyIndex === false) {
      return -1;
    }
    return $keyIndex;
  }
  
  public function write(int $data): void {
    if ($data & 0x01) {
      $this->isSet = true;
    } elseif ($this->isSet && !($data & 0x01)) {
      $this->isSet = false;
      $this->index = 0;
      $this->keyRegistors = $this->keyBuffer;
    }
  }

  public function read(): bool {
    return $this->keyRegistors[$this->index++];
  }
}

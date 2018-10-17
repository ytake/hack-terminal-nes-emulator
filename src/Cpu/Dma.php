<?hh // strict

namespace Ytake\Nes\Cpu;

use Ytake\Nes\Bus\Ram;
use Ytake\Nes\Ppu\Ppu;

class Dma
{
  public bool $isProcessing = false;
  public int $ramAddr = 0x0000;

  public function __construct(
    public Ram $ram, 
    public Ppu $ppu
  ) {}

  public function isDmaProcessing(): bool {
    return $this->isProcessing;
  }

  public function runDma(): void {
    if (! $this->isProcessing) {
      return;
    }
    for ($i = 0; $i < 0x100; $i = ($i + 1) | 0) {
      $this->ppu->transferSprite($i, $this->ram->read($this->ramAddr + $i));
    }
    $this->isProcessing = false;
  }

  public function write(int $data): void {
    $this->ramAddr = $data << 8;
    $this->isProcessing = true;
  }
}

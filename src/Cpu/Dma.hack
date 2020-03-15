namespace Hes\Cpu;

use type Hes\Bus\Ram;
use type Hes\Ppu\Ppu;

class Dma {
  private bool $isProcessing = false;
  private int $ramAddr = 0x0000;

  public function __construct(
    public Ram $ram,
    public Ppu $ppu
  ) {}

  <<__Rx>>
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

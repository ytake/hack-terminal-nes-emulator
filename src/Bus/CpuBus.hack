namespace Hes\Bus;

use type Hes\Cpu\Dma;
use type Hes\Ppu\Ppu;

final class CpuBus {

  public function __construct(
    public Ram $ram,
    public Rom $programRom,
    public Ppu $ppu,
    public Keypad $keypad,
    public Dma $dma
  ) {}

  public function readByCpu(
    int $addr
  ): mixed {
    if ($addr < 0x0800) {
      return $this->ram->read($addr);
    } elseif ($addr < 0x2000) {
      // mirror
      return $this->ram->read($addr - 0x0800);
    } elseif ($addr < 0x4000) {
      // mirror
      return $this->ppu->read(($addr - 0x2000) % 8);
    } elseif ($addr === 0x4016) {
      // TODO Add 2P
      return $this->keypad->read();
    } elseif ($addr >= 0xC000) {
      // Mirror, if prom block number equals 1
      if ($this->programRom->size() <= 0x4000) {
        return $this->programRom->read($addr - 0xC000);
      }
      return $this->programRom->read($addr - 0x8000);
    } elseif ($addr >= 0x8000) {
      // ROM
      return $this->programRom->read($addr - 0x8000);
    }
    return false;
  }

  public function writeByCpu(
    int $addr,
    int $data
  ): void {
    if ($addr < 0x0800) {
      // RAM
      $this->ram->write($addr, $data);
    } elseif ($addr < 0x2000) {
      // mirror
      $this->ram->write($addr - 0x0800, $data);
    } elseif ($addr < 0x2008) {
      // PPU
      $this->ppu->write($addr - 0x2000, $data);
    } elseif ($addr >= 0x4000 && $addr < 0x4020) {
      if ($addr === 0x4014) {
        $this->dma->write($data);
      } elseif ($addr === 0x4016) {
        // TODO Add 2P
        $this->keypad->write($data);
      } else {
        // APU
        return;
      }
    }
  }
}

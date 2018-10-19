<?hh // strict

namespace Hes;

use namespace HH\Lib\C;

use type Hes\Bus\CpuBus;
use type Hes\Bus\PpuBus;
use type Hes\Bus\Ram;
use type Hes\Bus\Rom;
use type Hes\Cpu\Cpu;
use type Hes\Cpu\Dma;
use type Hes\Cpu\OpCode;
use type Hes\Cpu\Interrupts;
use type Hes\Bus\Keypad;
use type Hes\NesFile\NesFile;
use type Hes\Ppu\Ppu;
use type Hes\Ppu\Renderer;
use namespace Hes\Exception;

use function is_file;
use function file_get_contents;

class Nes {

  public ?Cpu $cpu;
  public ?Ppu $ppu;
  public ?CpuBus $cpuBus;
  public ?Rom $programRom;
  public ?PpuBus $ppuBus;
  public ?Dma $dma;

  public Ram $ram;
  public Keypad $keypad;
  public Interrupts $interrupts;
  public Renderer $renderer;
  public Ram $characterMem;

  public function __construct(
    protected string $canvas
  ) {
    $this->renderer = new Renderer();
    $this->keypad = new Keypad();
    $this->ram = new Ram(2048);
    $this->characterMem = new Ram(0x4000);
    $this->interrupts = new Interrupts();
  }

  //
  // Memory map
  /*
  | addr           |  description               |   mirror       |
  +----------------+----------------------------+----------------+
  | 0x0000-0x07FF  |  RAM                       |                |
  | 0x0800-0x1FFF  |  reserve                   | 0x0000-0x07FF  |
  | 0x2000-0x2007  |  I/O(PPU)                  |                |
  | 0x2008-0x3FFF  |  reserve                   | 0x2000-0x2007  |
  | 0x4000-0x401F  |  I/O(APU, etc)             |                |
  | 0x4020-0x5FFF  |  ex RAM                    |                |
  | 0x6000-0x7FFF  |  battery backup RAM        |                |
  | 0x8000-0xBFFF  |  program ROM LOW           |                |
  | 0xC000-0xFFFF  |  program ROM HIGH          |                |
  */
  public function load(
    string $nesRomFilename
  ): void {
    if (!is_file($nesRomFilename)) {
      throw new Exception\RomNotFoundException('Nes ROM file not found.');
    }
    $nesRom = NesFile::parse(file_get_contents($nesRomFilename));
    for ($i = 0; $i < C\count($nesRom->characterRom); $i++) {
      $this->characterMem->write($i, $nesRom->characterRom[$i]);
    }
    $programRom = new Rom($nesRom->programRom);
    $ppuBus = new PpuBus($this->characterMem);
    $this->ppu = new Ppu($ppuBus, $this->interrupts, $nesRom->isHorizontalMirror);
    $this->dma = new Dma($this->ram, $this->ppu);
    $ppu = $this->ppu;
    $dma = $this->dma;
    if($ppu is Ppu && $dma is Dma) {
      $this->cpu = new Cpu(
        new CpuBus(
          $this->ram,
          $programRom,
          $ppu,
          $this->keypad,
          $dma
        ),
        $this->interrupts,
        new OpCode()
      );
      $this->cpu->reset();
    }
  }

  private function frame(): void {
    while (true) {
      $cycle = 0;
      $dma = $this->dma;
      $cpu = $this->cpu;
      $ppu = $this->ppu;
      if ($dma is Dma && $cpu is Cpu && $ppu is Ppu) {
        if ($dma->isDmaProcessing()) {
          $dma->runDma();
          $cycle = 514;
        }
        $cycle += $cpu->run();
        $renderingData = $ppu->run($cycle * 3);
        if ($renderingData) {
          $cpu->bus->keypad->fetch();
          $this->renderer->render($renderingData, $this->canvas);
          break;
        }
      }
    }
  }

  public function start(): void {
    do {
      $this->frame();
    } while (true);
  }

  public function close(): void {
  }
}

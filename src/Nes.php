<?hh // strict

namespace Hes;

use namespace Hes\Ppu;
use namespace Hes\Exception;

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
use type Facebook\CLILib\OutputInterface;

use function is_file;
use function file_get_contents;

class Nes {

  public ?Cpu $cpu;
  public ?Ppu\Ppu $ppu;
  public ?CpuBus $cpuBus;
  public ?Rom $programRom;
  public ?Dma $dma;

  public Ram $ram;
  public PpuBus $ppuBus;
  public Keypad $keypad;
  public Interrupts $interrupts;
  public Ppu\Renderer $renderer;
  public Ram $characterMem;
  private NesFile $nf;

  public function __construct(
    protected Ppu\Canvas $canvas,
    protected OutputInterface $output
  ) {
    $this->renderer = new Ppu\Renderer();
    $this->keypad = new Keypad();
    $this->ram = new Ram(2048);
    $this->characterMem = new Ram(0x4000);
    $this->ppuBus = new PpuBus($this->characterMem);
    $this->interrupts = new Interrupts();
    $this->nf = new NesFile();
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
    $nesRom = $this->nf->parse(file_get_contents($nesRomFilename));
    for ($i = 0; $i < $nesRom->characterRom->count(); $i++) {
      $this->characterMem->write($i, $nesRom->characterRom[$i]);
    }

    $programRom = new Rom($nesRom->programRom);
    $this->ppu = new Ppu\Ppu($this->ppuBus, $this->interrupts, $nesRom->isHorizontalMirror);
    $this->dma = new Dma($this->ram, $this->ppu);
    if ($this->ppu is Ppu\Ppu) {
      $this->cpu = new Cpu(
        new CpuBus(
          $this->ram,
          $programRom,
          $this->ppu,
          $this->keypad,
          $this->dma
        ),
        $this->interrupts,
        new OpCode()
      );
      $this->cpu->reset();
    }
  }

  private function frame(): mixed {
    $dma = $this->dma;
    $cpu = $this->cpu;
    $ppu = $this->ppu;
    if ($dma is Dma && $cpu is Cpu && $ppu is Ppu\Ppu) {
      while (true) {
        $cycle = 0;
        if ($dma->isDmaProcessing()) {
          $dma->runDma();
          $cycle = 514;
        }
        $cycle += $cpu->run();
        $renderingData = $ppu->run($cycle * 3);
        if ($renderingData is Ppu\RenderingData) {
          $cpu->bus->keypad->fetch();
          $this->renderer->render($renderingData, $this->canvas, $this->output);
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
}

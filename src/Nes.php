<?hh // strict

namespace Ytake\Nes;

use namespace HH\Lib\C;

use type Ytake\Nes\Bus\CpuBus;
use type Ytake\Nes\Bus\PpuBus;
use type Ytake\Nes\Bus\Ram;
use type Ytake\Nes\Bus\Rom;
use type Ytake\Nes\Cpu\Cpu;
use type Ytake\Nes\Cpu\Dma;
use type Ytake\Nes\Cpu\OpCode;
use type Ytake\Nes\Cpu\Interrupts;
use type Ytake\Nes\Bus\Keypad;
use type Ytake\Nes\NesFile\NesFile;
use type Ytake\Nes\Ppu\Canvas\CanvasInterface;
use type Ytake\Nes\Ppu\Canvas\AbstractDisposeCanvas;
use type Ytake\Nes\Ppu\Ppu;
use type Ytake\Nes\Ppu\Renderer;

use function is_file;
use function file_get_contents;

class Nes {

    /** @var \Nes\Cpu\Cpu */
    public ?Cpu $cpu;
    /** @var \Nes\Ppu\Ppu */
    public ?Ppu $ppu;
    /** @var \Nes\Bus\CpuBus */
    public ?CpuBus $cpuBus;
    /** @var \Nes\Bus\Ram */
    public ?Ram $characterMem;
    /** @var \Nes\Bus\Rom */
    public ?Rom $programRom;
    /** @var \Nes\Bus\Ram */
    public ?Ram $ram;
    /** @var \Nes\Bus\PpuBus */
    public ?PpuBus $ppuBus;
    /** @var \Nes\Ppu\Renderer */
    public Renderer $renderer;
    /** @var \Nes\Bus\Keypad */
    public ?Keypad $keypad;
    /** @var \Nes\Cpu\Dma */
    public ?Dma $dma;
    /** @var \Nes\Cpu\Interrupts */
  public ?Interrupts $interrupts;

  public function __construct(
    protected string $canvas
  ) {
    $this->renderer = new Renderer();
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
    /**
     * @param string $nesRomFilename
     * @throws \Exception
     */
  public function load(string $nesRomFilename): void {
    if (! is_file($nesRomFilename)) {
      throw new \RuntimeException('Nes ROM file not found.');
    }
    $nesRom = NesFile::parse(file_get_contents($nesRomFilename));
    $keypad = new Keypad();
    $ram = new Ram(2048);
    $characterMem = new Ram(0x4000);
    for ($i = 0; $i < C\count($nesRom->characterRom); $i++) {
      $characterMem->write($i, $nesRom->characterRom[$i]);
    }
    $programRom = new Rom($nesRom->programRom);
    $ppuBus = new PpuBus($characterMem);
    $interrupts = new Interrupts();
    $this->ppu = new Ppu($ppuBus, $interrupts, $nesRom->isHorizontalMirror);
    $this->dma = new Dma($ram, $this->ppu);
    $ppu = $this->ppu;
    $dma = $this->dma;
    if($ppu is Ppu && $dma is Dma) {
      $this->cpu = new Cpu(
        new CpuBus(
          $ram,
          $programRom,
          $ppu,
          new Keypad(),
          $dma
        ),
        $interrupts,
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

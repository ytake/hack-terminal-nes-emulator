<?hh // strict

namespace Ytake\Nes;

use type Ytake\Nes\Bus\CpuBus;
use type Ytake\Nes\Bus\PpuBus;
use type Ytake\Nes\Bus\Ram;
use type Ytake\Nes\Bus\Rom;
use type Ytake\Nes\Cpu\Cpu;
use type Ytake\Nes\Cpu\Dma;
use type Ytake\Nes\Cpu\Interrupts;
use type Ytake\Nes\Bus\Keypad;
use type Ytake\Nes\NesFile\NesFile;
use type Ytake\Nes\Ppu\Canvas\CanvasInterface;
use type Ytake\Nes\Ppu\Ppu;
use type Ytake\Nes\Ppu\Renderer;

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
    CanvasInterface $canvas
  ) {
    $this->renderer = new Renderer($canvas);
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
    public function load(string $nesRomFilename)
    {
        if (! is_file($nesRomFilename)) {
            throw new \RuntimeException('Nes ROM file not found.');
        }
        $nesRomBinary = file_get_contents($nesRomFilename);
        $nesRom = NesFile::parse($nesRomBinary);
        $this->keypad = new Keypad();
        $this->ram = new Ram(2048);
        $this->characterMem = new Ram(0x4000);
        for ($i = 0; $i < count($nesRom->characterRom); $i++) {
            $this->characterMem->write($i, $nesRom->characterRom[$i]);
        }
        $this->programRom = new Rom($nesRom->programRom);
        $this->ppuBus = new PpuBus($this->characterMem);
        $this->interrupts = new Interrupts();
        $this->ppu = new Ppu($this->ppuBus, $this->interrupts, $nesRom->isHorizontalMirror);
        $this->dma = new Dma($this->ram, $this->ppu);
        $this->cpuBus = new CpuBus(
            $this->ram,
            $this->programRom,
            $this->ppu,
            $this->keypad,
            $this->dma
        );
        $this->cpu = new Cpu($this->cpuBus, $this->interrupts);
        $this->cpu->reset();
    }
    /**
     * @throws \Exception
     */
  public function frame(): void {
    while (true) {
      $cycle = 0;
      if ($this->dma->isDmaProcessing()) {
                $this->dma->runDma();
                $cycle = 514;
            }
            $cycle += $this->cpu->run();
            $renderingData = $this->ppu->run($cycle * 3);
            if ($renderingData) {
                $this->cpu->bus->keypad->fetch();
                $this->renderer->render($renderingData);
                break;
            }
        }
    }
    /**
     * @throws \Exception
     */
    public function start()
    {
        do {
            $this->frame();
        } while (true);
    }
    public function close()
    {
    }
}

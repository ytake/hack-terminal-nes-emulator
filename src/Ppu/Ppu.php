<?hh // strict

namespace Ytake\Nes\Ppu;

use Ytake\Nes\Bus\PpuBus;
use Ytake\Nes\Bus\Ram;
use Ytake\Nes\Cpu\Interrupts;

use function array_fill;
use function intval;

class Ppu {
  const int SPRITES_NUMBER = 0x100;

    // PPU power up state
    // see. https://wiki.nesdev.com/w/index.php/PPU_power_up_state
    //
    // Memory map
    /*
    | addr           |  description               |
    +----------------+----------------------------+
    | 0x0000-0x0FFF  |  Pattern table#0           |
    | 0x1000-0x1FFF  |  Pattern table#1           |
    | 0x2000-0x23BF  |  Name table                |
    | 0x23C0-0x23FF  |  Attribute table           |
    | 0x2400-0x27BF  |  Name table                |
    | 0x27C0-0x27FF  |  Attribute table           |
    | 0x2800-0x2BBF  |  Name table                |
    | 0x2BC0-0x2BFF  |  Attribute table           |
    | 0x2C00-0x2FBF  |  Name Table                |
    | 0x2FC0-0x2FFF  |  Attribute Table           |
    | 0x3000-0x3EFF  |  mirror of 0x2000-0x2EFF   |
    | 0x3F00-0x3F0F  |  background Palette        |
    | 0x3F10-0x3F1F  |  sprite Palette            |
    | 0x3F20-0x3FFF  |  mirror of 0x3F00-0x3F1F   |
    */

    /*
      Control Register1 0x2000

    | bit  | description                                 |
    +------+---------------------------------------------+
    |  7   | Assert NMI when VBlank 0: disable, 1:enable |
    |  6   | PPU master/slave, always 1                  |
    |  5   | Sprite size 0: 8x8, 1: 8x16                 |
    |  4   | Bg pattern table 0:0x0000, 1:0x1000         |
    |  3   | sprite pattern table 0:0x0000, 1:0x1000     |
    |  2   | PPU memory increment 0: +=1, 1:+=32         |
    |  1-0 | Name table 0x00: 0x2000                     |
    |      |            0x01: 0x2400                     |
    |      |            0x02: 0x2800                     |
    |      |            0x03: 0x2C00                     |
    */

    /*
      Control Register2 0x2001

    | bit  | description                                 |
    +------+---------------------------------------------+
    |  7-5 | Background color  0x00: Black               |
    |      |                   0x01: Green               |
    |      |                   0x02: Blue                |
    |      |                   0x04: Red                 |
    |  4   | Enable sprite                               |
    |  3   | Enable background                           |
    |  2   | Sprite mask       render left end           |
    |  1   | Background mask   render left end           |
    |  0   | Display type      0: color, 1: mono         |
    */

    /** @var int[] */
  public dict<int, int> $registers = dict[];
    /** @var int */
  public int $cycle = 0;
    /** @var int */
  public int $line = 0;
    /** @var bool */
  public bool $isValidVramAddr = false;
    /** @var bool */
  public bool $isLowerVramAddr = false;
    /** @var int */
  public int $spriteRamAddr = 0;
    /** @var int */
  public int $vramAddr = 0x0000;
    /** @var \Nes\Bus\Ram */
  public Ram $vram;
    /** @var int */
  public int $vramReadBuf = 0;
    /** @var \Nes\Bus\Ram */
  public Ram $spriteRam;

    /** @var \Nes\Ppu\Tile[] */
  public vec<Tile> $background = vec[];

    /** @var \Nes\Ppu\SpriteWithAttribute[] */
  public dict<int, SpriteWithAttribute> $sprites = dict[];
    /** @var \Nes\Ppu\Palette */
  public Palette $palette;

    /** @var bool */
  public bool $isHorizontalScroll = true;
    /** @var int */
  public int $scrollX = 0;
    /** @var int */
  public int $scrollY = 0;

  public function __construct(
    public PpuBus $bus, 
    public Interrupts $interrupts, 
    public bool $isHorizontalMirror
  ) {
    $this->registers = dict(array_fill(0, 7, 0));
    $this->vram = new Ram(0x2000);
    $this->spriteRam = new Ram(0x100);
    $this->palette = new Palette();
  }

  public function vramOffset(): int {
    return ($this->registers[0x00] & 0x04)? 32: 1;
  }

  public function nameTableId(): int {
    return $this->registers[0x00] & 0x03;
  }

  public function getPalette(): dict<int, int> {
    return $this->palette->read();
  }

  public function clearSpriteHit(): void {
    $this->registers[0x02] &= 0xbf;
  }

  public function setSpriteHit(): void {
    $this->registers[0x02] |= 0x40;
  }

  public function hasSpriteHit(): bool {
    $y = $this->spriteRam->read(0);
    return ($y === $this->line) && $this->isBackgroundEnable() && $this->isSpriteEnable();
  }

  public function hasVblankIrqEnabled(): bool {
    return !!($this->registers[0] & 0x80);
  }

  public function isBackgroundEnable(): bool {
    return !!($this->registers[0x01] & 0x08);
  }

  public function isSpriteEnable(): bool {
    return !!($this->registers[0x01] & 0x10);
  }

        /*
          Name table id and address
          +------------+------------+
          |            |            |
          |  0(0x2000) |  1(0x2400) |
          |            |            |
          +------------+------------+
          |            |            |
          |  2(0x2800) |  3(0x2C00) |
          |            |            |
          +------------+------------+
        */
  public function scrollTileX(): int {
    return ~~intval(($this->scrollX + (($this->nameTableId() % 2) * 256)) / 8);
  }

  public function scrollTileY(): int {
    return ~~intval((($this->scrollY + (~~intval($this->nameTableId() / 2) * 240)) / 8));
  }

  public function tileY(): int {
    return ~~(intval($this->line / 8)) + $this->scrollTileY(); 
  }

  public function backgroundTableOffset(): int {
    return ($this->registers[0] & 0x10) ? 0x1000 : 0x0000;
  }

  public function setVblank(): void {
    $this->registers[0x02] |= 0x80;
  }

  public function isVblank(): bool {
    return !!($this->registers[0x02] & 0x80);
  }

  public function clearVblank(): void {
    $this->registers[0x02] &= 0x7F;
  }

  public function getBlockId(int $tileX, int $tileY): int {
        return ~~intval(($tileX % 4) / 2) + (~~intval(($tileY % 4) / 2)) * 2;
    }

    public function getAttribute(int $tileX, int $tileY, int $offset): int
    {
        $addr = ~~intval($tileX / 4) + (~~intval($tileY / 4) * 8) + 0x03C0 + $offset;
        return $this->vram->read($this->mirrorDownSpriteAddr($addr));
    }

    public function getSpriteId(int $tileX, int $tileY, int $offset): int
    {
        $tileNumber = $tileY * 32 + $tileX;
        $spriteAddr = $this->mirrorDownSpriteAddr($tileNumber + $offset);
        return $this->vram->read($spriteAddr);
    }

    public function mirrorDownSpriteAddr(int $addr): int
    {
        if (! $this->isHorizontalMirror) {
            return $addr;
        }
        if ($addr >= 0x0400 && $addr < 0x0800 && $addr >= 0x0C00) {
            return $addr - 0x400;
        }
        return $addr;
    }

    // The PPU draws one line at 341 clocks and prepares for the next line.
    // While drawing the BG and sprite at the first 256 clocks,
    // it searches for sprites to be drawn on the next scan line.
    // Get the pattern of the sprite searched with the remaining clock.
    /**
     * @param int $cycle
     *
     * @return \Nes\Ppu\RenderingData|null
     */
  public function run(int $cycle): ?RenderingData {
    $this->cycle += $cycle;
    if ($this->line === 0) {
      $this->buildSprites();
    }

    if ($this->cycle >= 341) {
      $this->cycle -= 341;
      $this->line++;

    if ($this->hasSpriteHit()) {
      $this->setSpriteHit();
    }

    if ($this->line <= 240 && $this->line % 8 === 0 && $this->scrollY <= 240) {
      $this->buildBackground();
    }
    if ($this->line === 241) {
      $this->setVblank();
      if ($this->hasVblankIrqEnabled()) {
        $this->interrupts->assertNmi();
      }
    }

            if ($this->line === 262) {
                $this->clearVblank();
                $this->clearSpriteHit();
                $this->line = 0;
                $this->interrupts->deassertNmi();
                return new RenderingData(
                    $this->getPalette(),
                    $this->isBackgroundEnable() ? $this->background : null,
                    $this->isSpriteEnable() ? $this->sprites : null
                );
            }
        }
        return null;
    }

  public function buildTile(int $tileX, int $tileY, int $offset): Tile {
    // INFO see. http://hp.vector.co.jp/authors/VA042397/nes/ppu.html
    $blockId = $this->getBlockId($tileX, $tileY);
    $spriteId = $this->getSpriteId($tileX, $tileY, $offset);
    $attr = $this->getAttribute($tileX, $tileY, $offset);
    $paletteId = ($attr >> ($blockId * 2)) & 0x03;
    $sprite = $this->buildSprite($spriteId, $this->backgroundTableOffset());
    return new Tile(
      $sprite,
      $paletteId,
      $this->scrollX,
      $this->scrollY
    );
  }

    public function buildBackground(): void
    {
        // INFO: Horizontal offsets range from 0 to 255. "Normal" vertical offsets range from 0 to 239,
        // while values of 240 to 255 are treated as -16 through -1 in a way, but tile data is incorrectly
        // fetched from the attribute table.
        $clampedTileY = $this->tileY() % 30;
        $tableIdOffset = (~~intval($this->tileY() / 30) % 2) ? 2 : 0;
        // background of a line.
        // Build viewport + 1 tile for background scroll.
        for ($x = 0; $x < 32 + 1; $x = ($x + 1) | 0) {
            $tileX = ($x + $this->scrollTileX());
            $clampedTileX = $tileX % 32;
            $nameTableId = (~~intval($tileX / 32) % 2) + $tableIdOffset;
            $offsetAddrByNameTable = $nameTableId * 0x400;
            $tile = $this->buildTile($clampedTileX, $clampedTileY, $offsetAddrByNameTable);
            $this->background[] = $tile;
        }
    }

    public function buildSprites(): void
    {
        $offset = ($this->registers[0] & 0x08) ? 0x1000 : 0x0000;
        for ($i = 0; $i < self::SPRITES_NUMBER; $i = ($i + 4) | 0) {
            // INFO: Offset sprite Y position, because First and last 8line is not rendered.
            $y = $this->spriteRam->read($i) - 8;
            if ($y < 0) {
                return;
            }
            $spriteId = $this->spriteRam->read($i + 1);
            $attr = $this->spriteRam->read($i + 2);
            $x = $this->spriteRam->read($i + 3);
            $sprite = $this->buildSprite($spriteId, $offset);
            $this->sprites[intval($i / 4)] = new SpriteWithAttribute($sprite, $x, $y, $attr, $spriteId);
        }
    }

  public function buildSprite(int $spriteId, int $offset): dict<int, dict<int, int>> {
    $sprite = dict(array_fill(0, 8, dict(array_fill(0, 8, 0))));
    for ($i = 0; $i < 16; $i = ($i + 1) | 0) {
      for ($j = 0; $j < 8; $j = ($j + 1) | 0) {
        $addr = $spriteId * 16 + $i + $offset;
        $ram = $this->readCharacterRAM($addr);
        if ($ram & (0x80 >> $j)) {
          $sprite[$i % 8][$j] += 0x01 << ~~intval($i / 8);
        }
      }
    }
    return $sprite;
  }

    public function readCharacterRAM(int $addr): int
    {
        return $this->bus->readByPpu($addr);
    }

    public function writeCharacterRAM(int $addr, int $data): void
    {
        $this->bus->writeByPpu($addr, $data);
    }

    public function readVram(): int
    {
        $buf = $this->vramReadBuf;
        if ($this->vramAddr >= 0x2000) {
            $addr = $this->calcVramAddr();
            $this->vramAddr += $this->vramOffset();
            if ($addr >= 0x3F00) {
                return $this->vram->read($addr);
            }
            $this->vramReadBuf = $this->vram->read($addr);
        } else {
            $this->vramReadBuf = $this->readCharacterRAM($this->vramAddr);
            $this->vramAddr += $this->vramOffset();
        }
        return $buf;
    }

    public function read(int $addr): int
    {
        /*
        | bit  | description                                 |
        +------+---------------------------------------------+
        | 7    | 1: VBlank clear by reading this register    |
        | 6    | 1: sprite hit                               |
        | 5    | 0: less than 8, 1: 9 or more                |
        | 4-0  | invalid                                     |
        |      | bit4 VRAM write flag [0: success, 1: fail]  |
        */
        if ($addr === 0x0002) {
            $this->isHorizontalScroll = true;
            $data = $this->registers[0x02];
            $this->clearVblank();
            // $this->clearSpriteHit();
            return $data;
        }
        // Write OAM data here. Writes will increment OAMADDR after the write
        // reads during vertical or forced blanking return the value from OAM at that address but do not increment.
        if ($addr === 0x0004) {
            return $this->spriteRam->read($this->spriteRamAddr);
        }
        if ($addr === 0x0007) {
            return $this->readVram();
        }
        return 0;
    }

  public function write(int $addr, int $data): void {
    if ($addr === 0x0003) {
      $this->writeSpriteRamAddr($data);
    }
    if ($addr === 0x0004) {
      $this->writeSpriteRamData($data);
    }
    if ($addr === 0x0005) {
      $this->writeScrollData($data);
    }
    if ($addr === 0x0006) {
      $this->writeVramAddr($data);
    }
    if ($addr === 0x0007) {
      $this->writeVramData($data);
    }
    $this->registers[$addr] = $data;
  }

  public function writeSpriteRamAddr(int $data): void {
    $this->spriteRamAddr = $data;
  }

  public function writeSpriteRamData(int $data): void {
    $this->spriteRam->write($this->spriteRamAddr, $data);
    $this->spriteRamAddr += 1;
  }

  public function writeScrollData(int $data): void {
    if ($this->isHorizontalScroll) {
      $this->isHorizontalScroll = false;
      $this->scrollX = $data & 0xFF;
    } else {
      $this->scrollY = $data & 0xFF;
      $this->isHorizontalScroll = true;
    }
  }

  public function writeVramAddr(int $data): void {
    if ($this->isLowerVramAddr) {
      $this->vramAddr += $data;
      $this->isLowerVramAddr = false;
      $this->isValidVramAddr = true;
    } else {
      $this->vramAddr = $data << 8;
      $this->isLowerVramAddr = true;
      $this->isValidVramAddr = false;
    }
  }

  public function calcVramAddr(): int {
    return ($this->vramAddr >= 0x3000 && $this->vramAddr < 0x3f00)
      ? $this->vramAddr -= 0x3000
      : $this->vramAddr - 0x2000;
  }

  public function writeVramData(int $data): void {
    if ($this->vramAddr >= 0x2000) {
      if ($this->vramAddr >= 0x3f00 && $this->vramAddr < 0x4000) {
        $this->palette->write($this->vramAddr - 0x3f00, $data);
      } else {
        $this->writeVram($this->calcVramAddr(), $data);
      }
    } else {
      $this->writeCharacterRAM($this->vramAddr, $data);
    }
    $this->vramAddr += $this->vramOffset();
  }

  public function writeVram(int $addr, int $data): void {
    $this->vram->write($addr, $data);
  }

  public function transferSprite(int $index, int $data): void {
    $addr = $index + $this->spriteRamAddr;
    $this->spriteRam->write($addr % 0x100, $data);
  }
}

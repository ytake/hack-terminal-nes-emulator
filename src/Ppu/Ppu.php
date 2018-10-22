<?hh // strict

namespace Hes\Ppu;

use type Hes\Bus\PpuBus;
use type Hes\Bus\Ram;
use type Hes\Cpu\Interrupts;

use function array_fill;
use function intval;

class Ppu {
  const int SPRITES_NUMBER = 0x100;
  public dict<int, int> $registers = dict[];
  public int $cycle = 0;
  public int $line = 0;
  public bool $isValidVramAddr = false;
  public bool $isLowerVramAddr = false;
  public int $spriteRamAddr = 0;
  public int $vramAddr = 0x0000;
  public Ram $vram;
  public int $vramReadBuf = 0;
  public Ram $spriteRam;
  public Vector<Tile> $background = Vector{};
  public dict<int, SpriteWithAttribute> $sprites = dict[];
  public Palette $palette;
  public bool $isHorizontalScroll = true;
  public int $scrollX = 0;
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

  <<__Rx>>
  public function vramOffset(): int {
    return ($this->registers[0x00] & 0x04)? 32: 1;
  }

  <<__Rx>>
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

  <<__Rx>>
  public function hasSpriteHit(): bool {
    return $this->spriteRam->read(0)
      |> ($$ === $this->line) && $this->isBackgroundEnable() && $this->isSpriteEnable();
  }

  <<__Rx>>
  public function hasVblankIrqEnabled(): bool {
    return !!($this->registers[0] & 0x80);
  }

  <<__Rx>>
  public function isBackgroundEnable(): bool {
    return !!($this->registers[0x01] & 0x08);
  }

  <<__Rx>>
  public function isSpriteEnable(): bool {
    return !!($this->registers[0x01] & 0x10);
  }

  <<__Rx>>
  public function scrollTileX(): int {
    return ~~intval(($this->scrollX + (($this->nameTableId() % 2) * 256)) / 8);
  }

  <<__Rx>>
  public function scrollTileY(): int {
    return ~~intval((($this->scrollY + (~~intval($this->nameTableId() / 2) * 240)) / 8));
  }

  <<__Rx>>
  public function tileY(): int {
    return ~~(intval($this->line / 8)) + $this->scrollTileY();
  }

  <<__Rx>>
  public function backgroundTableOffset(): int {
    return ($this->registers[0] & 0x10) ? 0x1000 : 0x0000;
  }

  public function setVblank(): void {
    $this->registers[0x02] |= 0x80;
  }

  <<__Rx>>
  public function isVblank(): bool {
    return !!($this->registers[0x02] & 0x80);
  }

  public function clearVblank(): void {
    $this->registers[0x02] &= 0x7F;
  }

  <<__Rx>>
  public function getBlockId(int $tileX, int $tileY): int {
    return ~~intval(($tileX % 4) / 2) + (~~intval(($tileY % 4) / 2)) * 2;
  }

  <<__Rx>>
  public function getAttribute(int $tileX, int $tileY, int $offset): int {
    $addr = ~~intval($tileX / 4) + (~~intval($tileY / 4) * 8) + 0x03C0 + $offset;
    return $this->vram->read($this->mirrorDownSpriteAddr($addr));
  }

  public function getSpriteId(int $tileX, int $tileY, int $offset): int {
    $tileNumber = $tileY * 32 + $tileX;
    return $this->vram->read(
      $this->mirrorDownSpriteAddr($tileNumber + $offset)
    );
  }

  <<__Rx>>
  public function mirrorDownSpriteAddr(int $addr): int {
    if (! $this->isHorizontalMirror) {
      return $addr;
    }
    if (($addr >= 0x0400 && $addr < 0x0800) || $addr >= 0x0C00) {
      return $addr - 0x400;
    }
    return $addr;
  }

  public function run(int $cycle): ?RenderingData {
    $this->cycle += $cycle;
    if ($this->line === 0) {
      $this->background = Vector{};
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

  public function buildBackground(): void {
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
      $this->background[] = $this->buildTile(
        $clampedTileX,
        $clampedTileY,
        $offsetAddrByNameTable
      );
    }
  }

  private function offsetRegister(): int {
    return ($this->registers[0] & 0x08) ? 0x1000 : 0x0000;
  }

  public function buildSprites(): void {
    $offset = $this->offsetRegister();
    for ($i = 0; $i < self::SPRITES_NUMBER; $i = ($i + 4) | 0) {
      // INFO: Offset sprite Y position, because First and last 8line is not rendered.
      $y = $this->spriteRam->read($i) - 8;
      if ($y < 0) {
        return;
      }
      $spriteId = $this->spriteRam->read($i + 1);
      $this->sprites[intval($i / 4)] = new SpriteWithAttribute(
        $this->buildSprite($spriteId, $offset),
        $this->spriteRam->read($i + 3),
        $y,
        $this->spriteRam->read($i + 2),
        $spriteId
      );
    }
  }

  <<__Rx>>
  public function buildSprite(int $spriteId, int $offset): dict<int, dict<int, int>> {
    $sprite = dict(array_fill(0, 8, dict(array_fill(0, 8, 0))));
    for ($i = 0; $i < 16; $i = ($i + 1) | 0) {
      for ($j = 0; $j < 8; $j = ($j + 1) | 0) {
        $ram = $this->readCharacterRAM($spriteId * 16 + $i + $offset);
        if ($ram & (0x80 >> $j)) {
          $sprite[$i % 8][$j] += 0x01 << ~~intval($i / 8);
        }
      }
    }
    return $sprite;
  }

  <<__Rx>>
  public function readCharacterRAM(int $addr): int {
    return $this->bus->readByPpu($addr);
  }

  public function writeCharacterRAM(int $addr, int $data): void {
    $this->bus->writeByPpu($addr, $data);
  }

  public function readVram(): int {
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

  public function read(int $addr): int {
    if ($addr === 0x0002) {
      $this->isHorizontalScroll = true;
      $data = $this->registers[0x02];
      $this->clearVblank();
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

<?hh // strict

namespace Hes\Ppu;

final class Tile {

  public function __construct(
    public dict<int, dict<int, int>> $pattern,
    public int $paletteId,
    public int $scrollX,
    public int $scrollY
  ) {}
}

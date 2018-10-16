<?hh // strict 

namespace Ytake\Nes\Ppu;

final class Tile {

  public function __construct(
    public Map<int, Map<int, int>> $pattern, 
    public int $paletteId, 
    public int $scrollX, 
    public int $scrollY
  ) {}
}

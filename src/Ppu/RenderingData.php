<?hh // strict

namespace Ytake\Nes\Ppu;

final class RenderingData {

  public function __construct(
    public dict<int, mixed> $palette, 
    public ?vec<Tile> $background, 
    public ?dict<int, SpriteWithAttribute> $sprites
  ) { }
}

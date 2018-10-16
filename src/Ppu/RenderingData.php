<?hh // strict

namespace Ytake\Nes\Ppu;

final class RenderingData {

  public function __construct(
    public dict<int, mixed> $palette, 
    public ?Map<int, Tile> $background, 
    public ?Map<int, SpriteWithAttribute> $sprites
  ) { }
}

<?hh // strict

namespace Hes\Ppu;

final class RenderingData {

  public function __construct(
    public dict<int, mixed> $palette,
    public ?Vector<Tile> $background,
    public ?dict<int, SpriteWithAttribute> $sprites
  ) { }
}

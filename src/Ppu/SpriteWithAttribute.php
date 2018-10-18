<?hh // strict

namespace Ytake\Nes\Ppu;

final class SpriteWithAttribute {

  public function __construct(
    public dict<int, dict<int, int>> $sprite,
    public int $x,
    public int $y,
    public int $attribute,
    public int $id
  ) {}
}

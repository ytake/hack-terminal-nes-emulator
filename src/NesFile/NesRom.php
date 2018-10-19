<?hh // strict

namespace Hes\NesFile;

final class NesRom {

  public function __construct(
    public bool $isHorizontalMirror,
    public vec<int> $programRom,
    public vec<int> $characterRom
  ) {}
}

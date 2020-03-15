namespace Hes\NesFile;

final class NesRom {

  public function __construct(
    public bool $isHorizontalMirror,
    public Vector<int> $programRom,
    public Vector<int> $characterRom
  ) {}
}

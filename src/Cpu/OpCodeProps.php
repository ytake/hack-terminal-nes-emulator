<?hh // strict

namespace Hes\Cpu;

final class OpCodeProps {

  public function __construct(
    public string $fullName,
    public string $baseName,
    public Addressing $mode,
    public int $cycle
  ) {}
}

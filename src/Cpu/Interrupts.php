<?hh // strict

namespace Ytake\Nes\Cpu;

final class Interrupts {

  private bool $nmi = false;

  private bool $irq = false;

  public function isNmiAssert(): bool {
    return $this->nmi;
  }

  public function isIrqAssert(): bool {
    return $this->irq;
  }

  public function assertNmi(): void {
    $this->nmi = true;
  }

  public function deassertNmi(): void {
    $this->nmi = false;
  }

  public function assertIrq(): void {
    $this->nmi = true;
  }

  public function deassertIrq(): void {
    $this->nmi = false;
  }
}

<?hh // strict

namespace HesTest\Cpu;

use type Hes\Cpu\OpCode;
use type Facebook\HackTest\HackTest;
use function Facebook\FBExpect\expect;

final class OpCodeTest extends HackTest {

  public function testShouldReturnImmMapCodes(): void {
    $o = new OpCode();
    expect($o->getOpCodes())->toBeSame($o->getOpCodes());
    expect($o->getOpCodes()->get('A9'))->toBeSame($o->getOpCodes()->get('A9'));
  }
}

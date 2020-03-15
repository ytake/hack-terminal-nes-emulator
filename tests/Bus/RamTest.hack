<?hh // strict

namespace HesTest\Bus;

use namespace HH\Lib\C;

use type Hes\Bus\Ram;
use type Facebook\HackTest\HackTest;
use function Facebook\FBExpect\expect;

final class RamTest extends HackTest {

  public function testShouldReturnRamVec(): void {
    $ram = new Ram(64);
    expect($ram->read(1))->toBeSame(0);
    $ram->write(1, 64);
    expect($ram->read(1))->toBeSame(64);
  }

  public function testShouldBeVecReset(): void {
    $ram = new Ram(64);
    $ram->reset();
    expect(C\count($ram->every()))->toNotBeSame(64);
  }
}

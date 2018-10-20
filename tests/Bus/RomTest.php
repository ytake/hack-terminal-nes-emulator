<?hh // strict

namespace HesTest\Bus;

use type Hes\Bus\Rom;
use type Facebook\HackTest\HackTest;
use function Facebook\FBExpect\expect;

final class RomTest extends HackTest {

  public function testShouldBeInt(): void {
    $rom = new Rom(Vector{1, 2, 3, 4});
    expect($rom->size())->toBeSame(4);
    expect($rom->read(1))->toBeSame(2);
  }

  public function testShouldThrowRuntimeException(): void {
    $rom = new Rom(Vector{});
    expect(() ==> $rom->read(1))
      ->toThrow(\RuntimeException::class);
  }
}

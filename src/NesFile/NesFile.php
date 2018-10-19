<?hh // strict

namespace Ytake\Nes\NesFile;

use namespace HH\Lib\{Vec, C, Str};
use type Ytake\Nes\Exception\NesFormatException;

use function substr;
use function strlen;
use function ord;
use function printf;
use function count;
use function dechex;

class NesFile {

  const int NES_HEADER_SIZE = 0x0010;
  const int PROGRAM_ROM_SIZE = 0x4000;
  const int CHARACTER_ROM_SIZE = 0x2000;
  
  public static function parse(string $nesBuffer): NesRom {
    self::invariantNes($nesBuffer);
    $nes = vec[];
    for ($i = 0; $i < Str\length($nesBuffer); ++$i) {
      $nes[$i] = (ord($nesBuffer[$i]) & 0xFF);
    }
    printf("Rom size: %d (0x%s)\n", C\count($nes), dechex(C\count($nes)));
    $programRomPages = $nes[4];
    printf("Program ROM pages: %d\n", $programRomPages);
    $characterRomPages = $nes[5];
    printf("Character ROM pages: %d\n", $characterRomPages);
    $isHorizontalMirror = !($nes[6] & 0x01);
    $mapper = ((($nes[6] & 0xF0) >> 4) | $nes[7] & 0xF0);
    printf("Mapper: %d\n", $mapper);
    $characterRomStart = self::NES_HEADER_SIZE + $programRomPages * self::PROGRAM_ROM_SIZE;
    $characterRomEnd = $characterRomStart + $characterRomPages * self::CHARACTER_ROM_SIZE;
    printf("Character ROM start: 0x%s (%d)\n", dechex($characterRomStart), $characterRomStart);
    printf("Character ROM end: 0x%s (%d)\n", dechex($characterRomEnd), $characterRomEnd);

    $nesRom = new NesRom(
      $isHorizontalMirror,
      Vec\slice($nes, self::NES_HEADER_SIZE, ($characterRomStart - 1) - self::NES_HEADER_SIZE),
      Vec\slice($nes, $characterRomStart, ($characterRomEnd - 1) - $characterRomStart)
    );
    printf(
      "Program   ROM: 0x0000 - 0x%s (%d bytes)\n",
      dechex(C\count($nesRom->programRom)),
      C\count($nesRom->programRom)
    );
    printf(
      "Character ROM: 0x0000 - 0x%s (%d bytes)\n",
      dechex(C\count($nesRom->characterRom)),
      C\count($nesRom->characterRom)
    );
    return $nesRom;
  }

  <<__Rx>>
  private static function invariantNes(string $buff): void {
    if (substr($buff, 0, 3) !== 'NES') {
      throw new NesFormatException('This file is not NES format.');
    }
  }
}

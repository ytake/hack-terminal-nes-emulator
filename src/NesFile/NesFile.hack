namespace Hes\NesFile;

use namespace HH\Lib\Str;
use type Hes\Exception\NesFormatException;

use function ord;
use function printf;
use function dechex;

class NesFile {

  const int NES_HEADER_SIZE = 0x0010;
  const int PROGRAM_ROM_SIZE = 0x4000;
  const int CHARACTER_ROM_SIZE = 0x2000;

  public function parse(string $nesBuffer): NesRom {
    $nes = Map{};
    self::invariantNes($nesBuffer);
    for ($i = 0; $i < Str\length($nesBuffer); $i++) {
      $nes->add(Pair{$i, ord($nesBuffer[$i]) & 0xFF});
    }
    printf("Rom size: %d (0x%s)\n", $nes->count(), dechex($nes->count()));
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
      $nes->slice(self::NES_HEADER_SIZE, ($characterRomStart - 1) - self::NES_HEADER_SIZE)->concat(Map{}),
      $nes->slice($characterRomStart, ($characterRomEnd - 1) - $characterRomStart)->concat(Map{})
    );
    printf(
      "Program   ROM: 0x0000 - 0x%s (%d bytes)\n",
      dechex($nesRom->programRom->count()),
      $nesRom->programRom->count()
    );
    printf(
      "Character ROM: 0x0000 - 0x%s (%d bytes)\n",
      dechex($nesRom->characterRom->count()),
      $nesRom->characterRom->count()
    );
    return $nesRom;
  }

  private static function invariantNes(string $buff): void {
    if (Str\slice($buff, 0, 3) !== 'NES') {
      throw new NesFormatException('This file is not NES format.');
    }
  }
}

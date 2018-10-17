<?hh 

namespace Ytake\Nes;

use function printf;

final class Debugger {

  public static function dump(array $array): void {
    foreach ($array as $idx => $byte) {
      if ($idx % 16 == 0) {
        printf("\n%04x ", $idx);
      }
      if ($idx % 8 == 0) {
        printf(" ");
      }
      printf('%02x ', $byte);
    }
  }
}

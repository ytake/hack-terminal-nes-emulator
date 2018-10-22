<?hh // strict

namespace Hes;

use type Hes\Nes;
use type Facebook\CLILib\CLIWithRequiredArguments;
use namespace Facebook\CLILib\CLIOptions;

use const PHP_EOL;

final class Application extends CLIWithRequiredArguments {

  private Ppu\Canvas $canvas = Ppu\Canvas::PNG;

  <<__Override>>
  public static function getHelpTextForRequiredArguments(): vec<string> {
    return vec['file'];
  }

  <<__Override>>
  public async function mainAsync(): Awaitable<int> {
    $arg = $this->getArguments();
    $filename = $arg[0];
    $nes = new Nes($this->canvas, $this->getStdout());
    try {
      $nes->load($filename);
    } catch (\Exception $e) {
      echo $e->getMessage();
      return 1;
    }
    $nes->start();
    return 0;
  }

  <<__Override>>
  protected function getSupportedOptions(
  ): vec<CLIOptions\CLIOption> {
	  return vec[
	    CLIOptions\with_required_enum(
        Ppu\Canvas::class,
	      $s ==> { $this->canvas = $s; },
		    'Canvas to display screen.'.PHP_EOL.'Option: terminal (default), png, null',
		    '--canvas',
		    '-c',
	    ),
    ];
  }
}

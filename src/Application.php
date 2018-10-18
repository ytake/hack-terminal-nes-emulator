<?hh // strict

namespace Ytake\Nes;

use type Facebook\CLILib\CLIBase;
use type Facebook\CLILib\CLIWithRequiredArguments;
use namespace Facebook\CLILib\CLIOptions;
use namespace Ytake\Nes\Ppu\Canvas;

use const PHP_EOL;
use function ucfirst;
use function strtolower;
use function sprintf;
use function array_key_exists;

final class Application extends CLIWithRequiredArguments {
  
  private string $canvas = '';
  
  private dict<string, classname<Canvas\AbstractDisposeCanvas>> $v = dict[
    'terminal' => Canvas\TerminalCanvas::class,
    'null' =>  Canvas\NullCanvas::class,
    'png' => Canvas\PngCanvas::class,
  ];

  <<__Override>>
  public static function getHelpTextForRequiredArguments(): vec<string> {
    return vec['file'];
  }

  <<__Override>>
  public async function mainAsync(): Awaitable<int> {
    $arg = $this->getArguments();
    $filename = $arg[0];
    if ($this->canvas !== '') {
      $canvasName = ucfirst(strtolower($this->canvas));
      $cn = Canvas\TerminalCanvas::class;
      if(array_key_exists($canvasName, $this->v)) {
        $cn = $this->v[$canvasName];
      }
      $nes = new \Ytake\Nes\Nes($cn);
      try {
        $nes->load($filename);
      } catch (\Exception $e) {
        echo $e->getMessage();
      }
      $nes->start();
    }
    return 0;
  }

  <<__Override>>
  protected function getSupportedOptions(
  ): vec<CLIOptions\CLIOption> {
	return vec[
	  CLIOptions\with_required_string(
	    $s ==> { $this->canvas = $s; },
		'Canvas to display screen.'.PHP_EOL.'Option: terminal (default), png, null',
		'--canvas',
		'-c',
	  ),
    ];
  }
}

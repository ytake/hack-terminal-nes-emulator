# An NES emulator written in HHVM/Hack

Based on [bokuweb/flownes](https://github.com/bokuweb/flownes), [gabrielrcouto/php-terminal-gameboy-emulator](https://github.com/gabrielrcouto/php-terminal-gameboy-emulator).

[hasegawa-tomoki/php-terminal-nes-emulator](https://github.com/hasegawa-tomoki/php-terminal-nes-emulator) For HHVM/Hack

## Require

HHVM >= 3.28 (*Not Supoorted PHP*)

## Install

### Composer

```bash
$ hhvm $(which composer) install
```

## Usage

### Measuring FPS

```bash
$ hhvm ./boot testing.nes -c null
```

### ScreenShot

```bash
$ hhvm ./boot testing.nes -c png
```

### Terminal

WIP

## Credit

- [bokuweb/flownes](https://github.com/bokuweb/flownes)
- [php-terminal-gameboy-emulator](https://github.com/gabrielrcouto/php-terminal-gameboy-emulator)  
- [hasegawa-tomoki/php-terminal-nes-emulator](https://github.com/hasegawa-tomoki/php-terminal-nes-emulator)

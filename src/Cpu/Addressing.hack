namespace Hes\Cpu;

enum Addressing: string as string {
  Immediate = 'immediate';
  ZeroPage = 'zeroPage';
  Relative = 'relative';
  Implied = 'implied';
  Absolute = 'absolute';
  Accumulator = 'accumulator';
  ZeroPageX = 'zeroPageX';
  ZeroPageY = 'zeroPageY';
  AbsoluteX = 'absoluteX';
  AbsoluteY = 'absoluteY';
  PreIndexedIndirect = 'preIndexedIndirect';
  PostIndexedIndirect = 'postIndexedIndirect';
  IndirectAbsolute = 'indirectAbsolute';
}

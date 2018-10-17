<?hh // strict

namespace Ytake\Nes\Cpu;

final class OpCode {

  private ImmVector<int> $cycle = ImmVector{
    7, 6, 2, 8, 3, 3, 5, 5, 3, 2, 2, 2, 4, 4, 6, 6,
    2, 5, 2, 8, 4, 4, 6, 6, 2, 4, 2, 7, 4, 4, 6, 7,
    6, 6, 2, 8, 3, 3, 5, 5, 4, 2, 2, 2, 4, 4, 6, 6,
    2, 5, 2, 8, 4, 4, 6, 6, 2, 4, 2, 7, 4, 4, 6, 7,
    6, 6, 2, 8, 3, 3, 5, 5, 3, 2, 2, 2, 3, 4, 6, 6,
    2, 5, 2, 8, 4, 4, 6, 6, 2, 4, 2, 7, 4, 4, 6, 7,
    6, 6, 2, 8, 3, 3, 5, 5, 4, 2, 2, 2, 5, 4, 6, 6,
    2, 5, 2, 8, 4, 4, 6, 6, 2, 4, 2, 7, 4, 4, 6, 7,
    2, 6, 2, 6, 3, 3, 3, 3, 2, 2, 2, 2, 4, 4, 4, 4,
    2, 6, 2, 6, 4, 4, 4, 4, 2, 4, 2, 5, 5, 4, 5, 5,
    2, 6, 2, 6, 3, 3, 3, 3, 2, 2, 2, 2, 4, 4, 4, 4,
    2, 5, 2, 5, 4, 4, 4, 4, 2, 4, 2, 4, 4, 4, 4, 4,
    2, 6, 2, 8, 3, 3, 5, 5, 2, 2, 2, 2, 4, 4, 6, 6,
    2, 5, 2, 8, 4, 4, 6, 6, 2, 4, 2, 7, 4, 4, 7, 7,
    2, 6, 3, 8, 3, 3, 5, 5, 2, 2, 2, 2, 4, 4, 6, 6,
    2, 5, 2, 8, 4, 4, 6, 6, 2, 4, 2, 7, 4, 4, 7, 7,
  };

  <<__Memoize>>
  public function getOpCodes(): ImmMap<string, OpCodeProps> {
    return ImmMap{
      'A9' => new OpCodeProps('LDA_IMM', 'LDA', Addressing::Immediate, $this->cycle[0xA9]),
      'A5' => new OpCodeProps('LDA_ZERO', 'LDA', Addressing::ZeroPage, $this->cycle[0xA5]),
      'AD' => new OpCodeProps('LDA_ABS', 'LDA', Addressing::Absolute, $this->cycle[0xAD]),
      'B5' => new OpCodeProps('LDA_ZEROX', 'LDA', Addressing::ZeroPageX, $this->cycle[0xB5]),
      'BD' => new OpCodeProps('LDA_ABSX', 'LDA', Addressing::AbsoluteX, $this->cycle[0xBD]),
      'B9' => new OpCodeProps('LDA_ABSY', 'LDA', Addressing::AbsoluteY, $this->cycle[0xB9]),
      'A1' => new OpCodeProps('LDA_INDX', 'LDA', Addressing::PreIndexedIndirect, $this->cycle[0xA1]),
      'B1' => new OpCodeProps('LDA_INDY', 'LDA', Addressing::PostIndexedIndirect, $this->cycle[0xB1]),
      'A2' => new OpCodeProps('LDX_IMM', 'LDX', Addressing::Immediate, $this->cycle[0xA2]),
      'A6' => new OpCodeProps('LDX_ZERO', 'LDX', Addressing::ZeroPage, $this->cycle[0xA6]),
      'AE' => new OpCodeProps('LDX_ABS', 'LDX', Addressing::Absolute, $this->cycle[0xAE]),
      'B6' => new OpCodeProps('LDX_ZEROY', 'LDX', Addressing::ZeroPageY, $this->cycle[0xB6]),
      'BE' => new OpCodeProps('LDX_ABSY', 'LDX', Addressing::AbsoluteY, $this->cycle[0xBE]),
      'A0' => new OpCodeProps('LDY_IMM', 'LDY', Addressing::Immediate, $this->cycle[0xA0]),
      'A4' => new OpCodeProps('LDY_ZERO', 'LDY', Addressing::ZeroPage, $this->cycle[0xA4]),
      'AC' => new OpCodeProps('LDY_ABS', 'LDY', Addressing::Absolute, $this->cycle[0xAC]),
      'B4' => new OpCodeProps('LDY_ZEROX', 'LDY', Addressing::ZeroPageX, $this->cycle[0xB4]),
      'BC' => new OpCodeProps('LDY_ABSX', 'LDY', Addressing::AbsoluteX, $this->cycle[0xBC]),
      '85' => new OpCodeProps('STA_ZERO', 'STA', Addressing::ZeroPage, $this->cycle[0x85]),
      '8D' => new OpCodeProps('STA_ABS', 'STA', Addressing::Absolute, $this->cycle[0x8D]),
      '95' => new OpCodeProps('STA_ZEROX', 'STA', Addressing::ZeroPageX, $this->cycle[0x95]),
      '9D' => new OpCodeProps('STA_ABSX', 'STA', Addressing::AbsoluteX, $this->cycle[0x9D]),
      '99' => new OpCodeProps('STA_ABSY', 'STA', Addressing::AbsoluteY, $this->cycle[0x99]),
      '81' => new OpCodeProps('STA_INDX', 'STA', Addressing::PreIndexedIndirect, $this->cycle[0x81]),
      '91' => new OpCodeProps('STA_INDY', 'STA', Addressing::PostIndexedIndirect, $this->cycle[0x91]),
      '86' => new OpCodeProps('STX_ZERO', 'STX', Addressing::ZeroPage, $this->cycle[0x86]),
      '8E' => new OpCodeProps('STX_ABS', 'STX', Addressing::Absolute, $this->cycle[0x8E]),
      '96' => new OpCodeProps('STX_ZEROY', 'STX', Addressing::ZeroPageY, $this->cycle[0x96]),
      '84' => new OpCodeProps('STY_ZERO', 'STY', Addressing::ZeroPage, $this->cycle[0x84]),
      '8C' => new OpCodeProps('STY_ABS', 'STY', Addressing::Absolute, $this->cycle[0x8C]),
      '94' => new OpCodeProps('STY_ZEROX', 'STY', Addressing::ZeroPageX, $this->cycle[0x94]),
      '8A' => new OpCodeProps('TXA', 'TXA', Addressing::Implied, $this->cycle[0x8A]),
      '98' => new OpCodeProps('TYA', 'TYA', Addressing::Implied, $this->cycle[0x98]),
      '9A' => new OpCodeProps('TXS', 'TXS', Addressing::Implied, $this->cycle[0x9A]),
      'A8' => new OpCodeProps('TAY', 'TAY', Addressing::Implied, $this->cycle[0xA8]),
      'AA' => new OpCodeProps('TAX', 'TAX', Addressing::Implied, $this->cycle[0xAA]),
      'BA' => new OpCodeProps('TSX', 'TSX', Addressing::Implied, $this->cycle[0xBA]),
      '8' => new OpCodeProps('PHP', 'PHP', Addressing::Implied, $this->cycle[0x08]),
      '28' => new OpCodeProps('PLP', 'PLP', Addressing::Implied, $this->cycle[0x28]),
      '48' => new OpCodeProps('PHA', 'PHA', Addressing::Implied, $this->cycle[0x48]),
      '68' => new OpCodeProps('PLA', 'PLA', Addressing::Implied, $this->cycle[0x68]),
      '69' => new OpCodeProps('ADC_IMM', 'ADC', Addressing::Immediate, $this->cycle[0x69]),
      '65' => new OpCodeProps('ADC_ZERO', 'ADC', Addressing::ZeroPage, $this->cycle[0x65]),
      '6D' => new OpCodeProps('ADC_ABS', 'ADC', Addressing::Absolute, $this->cycle[0x6D]),
      '75' => new OpCodeProps('ADC_ZEROX', 'ADC', Addressing::ZeroPageX, $this->cycle[0x75]),
      '7D' => new OpCodeProps('ADC_ABSX', 'ADC', Addressing::AbsoluteX, $this->cycle[0x7D]),
      '79' => new OpCodeProps('ADC_ABSY', 'ADC', Addressing::AbsoluteY, $this->cycle[0x79]),
      '61' => new OpCodeProps('ADC_INDX', 'ADC', Addressing::PreIndexedIndirect, $this->cycle[0x61]),
      '71' => new OpCodeProps('ADC_INDY', 'ADC', Addressing::PostIndexedIndirect, $this->cycle[0x71]),
      'E9' => new OpCodeProps('SBC_IMM', 'SBC', Addressing::Immediate, $this->cycle[0xE9]),
      'E5' => new OpCodeProps('SBC_ZERO', 'SBC', Addressing::ZeroPage, $this->cycle[0xE5]),
      'ED' => new OpCodeProps('SBC_ABS', 'SBC', Addressing::Absolute, $this->cycle[0xED]),
      'F5' => new OpCodeProps('SBC_ZEROX', 'SBC', Addressing::ZeroPageX, $this->cycle[0xF5]),
      'FD' => new OpCodeProps('SBC_ABSX', 'SBC', Addressing::AbsoluteX, $this->cycle[0xFD]),
      'F9' => new OpCodeProps('SBC_ABSY', 'SBC', Addressing::AbsoluteY, $this->cycle[0xF9]),
      'E1' => new OpCodeProps('SBC_INDX', 'SBC', Addressing::PreIndexedIndirect, $this->cycle[0xE1]),
      'F1' => new OpCodeProps('SBC_INDY', 'SBC', Addressing::PostIndexedIndirect, $this->cycle[0xF1]),

      'E0' => new OpCodeProps('CPX_IMM', 'CPX', Addressing::Immediate, $this->cycle[0xE0]),
      'E4' => new OpCodeProps('CPX_ZERO', 'CPX', Addressing::ZeroPage, $this->cycle[0xE4]),
      'EC' => new OpCodeProps('CPX_ABS', 'CPX', Addressing::Absolute, $this->cycle[0xEC]),
      'C0' => new OpCodeProps('CPY_IMM', 'CPY', Addressing::Immediate, $this->cycle[0xC0]),
      'C4' => new OpCodeProps('CPY_ZERO', 'CPY', Addressing::ZeroPage, $this->cycle[0xC4]),
      'CC' => new OpCodeProps('CPY_ABS', 'CPY', Addressing::Absolute, $this->cycle[0xCC]),
      'C9' => new OpCodeProps('CMP_IMM', 'CMP', Addressing::Immediate, $this->cycle[0xC9]),
      'C5' => new OpCodeProps('CMP_ZERO', 'CMP', Addressing::ZeroPage, $this->cycle[0xC5]),
      'CD' => new OpCodeProps('CMP_ABS', 'CMP', Addressing::Absolute, $this->cycle[0xCD]),
      'D5' => new OpCodeProps('CMP_ZEROX', 'CMP', Addressing::ZeroPageX, $this->cycle[0xD5]),
      'DD' => new OpCodeProps('CMP_ABSX', 'CMP', Addressing::AbsoluteX, $this->cycle[0xDD]),
      'D9' => new OpCodeProps('CMP_ABSY', 'CMP', Addressing::AbsoluteY, $this->cycle[0xD9]),
      'C1' => new OpCodeProps('CMP_INDX', 'CMP', Addressing::PreIndexedIndirect, $this->cycle[0xC1]),
      'D1' => new OpCodeProps('CMP_INDY', 'CMP', Addressing::PostIndexedIndirect, $this->cycle[0xD1]),
      '29' => new OpCodeProps('AND_IMM', 'AND', Addressing::Immediate, $this->cycle[0x29]),
      '25' => new OpCodeProps('AND_ZERO', 'AND', Addressing::ZeroPage, $this->cycle[0x25]),
      '2D' => new OpCodeProps('AND_ABS', 'AND', Addressing::Absolute, $this->cycle[0x2D]),
      '35' => new OpCodeProps('AND_ZEROX', 'AND', Addressing::ZeroPageX, $this->cycle[0x35]),
      '3D' => new OpCodeProps('AND_ABSX', 'AND', Addressing::AbsoluteX, $this->cycle[0x3D]),
      '39' => new OpCodeProps('AND_ABSY', 'AND', Addressing::AbsoluteY, $this->cycle[0x39]),
      '21' => new OpCodeProps('AND_INDX', 'AND', Addressing::PreIndexedIndirect, $this->cycle[0x21]),
      '31' => new OpCodeProps('AND_INDY', 'AND', Addressing::PostIndexedIndirect, $this->cycle[0x31]),
      '49' => new OpCodeProps('EOR_IMM', 'EOR', Addressing::Immediate, $this->cycle[0x49]),
      '45' => new OpCodeProps('EOR_ZERO', 'EOR', Addressing::ZeroPage, $this->cycle[0x45]),
      '4D' => new OpCodeProps('EOR_ABS', 'EOR', Addressing::Absolute, $this->cycle[0x4D]),
      '55' => new OpCodeProps('EOR_ZEROX', 'EOR', Addressing::ZeroPageX, $this->cycle[0x55]),
      '5D' => new OpCodeProps('EOR_ABSX', 'EOR', Addressing::AbsoluteX, $this->cycle[0x5D]),
      '59' => new OpCodeProps('EOR_ABSY', 'EOR', Addressing::AbsoluteY, $this->cycle[0x59]),
      '41' => new OpCodeProps('EOR_INDX', 'EOR', Addressing::PreIndexedIndirect, $this->cycle[0x41]),
      '51' => new OpCodeProps('EOR_INDY', 'EOR', Addressing::PostIndexedIndirect, $this->cycle[0x51]),
      '9' => new OpCodeProps('ORA_IMM', 'ORA', Addressing::Immediate, $this->cycle[0x09]),
      '5' => new OpCodeProps('ORA_ZERO', 'ORA', Addressing::ZeroPage, $this->cycle[0x05]),
      'D' => new OpCodeProps('ORA_ABS', 'ORA', Addressing::Absolute, $this->cycle[0x0D]),
      '15' => new OpCodeProps('ORA_ZEROX', 'ORA', Addressing::ZeroPageX, $this->cycle[0x15]),
      '1D' => new OpCodeProps('ORA_ABSX', 'ORA', Addressing::AbsoluteX, $this->cycle[0x1D]),
      '19' => new OpCodeProps('ORA_ABSY', 'ORA', Addressing::AbsoluteY, $this->cycle[0x19]),
      '1' => new OpCodeProps('ORA_INDX', 'ORA', Addressing::PreIndexedIndirect, $this->cycle[0x01]),
      '11' => new OpCodeProps('ORA_INDY', 'ORA', Addressing::PostIndexedIndirect, $this->cycle[0x11]),
      '24' => new OpCodeProps('BIT_ZERO', 'BIT', Addressing::ZeroPage, $this->cycle[0x24]),
      '2C' => new OpCodeProps('BIT_ABS', 'BIT', Addressing::Absolute, $this->cycle[0x2C]),
      'A' => new OpCodeProps('ASL', 'ASL', Addressing::Accumulator, $this->cycle[0x0A]),
      '6' => new OpCodeProps('ASL_ZERO', 'ASL', Addressing::ZeroPage, $this->cycle[0x06]),
      'E' => new OpCodeProps('ASL_ABS', 'ASL', Addressing::Absolute, $this->cycle[0x0E]),
      '16' => new OpCodeProps('ASL_ZEROX', 'ASL', Addressing::ZeroPageX, $this->cycle[0x16]),
      '1E' => new OpCodeProps('ASL_ABSX', 'ASL', Addressing::AbsoluteX, $this->cycle[0x1E]),
      '4A' => new OpCodeProps('LSR', 'LSR', Addressing::Accumulator, $this->cycle[0x4A]),
      '46' => new OpCodeProps('LSR_ZERO', 'LSR', Addressing::ZeroPage, $this->cycle[0x46]),
      '4E' => new OpCodeProps('LSR_ABS', 'LSR', Addressing::Absolute, $this->cycle[0x4E]),
      '56' => new OpCodeProps('LSR_ZEROX', 'LSR', Addressing::ZeroPageX, $this->cycle[0x56]),
      '5E' => new OpCodeProps('LSR_ABSX', 'LSR', Addressing::AbsoluteX, $this->cycle[0x5E]),
      '2A' => new OpCodeProps('ROL', 'ROL', Addressing::Accumulator, $this->cycle[0x2A]),
      '26' => new OpCodeProps('ROL_ZERO', 'ROL', Addressing::ZeroPage, $this->cycle[0x26]),
      '2E' => new OpCodeProps('ROL_ABS', 'ROL', Addressing::Absolute, $this->cycle[0x2E]),
      '36' => new OpCodeProps('ROL_ZEROX', 'ROL', Addressing::ZeroPageX, $this->cycle[0x36]),
      '3E' => new OpCodeProps('ROL_ABSX', 'ROL', Addressing::AbsoluteX, $this->cycle[0x3E]),
      '6A' => new OpCodeProps('ROR', 'ROR', Addressing::Accumulator, $this->cycle[0x6A]),
      '66' => new OpCodeProps('ROR_ZERO', 'ROR', Addressing::ZeroPage, $this->cycle[0x66]),
      '6E' => new OpCodeProps('ROR_ABS', 'ROR', Addressing::Absolute, $this->cycle[0x6E]),
      '76' => new OpCodeProps('ROR_ZEROX', 'ROR', Addressing::ZeroPageX, $this->cycle[0x76]),
      '7E' => new OpCodeProps('ROR_ABSX', 'ROR', Addressing::AbsoluteX, $this->cycle[0x7E]),
      'E8' => new OpCodeProps('INX', 'INX', Addressing::Implied, $this->cycle[0xE8]),
      'C8' => new OpCodeProps('INY', 'INY', Addressing::Implied, $this->cycle[0xC8]),
      'E6' => new OpCodeProps('INC_ZERO', 'INC', Addressing::ZeroPage, $this->cycle[0xE6]),
      'EE' => new OpCodeProps('INC_ABS', 'INC', Addressing::Absolute, $this->cycle[0xEE]),
      'F6' => new OpCodeProps('INC_ZEROX', 'INC', Addressing::ZeroPageX, $this->cycle[0xF6]),
      'FE' => new OpCodeProps('INC_ABSX', 'INC', Addressing::AbsoluteX, $this->cycle[0xFE]),
      'CA' => new OpCodeProps('DEX', 'DEX', Addressing::Implied, $this->cycle[0xCA]),
      '88' => new OpCodeProps('DEY', 'DEY', Addressing::Implied, $this->cycle[0x88]),
      'C6' => new OpCodeProps('DEC_ZERO', 'DEC', Addressing::ZeroPage, $this->cycle[0xC6]),
      'CE' => new OpCodeProps('DEC_ABS', 'DEC', Addressing::Absolute, $this->cycle[0xCE]),
      'D6' => new OpCodeProps('DEC_ZEROX', 'DEC', Addressing::ZeroPageX, $this->cycle[0xD6]),
      'DE' => new OpCodeProps('DEC_ABSX', 'DEC', Addressing::AbsoluteX, $this->cycle[0xDE]),
      '18' => new OpCodeProps('CLC', 'CLC', Addressing::Implied, $this->cycle[0x18]),
      '58' => new OpCodeProps('CLI', 'CLI', Addressing::Implied, $this->cycle[0x58]),
      'B8' => new OpCodeProps('CLV', 'CLV', Addressing::Implied, $this->cycle[0xB8]),
      '38' => new OpCodeProps('SEC', 'SEC', Addressing::Implied, $this->cycle[0x38]),
      '78' => new OpCodeProps('SEI', 'SEI', Addressing::Implied, $this->cycle[0x78]),
      'EA' => new OpCodeProps('NOP', 'NOP', Addressing::Implied, $this->cycle[0xEA]),
      '0' => new OpCodeProps('BRK', 'BRK', Addressing::Implied, $this->cycle[0x00]),
      '20' => new OpCodeProps('JSR_ABS', 'JSR', Addressing::Absolute, $this->cycle[0x20]),
      '4C' => new OpCodeProps('JMP_ABS', 'JMP', Addressing::Absolute, $this->cycle[0x4C]),
      '6C' => new OpCodeProps('JMP_INDABS', 'JMP', Addressing::IndirectAbsolute, $this->cycle[0x6C]),
      '40' => new OpCodeProps('RTI', 'RTI', Addressing::Implied, $this->cycle[0x40]),
      '60' => new OpCodeProps('RTS', 'RTS', Addressing::Implied, $this->cycle[0x60]),
      '10' => new OpCodeProps('BPL', 'BPL', Addressing::Relative, $this->cycle[0x10]),
      '30' => new OpCodeProps('BMI', 'BMI', Addressing::Relative, $this->cycle[0x30]),
      '50' => new OpCodeProps('BVC', 'BVC', Addressing::Relative, $this->cycle[0x50]),
      '70' => new OpCodeProps('BVS', 'BVS', Addressing::Relative, $this->cycle[0x70]),
      '90' => new OpCodeProps('BCC', 'BCC', Addressing::Relative, $this->cycle[0x90]),
      'B0' => new OpCodeProps('BCS', 'BCS', Addressing::Relative, $this->cycle[0xB0]),
      'D0' => new OpCodeProps('BNE', 'BNE', Addressing::Relative, $this->cycle[0xD0]),
      'F0' => new OpCodeProps('BEQ', 'BEQ', Addressing::Relative, $this->cycle[0xF0]),
      'F8' => new OpCodeProps('SED', 'SED', Addressing::Implied, $this->cycle[0xF8]),
      'D8' => new OpCodeProps('CLD', 'CLD', Addressing::Implied, $this->cycle[0xD8]),
      // unofficial opecode
      // Also see https://wiki.nesdev.com/w/index.php/CPU_unofficial_opcodes
      '1A' => new OpCodeProps('NOP', 'NOP', Addressing::Implied, $this->cycle[0x1A]),
      '3A' => new OpCodeProps('NOP', 'NOP', Addressing::Implied, $this->cycle[0x3A]),
      '5A' => new OpCodeProps('NOP', 'NOP', Addressing::Implied, $this->cycle[0x5A]),
      '7A' => new OpCodeProps('NOP', 'NOP', Addressing::Implied, $this->cycle[0x7A]),
      'DA' => new OpCodeProps('NOP', 'NOP', Addressing::Implied, $this->cycle[0xDA]),
      'FA' => new OpCodeProps('NOP', 'NOP', Addressing::Implied, $this->cycle[0xFA]),
      '02' => new OpCodeProps('NOP', 'NOP', Addressing::Implied, $this->cycle[0x02]),
      '12' => new OpCodeProps('NOP', 'NOP', Addressing::Implied, $this->cycle[0x12]),
      '22' => new OpCodeProps('NOP', 'NOP', Addressing::Implied, $this->cycle[0x22]),
      '32' => new OpCodeProps('NOP', 'NOP', Addressing::Implied, $this->cycle[0x32]),
      '42' => new OpCodeProps('NOP', 'NOP', Addressing::Implied, $this->cycle[0x42]),
      '52' => new OpCodeProps('NOP', 'NOP', Addressing::Implied, $this->cycle[0x52]),
      '62' => new OpCodeProps('NOP', 'NOP', Addressing::Implied, $this->cycle[0x62]),
      '72' => new OpCodeProps('NOP', 'NOP', Addressing::Implied, $this->cycle[0x72]),
      '92' => new OpCodeProps('NOP', 'NOP', Addressing::Implied, $this->cycle[0x92]),
      'B2' => new OpCodeProps('NOP', 'NOP', Addressing::Implied, $this->cycle[0xB2]),
      'D2' => new OpCodeProps('NOP', 'NOP', Addressing::Implied, $this->cycle[0xD2]),
      'F2' => new OpCodeProps('NOP', 'NOP', Addressing::Implied, $this->cycle[0xF2]),
      '80' => new OpCodeProps('NOPD', 'NOPD', Addressing::Implied, $this->cycle[0x80]),
      '82' => new OpCodeProps('NOPD', 'NOPD', Addressing::Implied, $this->cycle[0x82]),
      '89' => new OpCodeProps('NOPD', 'NOPD', Addressing::Implied, $this->cycle[0x89]),
      'C2' => new OpCodeProps('NOPD', 'NOPD', Addressing::Implied, $this->cycle[0xC2]),
      'E2' => new OpCodeProps('NOPD', 'NOPD', Addressing::Implied, $this->cycle[0xE2]),
      '04' => new OpCodeProps('NOPD', 'NOPD', Addressing::Implied, $this->cycle[0x04]),
      '44' => new OpCodeProps('NOPD', 'NOPD', Addressing::Implied, $this->cycle[0x44]),
      '64' => new OpCodeProps('NOPD', 'NOPD', Addressing::Implied, $this->cycle[0x64]),
      '14' => new OpCodeProps('NOPD', 'NOPD', Addressing::Implied, $this->cycle[0x14]),
      '34' => new OpCodeProps('NOPD', 'NOPD', Addressing::Implied, $this->cycle[0x34]),
      '54' => new OpCodeProps('NOPD', 'NOPD', Addressing::Implied, $this->cycle[0x54]),
      '74' => new OpCodeProps('NOPD', 'NOPD', Addressing::Implied, $this->cycle[0x74]),
      'D4' => new OpCodeProps('NOPD', 'NOPD', Addressing::Implied, $this->cycle[0xD4]),
      'F4' => new OpCodeProps('NOPD', 'NOPD', Addressing::Implied, $this->cycle[0xF4]),
      '0C' => new OpCodeProps('NOPI', 'NOPI', Addressing::Implied, $this->cycle[0x0C]),
      '1C' => new OpCodeProps('NOPI', 'NOPI', Addressing::Implied, $this->cycle[0x1C]),
      '3C' => new OpCodeProps('NOPI', 'NOPI', Addressing::Implied, $this->cycle[0x3C]),
      '5C' => new OpCodeProps('NOPI', 'NOPI', Addressing::Implied, $this->cycle[0x5C]),
      '7C' => new OpCodeProps('NOPI', 'NOPI', Addressing::Implied, $this->cycle[0x7C]),
      'DC' => new OpCodeProps('NOPI', 'NOPI', Addressing::Implied, $this->cycle[0xDC]),
      'FC' => new OpCodeProps('NOPI', 'NOPI', Addressing::Implied, $this->cycle[0xFC]),
      // LAX
      'A7' => new OpCodeProps('LAX_ZERO', 'LAX', Addressing::ZeroPage, $this->cycle[0xA7]),
      'B7' => new OpCodeProps('LAX_ZEROY', 'LAX', Addressing::ZeroPageY, $this->cycle[0xB7]),
      'AF' => new OpCodeProps('LAX_ABS', 'LAX', Addressing::Absolute, $this->cycle[0xAF]),
      'BF' => new OpCodeProps('LAX_ABSY', 'LAX', Addressing::AbsoluteY, $this->cycle[0xBF]),
      'A3' => new OpCodeProps('LAX_INDX', 'LAX', Addressing::PreIndexedIndirect, $this->cycle[0xA3]),
      'B3' => new OpCodeProps('LAX_INDY', 'LAX', Addressing::PostIndexedIndirect, $this->cycle[0xB3]),
      // SAX
      '87' => new OpCodeProps('SAX_ZERO', 'SAX', Addressing::ZeroPage, $this->cycle[0x87]),
      '97' => new OpCodeProps('SAX_ZEROY', 'SAX', Addressing::ZeroPageY, $this->cycle[0x97]),
      '8F' => new OpCodeProps('SAX_ABS', 'SAX', Addressing::Absolute, $this->cycle[0x8F]),
      '83' => new OpCodeProps('SAX_INDX', 'SAX', Addressing::PreIndexedIndirect, $this->cycle[0x83]),
      // SBC
      'EB' => new OpCodeProps('SBC_IMM', 'SBC', Addressing::Immediate, $this->cycle[0xEB]),
      // DCP
      'C7' => new OpCodeProps('DCP_ZERO', 'DCP', Addressing::ZeroPage, $this->cycle[0xC7]),
      'D7' => new OpCodeProps('DCP_ZEROX', 'DCP', Addressing::ZeroPageX, $this->cycle[0xD7]),
      'CF' => new OpCodeProps('DCP_ABS', 'DCP', Addressing::Absolute, $this->cycle[0xCF]),
      'DF' => new OpCodeProps('DCP_ABSX', 'DCP', Addressing::AbsoluteX, $this->cycle[0xDF]),
      'DB' => new OpCodeProps('DCP_ABSY', 'DCP', Addressing::AbsoluteY, $this->cycle[0xD8]),
      'C3' => new OpCodeProps('DCP_INDX', 'DCP', Addressing::PreIndexedIndirect, $this->cycle[0xC3]),
      'D3' => new OpCodeProps('DCP_INDY', 'DCP', Addressing::PostIndexedIndirect, $this->cycle[0xD3]),
      // ISB
      'E7' => new OpCodeProps('ISB_ZERO', 'ISB', Addressing::ZeroPage, $this->cycle[0xE7]),
      'F7' => new OpCodeProps('ISB_ZEROX', 'ISB', Addressing::ZeroPageX, $this->cycle[0xF7]),
      'EF' => new OpCodeProps('ISB_ABS', 'ISB', Addressing::Absolute, $this->cycle[0xEF]),
      'FF' => new OpCodeProps('ISB_ABSX', 'ISB', Addressing::AbsoluteX, $this->cycle[0xFF]),
      'FB' => new OpCodeProps('ISB_ABSY', 'ISB', Addressing::AbsoluteY, $this->cycle[0xF8]),
      'E3' => new OpCodeProps('ISB_INDX', 'ISB', Addressing::PreIndexedIndirect, $this->cycle[0xE3]),
      'F3' => new OpCodeProps('ISB_INDY', 'ISB', Addressing::PostIndexedIndirect, $this->cycle[0xF3]),
      // SLO
      '07' => new OpCodeProps('SLO_ZERO', 'SLO', Addressing::ZeroPage, $this->cycle[0x07]),
      '17' => new OpCodeProps('SLO_ZEROX', 'SLO', Addressing::ZeroPageX, $this->cycle[0x17]),
      '0F' => new OpCodeProps('SLO_ABS', 'SLO', Addressing::Absolute, $this->cycle[0x0F]),
      '1F' => new OpCodeProps('SLO_ABSX', 'SLO', Addressing::AbsoluteX, $this->cycle[0x1F]),
      '1B' => new OpCodeProps('SLO_ABSY', 'SLO', Addressing::AbsoluteY, $this->cycle[0x1B]),
      '03' => new OpCodeProps('SLO_INDX', 'SLO', Addressing::PreIndexedIndirect, $this->cycle[0x03]),
      '13' => new OpCodeProps('SLO_INDY', 'SLO', Addressing::PostIndexedIndirect, $this->cycle[0x13]),
      // RLA
      '27' => new OpCodeProps('RLA_ZERO', 'RLA', Addressing::ZeroPage, $this->cycle[0x27]),
      '37' => new OpCodeProps('RLA_ZEROX', 'RLA', Addressing::ZeroPageX, $this->cycle[0x37]),
      '2F' => new OpCodeProps('RLA_ABS', 'RLA', Addressing::Absolute, $this->cycle[0x2F]),
      '3F' => new OpCodeProps('RLA_ABSX', 'RLA', Addressing::AbsoluteX, $this->cycle[0x3F]),
      '3B' => new OpCodeProps('RLA_ABSY', 'RLA', Addressing::AbsoluteY, $this->cycle[0x3B]),
      '23' => new OpCodeProps('RLA_INDX', 'RLA', Addressing::PreIndexedIndirect, $this->cycle[0x23]),
      '33' => new OpCodeProps('RLA_INDY', 'RLA', Addressing::PostIndexedIndirect, $this->cycle[0x33]),
      // SRE
      '47' => new OpCodeProps('SRE_ZERO', 'SRE', Addressing::ZeroPage, $this->cycle[0x47]),
      '57' => new OpCodeProps('SRE_ZEROX', 'SRE', Addressing::ZeroPageX, $this->cycle[0x57]),
      '4F' => new OpCodeProps('SRE_ABS', 'SRE', Addressing::Absolute, $this->cycle[0x4F]),
      '5F' => new OpCodeProps('SRE_ABSX', 'SRE', Addressing::AbsoluteX, $this->cycle[0x5F]),
      '5B' => new OpCodeProps('SRE_ABSY', 'SRE', Addressing::AbsoluteY, $this->cycle[0x5B]),
      '43' => new OpCodeProps('SRE_INDX', 'SRE', Addressing::PreIndexedIndirect, $this->cycle[0x43]),
      '53' => new OpCodeProps('SRE_INDY', 'SRE', Addressing::PostIndexedIndirect, $this->cycle[0x53]),
      // RRA
      '67' => new OpCodeProps('RRA_ZERO', 'RRA', Addressing::ZeroPage, $this->cycle[0x67]),
      '77' => new OpCodeProps('RRA_ZEROX', 'RRA', Addressing::ZeroPageX, $this->cycle[0x77]),
      '6F' => new OpCodeProps('RRA_ABS', 'RRA', Addressing::Absolute, $this->cycle[0x6F]),
      '7F' => new OpCodeProps('RRA_ABSX', 'RRA', Addressing::AbsoluteX, $this->cycle[0x7F]),
      '7B' => new OpCodeProps('RRA_ABSY', 'RRA', Addressing::AbsoluteY, $this->cycle[0x7B]),
      '63' => new OpCodeProps('RRA_INDX', 'RRA', Addressing::PreIndexedIndirect, $this->cycle[0x63]),
      '73' => new OpCodeProps('RRA_INDY', 'RRA', Addressing::PostIndexedIndirect, $this->cycle[0x73]),
    };
  }
}

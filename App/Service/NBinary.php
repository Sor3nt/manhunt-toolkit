<?php
namespace App\Service;

use App\Service\Archive\ZLib;

class NBinary{
    const INT_8 = 'INT_8';
    const INT_16 = 'INT_16';
    const U_INT_8 = 'U_INT_8';
    const LITTLE_U_INT_16 = 'LITTLE_U_INT_16';
    const LITTLE_U_INT_32 = 'LITTLE_U_INT_32';
    const BIG_U_INT_32 = 'BIG_U_INT_32';
    const BIG_U_INT_16 = 'BIG_U_INT_16';
    const BIG_U_INT_8 = 'BIG_U_INT_8';
    const INT_32 = 'INT_32';
    const FLOAT_32 = 'FLOAT_32';
    const BIG_FLOAT_32 = 'BIG_FLOAT_32';
    const STRING = 'STRING';
    const HEX = 'RAW';
    const BINARY = 'BINARY';

    public $numericBigEndian = false;

    public $binary = "";
    public $hex = "";

    public $current = 0;

    public function __construct( $binary = null ){
        if (is_null($binary)) return;

        $fourCC = \mb_substr($binary, 0, 4, '8bit');

        if ($fourCC === "\x5a\x32\x48\x4d" || $fourCC === "\x4d\x48\x32\x5a"){
            $binary = ZLib::uncompress( $binary );
        }

        $this->binary = $binary;
        $this->hex = bin2hex($binary);
    }

    public function length(){
        return \mb_strlen($this->binary, '8bit');
    }

    public function remain(){
        return $this->length() - $this->current;
    }

    public function getAsArray(){
        return str_split(bin2hex($this->binary), 2);
    }

    public function jumpTo( $offset, $absolutePosition = true ){
        if ($absolutePosition == false){
            if ($offset > $this->length()) throw new \Exception("jump to not possible, out of range => " . $offset . " max: " . $this->length());
            $this->current += $offset;
        }else{
            if ($offset > $this->length()) throw new \Exception("jump to not possible, out of range => " . $offset . " max: " . $this->length());
            $this->current = $offset;
        }
    }

    public function readSwitchedInt16(){
        $this->numericBigEndian = !$this->numericBigEndian;
        $hex = $this->consume(2, NBinary::HEX);
        $hex = implode('',array_reverse(str_split($hex, 2)));
        $result = $this->unpack(hex2bin($hex), NBinary::INT_16);
        $this->numericBigEndian = !$this->numericBigEndian;
        return $result;
    }

    public function range( $fromOffset, $toOffset, $absolutePosition = false ){

        if ($absolutePosition == false){
            return hex2bin(substr($this->hex, ($this->current + $fromOffset) * 2, ($toOffset - $fromOffset) * 2));
//            return mb_substr($this->binary, $this->current + $fromOffset, ($toOffset - $fromOffset), '8bit');

        }else{
            return hex2bin(substr($this->hex, $fromOffset * 2, ($toOffset - $fromOffset) * 2));
//            return mb_substr($this->binary, $fromOffset, ($toOffset - $fromOffset), '8bit');

        }

    }


    public function overwriteBatch($positions, $type){

        foreach ($positions as $offset => $bytes) {
            $this->current = $offset;

            $add = $this->pack($bytes, $type);

//            $add = bin2hex($add);
            $this->binary = substr_replace($this->binary, $add, $this->current, mb_strlen($add, '8bit'));
//
//
//            $neededLength = mb_strlen($add, '8bit');
//
//            $before = substr($this->hex, 0, $this->current * 2);
//            $after = substr($this->hex, ($this->current + $neededLength) * 2);
//
//            $this->hex = $before . bin2hex($add) . $after;
//echo ".";
        }
        $this->hex = bin2hex($this->binary);
    }
    public function overwrite($bytes, $type){
        $add = $this->pack($bytes, $type);

        $neededLength = mb_strlen($add, '8bit');

        $before = substr($this->hex, 0, $this->current * 2);
        $after = substr($this->hex, ($this->current + $neededLength) * 2);

        $this->hex = $before . bin2hex($add) . $after;
        $this->binary = hex2bin($this->hex);
    }

    public function write($bytes, $type){
        $add = $this->pack($bytes, $type);
        $this->binary .= $add;
        $this->hex .= bin2hex($add);
        $this->current = strlen($this->hex) / 2;
    }

    public function unpack($data, $type){
        return $this->unpackPack($data, $type, false);
    }

    public function pack($data, $type){
        return $this->unpackPack($data, $type, true);
    }

    private function unpackPack($data, $type, $doPack = false){

        if ($this->numericBigEndian){
//            if ($type == self::INT_8) die("big int_8 ?");
            if ($type == self::INT_16) $type = self::BIG_U_INT_16;
            if ($type == self::INT_32) $type = self::BIG_U_INT_32;
            if ($type == self::FLOAT_32) $type = self::BIG_FLOAT_32;
        }


        switch ($type){
            case self::INT_8:
                return $doPack ? pack('c', $data) : current(unpack("c", ($data)));
                break;
            case self::INT_16:
                return $doPack ? pack('s', $data) : current(unpack("s", ($data)));
                break;
            case self::U_INT_8:
                return $doPack ? pack('C', $data) : current(unpack("C", ($data)));
                break;
            case self::LITTLE_U_INT_16:
                return $doPack ? pack('v', $data) : current(unpack("v", ($data)));
                break;
            case self::LITTLE_U_INT_32:
                return $doPack ? pack('V', $data) : current(unpack("V", ($data)));
                break;
            case self::INT_32:
                return $doPack ? pack('L', $data) : (int) current(unpack("L", ($data)));
                break;
            case self::FLOAT_32:
                return $doPack ? pack('f', $data) : (float) current(unpack("f", ($data)));
                break;
            case self::BIG_FLOAT_32:
                return $doPack ? pack('G', $data) : (float) current(unpack("G", ($data)));
                break;
            case self::BIG_U_INT_32:
                return $doPack ? pack('N', $data) : current(unpack("N", ($data)));
                break;
            case self::BIG_U_INT_16:
                return $doPack ? pack('n', $data) : current(unpack("n", ($data)));
                break;
            case self::BIG_U_INT_8:
                return $doPack ? pack('t', $data) : current(unpack("t", ($data)));
                break;
            case self::STRING:

                if ($doPack) return $data;

                if (mb_strpos($data, "\x00") !== false){
                    return mb_substr($data, 0, mb_strpos($data, "\x00"));
                }else{
                    return trim($data);
                }

                break;
            case self::BINARY:
                return $data;
                break;
            case self::HEX:
                if ($doPack) return hex2bin($data);
                return bin2hex($data);
                break;
        }

        return $data;

    }

    public function get( $bytes, $startAt = 0, $asHex = false){
        $result = substr($this->hex, ($this->current + $startAt) * 2, $bytes * 2);

        if ($asHex){
            return $result;
        }

        return hex2bin($result);
    }

    public function getString( $delimiter = "\x00", $doPadding = true ){

        $partOnly = mb_substr($this->binary, $this->current, null, '8bit');

        $delimiterPos = mb_strpos($partOnly, $delimiter, null, '8bit');
        if ($delimiterPos === -1) return '';
        $result = mb_substr($partOnly, 0, $delimiterPos, '8bit');


        $padding = 0;
        if ($doPadding){
            $padding  = 4 - (( mb_strlen($result, '8bit') ) % 4);
        }

        $this->current += $delimiterPos + $padding;

        return $result;
    }

    public function getPadding($paddingChar = "\x00", $to = 4, $text = null ){

        if ($text == null) $text = strlen($this->hex) / 2;
        else $text = strlen($text);

        $padding = $to - (( $text ) % $to);

        if ($padding == $to) return "";
        return str_repeat($paddingChar, $padding);

    }

    public function readXYZ( $len = 4, $type = NBinary::FLOAT_32){

        $x = $this->consume($len, NBinary::BINARY);
        $y = $this->consume($len, NBinary::BINARY);
        $z = $this->consume($len, NBinary::BINARY);

        if ($x === "\x00\x00\x00\x80"){
            $x = "-0";
        }else{
            $x = $this->unpack($x, $type);
        }

        if ($y === "\x00\x00\x00\x80"){
            $y = "-0";
        }else{
            $y = $this->unpack($y, $type);
        }


        if ($z === "\x00\x00\x00\x80"){
            $z = "-0";
        }else{
            $z = $this->unpack($z, $type);
        }

        return [$x, $y, $z];
    }

    public function readXYZW( $len = 4, $type = NBinary::FLOAT_32){

        $x = $this->consume($len, NBinary::BINARY);
        $y = $this->consume($len, NBinary::BINARY);
        $z = $this->consume($len, NBinary::BINARY);
        $w = $this->consume($len, NBinary::BINARY);

        if ($x === "\x00\x00\x00\x80"){
            $x = "-0";
        }else{
            $x = $this->unpack($x, $type);
        }

        if ($y === "\x00\x00\x00\x80"){
            $y = "-0";
        }else{
            $y = $this->unpack($y, $type);
        }


        if ($z === "\x00\x00\x00\x80"){
            $z = "-0";
        }else{
            $z = $this->unpack($z, $type);
        }

        if ($w === "\x00\x00\x00\x80"){
            $w = "-0";
        }else{
            $w = $this->unpack($w, $type);
        }

        return [$x, $y, $z, $w];
    }

    public function writeXYZ($xyz, $type = NBinary::FLOAT_32){

        if ($xyz[0] === "-0") $this->write("\x00\x00\x00\x80", NBinary::BINARY);
        else $this->write($xyz[0], $type);

        if ($xyz[1] === "-0") $this->write("\x00\x00\x00\x80", NBinary::BINARY);
        else $this->write($xyz[1], $type);

        if ($xyz[2] === "-0") $this->write("\x00\x00\x00\x80", NBinary::BINARY);
        else $this->write($xyz[2], $type);
    }

    public function consume( $bytes, $type, $skip = 0){

        $this->current += $skip;

        $result = hex2bin(substr($this->hex, $this->current * 2, $bytes * 2));
//        $result = mb_substr($this->binary, $this->current, $bytes, '8bit');

        $this->current += $bytes ;

        return $this->unpack($result, $type);
    }


    public function concat( NBinary $binary){
        $this->binary .= $binary->binary;
        $this->hex .= bin2hex($binary->binary);
        $this->current = strlen($this->hex) / 2;

    }

    public function getFromPos($offset, $size, $type){
        $this->jumpTo($offset);
        return $this->consume($size, $type);

    }

}
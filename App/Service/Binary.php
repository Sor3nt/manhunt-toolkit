<?php
namespace App\Service;

use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\Finder\Finder;

class Binary {

    private $buffer;

    public function __construct( $binary = null, $hex = false )
    {
        if (!is_null($binary)){
            $this->buffer = $hex ? hex2bin($binary) : $binary;
        }
    }

    public function debug(){

        echo "Hex: " . $this->toHex() . "\n";
        echo "Binary Length: " . $this->length() . "\n";
        echo "Hex Length: " . $this->length(true) . "\n";

        foreach ([
                     'a','A','h','H','c','C','s','S','n','v','i','I','l','L','N','V','f','q', 'Q', 'J', 'P', 'f', 'g', 'G', 'd', 'e', 'E'
                 ] as $type) {

            try{
                echo sprintf(
                    "Unpack %s:",
                    $type
                );

                var_dump(unpack($type, $this->buffer));
                echo "\n";
            }catch(\Exception $e){}

            try{
                echo sprintf(
                    "Unpack %s*:",
                    $type
                );

                var_dump(unpack($type . '*', $this->buffer));
                echo "\n";
            }catch(\Exception $e){}
        }

        return $this->toHex();
    }

    public function __toString()
    {
        return $this->toHex();
    }

    public function addBinary( $add ){
        $this->buffer .= $add;
    }

    public function addHex( $add ){
        $this->buffer .= hex2bin($add);
    }


    public function toHex( $toBigEndian = false){
        return $toBigEndian ? Helper::toBigEndian(bin2hex($this->buffer)) : bin2hex($this->buffer);
    }

    public function toBinary(){
        return $this->buffer;
    }

    public function toString(){
        $string = str_replace('00', '', $this->toHex());
        return (string) hex2bin($string);
    }


    public function toInt($toBigEndian = false){
        if ($this->length() == 0) return null;

        if ($toBigEndian){
            return (int) current(unpack("L", hex2bin($this->toHex(true))));
        }else{
            return (int) current(unpack("L", $this->buffer));
        }
    }

    public function toFloat(){
        return (float) current(unpack("f", $this->buffer));
    }

    public function toBoolean(){
        return (bool) current(unpack("L", $this->buffer));
    }


    /**
     * @param $bytes
     * @return Binary[]
     */
    public function split( $bytes, $toBigEndian = false){
        $parts = str_split(
            $this->toHex(),
            $bytes * 2
        );

        $return = [];
        foreach ($parts as $index => $part) {
            $return[$index] = new Binary($toBigEndian ? Helper::toBigEndian($part) : $part, true);
        }

        return $return;
    }

    public function length($hex = false){
        return $hex ? strlen($this->toHex()) : strlen($this->toHex()) / 2;
    }

    public function remain($hex = false){
        return $hex ? strlen($this->toHex()) : strlen($this->toHex()) / 2;
    }

    public function skipBytes( $bytes = 4){
        return $this->substr($bytes, $this->length(true));
    }


    public function getBytes( $bytes = 4){
        return $this->substr($bytes, $this->length(true))->getBytes();
    }

    public function getMissedBytes( $bytes = 4){
        return $bytes - (($this->length() ) % $bytes);
    }


    public function addMissedBytes( $hex ){
        $missed = $this->getMissedBytes();
        $this->buffer = hex2bin($this->toHex() . str_repeat($hex, $missed));
        return $this;
    }

    public function append( Binary $binary){

        $this->buffer = hex2bin($this->toHex() . $binary->toHex());

    }

    /**
     * @return Binary
     */
    public function toBigEndian(){
        $split = $this->split(1);
        $split = array_reverse($split);
        return new Binary(
            join('', $split),
            true
        );
    }

    public function read( $length, $offset = 0 ){
        return $this->substr($offset, $length);

    }

    public function substr($startOrSearchHexPos, $lengthOrSearchHexPos = null, Binary &$remain = null, $bigEndian = false){

        $length = $lengthOrSearchHexPos;
        $start = $startOrSearchHexPos;
        $hexLength = strlen($this->toHex());


        if (!is_null($startOrSearchHexPos) && !is_numeric($startOrSearchHexPos)){
            $start = mb_strpos($this->buffer, $startOrSearchHexPos);
        }

        if (!is_null($lengthOrSearchHexPos) && !is_numeric($lengthOrSearchHexPos)){
            $length = mb_strpos($this->buffer, $lengthOrSearchHexPos);
        }

        $hex = substr(
            $this->toHex(),
            $start * 2,
            is_null($length) ? $hexLength : $length * 2
        );

        if (!is_null($length) ){
            $remain = new Binary(substr(
                $this->toHex(),
                ($start * 2) + (is_null($length) ? $hexLength : $length * 2)
            ), true);
        }


//        $this->buffer = $remain;

        if ($bigEndian == true){
            return new Binary(Helper::toBigEndian($hex), true);
        }else{
            return new Binary($hex, true);

        }
    }

    private static function int8($i) {
        return is_int($i) ? pack("c", $i) : unpack("c", $i)[1];
    }

    private static function uInt8($i) {
        return is_int($i) ? pack("C", $i) : unpack("C", $i)[1];
    }

    private static function int16($i) {
        return is_int($i) ? pack("s", $i) : unpack("s", $i)[1];
    }

    private static function uInt16($i, $endianness=false) {
        $f = is_int($i) ? "pack" : "unpack";

        if ($endianness === true) {  // big-endian
            $i = $f("n", $i);
        }
        else if ($endianness === false) {  // little-endian
            $i = $f("v", $i);
        }
        else if ($endianness === null) {  // machine byte order
            $i = $f("S", $i);
        }

        return is_array($i) ? $i[1] : $i;
    }

    private static function int32($i) {
        return is_int($i) ? pack("l", $i) : unpack("l", $i)[1];
    }

    private static function uInt32($i, $endianness=false) {
        $f = is_int($i) ? "pack" : "unpack";

        if ($endianness === true) {  // big-endian
            $i = $f("N", $i);
        }
        else if ($endianness === false) {  // little-endian
            $i = $f("V", $i);
        }
        else if ($endianness === null) {  // machine byte order
            $i = $f("L", $i);
        }

        return is_array($i) ? $i[1] : $i;
    }

    private static function int64($i) {
        return is_int($i) ? pack("q", $i) : unpack("q", $i)[1];
    }

    private static function uInt64($i, $endianness=false) {
        $f = is_int($i) ? "pack" : "unpack";

        if ($endianness === true) {  // big-endian
            $i = $f("J", $i);
        }
        else if ($endianness === false) {  // little-endian
            $i = $f("P", $i);
        }
        else if ($endianness === null) {  // machine byte order
            $i = $f("Q", $i);
        }

        return is_array($i) ? $i[1] : $i;
    }


    public function toUInt64( $endianness = false ){
        return $this->uInt64($this->buffer, $endianness);
    }

    public function toUInt32( $endianness = false ){
        return $this->uInt32($this->buffer, $endianness);
    }

    public function toUInt16( $endianness = false ){
        return $this->uInt16($this->buffer, $endianness);
    }

    public function toInt64(){
        return $this->int64($this->buffer);
    }

    public function toInt32(){
        return $this->int32($this->buffer);
    }

    public function toInt8(){
        return $this->int8($this->buffer);
    }


    public function toInt16(){
        return $this->int16($this->buffer);
    }

    public function toUInt8(){
        return $this->uInt8($this->buffer);
    }



}
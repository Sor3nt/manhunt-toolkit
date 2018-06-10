<?php
namespace App\Bytecode;


use App\Bytecode\Mls\Header;
use App\Bytecode\Mls\Scripts;
use App\Bytecode\Mls\Srce;

class Helper{

    public function toBigEndian( $hex ){
        $split = str_split($hex, 2);
        $split = array_reverse($split);
        return join('', $split);
    }

    public function extractAndReplaceStrings( $content ){

        preg_match_all("/\'(.*?)\'/", $content, $strings);

        if (count($strings) == 0) return [
            [],
            $content
        ];

        foreach ($strings[1] as $index => $string) {

            // we replace the given string with the right integer value
            $content = str_replace(sprintf("'%s'", $string), 'str_'.(strlen($string) + 1), $content);
        }

        return [
            $strings[1],
            $content
        ];
    }

    public function splitInnerToOuter( $content ){

        $p = 0;

        $result = [];

        while(substr_count($content, "(") > 0){

            $innerLeft = strrpos($content, '(');
            $functioLeft = substr($content, 0, $innerLeft);

            if (strpos($functioLeft, '(') !== false){
                $functioLeft = substr($functioLeft, strpos($functioLeft, '(') + 1);
            }

            $left = substr($content, $innerLeft);
            $innerContent = substr($left, 1, strpos($left, ')') - 1 );

            $content = str_replace($functioLeft .'(' . $innerContent . ')', 'c' . $p, $content);
            $p++;

            $result[] = [
                'function' => $functioLeft,
                'parameters' => array_map('trim', explode(',', $innerContent))
            ];

        }

        return $result;

    }

    public function pad($hex, $lim = 8, $before = false, $char = "0")
    {
        return (
            strlen($hex) >= $lim)
            ?
                $hex
            :
                $this->pad(
                    $before ?
                        $char . $hex
                        :
                        $hex . $char
                    ,
                    $lim,
                    $before,
                    $char
                );
    }

    public function toSize( $dara, $bigEndian = true ){


        $codeLenght = strlen($dara) / 2;
        $padded = $this->pad(dechex($codeLenght),4, true);
        if ($bigEndian) $padded = $this->toBigEndian($padded);
        return $this->pad($padded);

    }

    public function fromIntToHex( $int ){
        $codeLenght = $this->toBigEndian($this->pad(dechex($int),4, true));
        return $this->pad($codeLenght);

    }

}
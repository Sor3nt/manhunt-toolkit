<?php

namespace App\Service;

class ImageMagick
{
    public string $binary = "convert";

    public function isAvailable(){
        $where = exec(sprintf('%s --help', $this->binary));
        return strpos($where, "for standard input or output") !== false;
    }


    public function convertToDXT( $binary, $format ){

        $tmp1 = uniqid(rand(), true);
        $tmp2 = uniqid(rand(), true) . '.dds';
        file_put_contents($tmp1, $binary);
        system(sprintf('%s %s -define dds:compression=%s -define dds:mipmaps=0 %s', $this->binary, $tmp1, $format, $tmp2));
        $content = file_get_contents($tmp2);
        unlink($tmp1);
        unlink($tmp2);
        return $content;
    }

    public function convertTo( $binary, $format ){

        $tmp1 = uniqid(rand(), true);
        $tmp2 = uniqid(rand(), true) . '.' . $format;
        file_put_contents($tmp1, $binary);
        system(sprintf('%s %s %s', $this->binary, $tmp1, $tmp2));
        $content = file_get_contents($tmp2);
        unlink($tmp1);
        unlink($tmp2);
        return $content;
    }



}
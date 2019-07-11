<?php
namespace App\Service\Archive\Textures;

use App\Service\NBinary;

class Image {

    public function saveImage($data, $width, $height){

        $img = imagecreatetruecolor($width, $height);
        imagesavealpha($img, true);

        $x = 0;
        $y = 0;
        foreach ($data as $rgba) {
//            $color =  imagecolorallocatealpha($img,$rgba[0],$rgba[1],$rgba[2],$rgba[3]);
            $color =  imagecolorallocate($img,$rgba[0],$rgba[1],$rgba[2]);
            imagesetpixel($img,$x,$y,$color);

            $x++;
            if ($x >= $width){
                $x = 0;
                $y++;
            }
        }

        ob_start();
        imagejpeg($img, null, 100);
        return ob_get_clean();
//        imagepng($img, $filename . ".png");
    }

}
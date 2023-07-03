<?php
namespace App\Service\Archive\Textures;

use App\Service\NBinary;

class Image {

    /**
     * @param $rgba
     * @param $width
     * @param $height
     * @param string $format
     * @return string
     * @throws \Exception
     */
    public function rgbaToImage( $rgba, $width, $height){
        $img = imagecreatetruecolor($width, $height);

        //fill the image with transparent otherwise its black...
        $transparent = imagecolorallocatealpha( $img, 0, 0, 0, 127 );
        imagefill( $img, 0, 0, $transparent );

        $i = 0;
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {

                if (!isset($rgba[$i + 3 ])){
                    var_dump("some rgba issues ?!");
                    return "";
                }

                $color =  imagecolorallocatealpha(
                    $img,
                    $rgba[$i],
                    $rgba[$i + 1],
                    $rgba[$i + 2],
                    127 - ($rgba[$i + 3 ] >> 1)

                );

                imagesetpixel($img,$x,$y,$color);

                $i +=4;
            }
        }


        ob_start();

        imagesavealpha($img, true);

        imagepng($img, null, 9);


        return ob_get_clean();
    }
    /**
     * @param $rgba
     * @param $width
     * @param $height
     * @param string $format
     * @return string
     * @throws \Exception
     */
    public function rampsToImage( $rgba, $width, $height){

        $img = imagecreatetruecolor($width, $height);

        $i = 0;
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {

                $color =  imagecolorallocate(
                    $img,
                    $rgba[$i],
                    $rgba[$i],
                    $rgba[$i]

                );

                imagesetpixel($img,$x,$y,$color);

                $i +=1;
            }
        }


        ob_start();

        imagejpeg($img, null, 9);

        return ob_get_clean();
    }


}
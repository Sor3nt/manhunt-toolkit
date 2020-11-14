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
    public function rgbaToImage( $rgba, $width, $height, $format = "png"){
        $img = imagecreatetruecolor($width, $height);

        //fill the image with transparent otherwise its black...
        $transparent = imagecolorallocatealpha( $img, 0, 0, 0, 127 );
        imagefill( $img, 0, 0, $transparent );

        $i = 0;
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {

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

        if ($format == "png") imagepng($img, null, 9);
        else throw new \Exception('Output Format not implemented');

        return ob_get_clean();
    }


}
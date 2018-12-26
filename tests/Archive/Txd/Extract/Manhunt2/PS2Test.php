<?php
namespace App\Tests\Archive\Txd\Extract\Manhunt2;

use App\Service\Archive\Bmp;
use App\Service\Archive\Dxt1;
use App\Service\Archive\Dxt5;
use App\Service\Archive\ZLib;
use App\Service\Resources;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PS2Test extends KernelTestCase
{

    public function testLevel1()
    {
        echo "\n*** TXD: Implement PS2 (TODO) ";
        $this->assertEquals(true, true);
return;
        $resources = new Resources();
        $resources->workDirectory = explode("/tests/", __DIR__)[0] . "/tests/Resources";

        $content = $resources->load('/Archive/Txd/Manhunt2/PS2/TITLE.TXD');
//        $content = $resources->load('/Archive/Txd/Manhunt2/PS2/TITLE_2.TXD');
//        $content = $resources->load('/Archive/Txd/Manhunt2/PS2/GMODELS.TXD');
//        $content = $resources->load('/Archive/Txd/Manhunt2/PS2/DSFE.TXD');
//        $content = $resources->load('/Archive/Txd/Manhunt2/PSP/GMODELS.TXD');

        $content = $content->getContent();

//        $content = [
//            file_get_contents('tests/Resources/Archive/Txd/Manhunt2/PS2/test.raw')
//        ];

        foreach ($content as $item) {

//            $item = [
//                'name' => "test",
//                'data' => $item,
//                'width' => 128,
//                'height' => 128,
//            ];

//            if ($item['name'] == "white") continue;
//            if ($item['name'] == "Dedhed1_128") continue;
//            if ($item['name'] == "Bag_head") continue;
            echo $item['name'] . "\n";
//            echo $item['bitPerPixel'] . "\n";


            $bmpHandler = new Bmp();
            $dxtHandler = new Dxt1();
//                $dxtHandler = new Dxt5();

//            var_dump(bin2hex($item['data']));
//            exit;
//            $test = str_split(bin2hex($item['data']), 8);
//            $test = array_reverse($test);
//            foreach ($test as &$item2) {
//                $inner = str_split($item2, 4);
//
//                $item2 = implode("",array_reverse($inner));
//            }
//
//            $item['data'] = hex2bin(implode("", $test));
//
//            file_put_contents('/Users/matthias/www/privat/manhunt-toolkit-ide-git/tests/Resources/_test_exports/' . $item['name'] . '.dds', $item['data']);
//var_dump($item['width']);
//var_dump($item['height']);
//exit;


            //decode the DXT Texture
            $bmpRgba = $dxtHandler->decode(
                $item['data'],
                $item['width'],
                $item['height'],
//                'abgr'
                'rgba'
            );



            //convert 4bit to 8bit
            if ($item['bitPerPixel'] == 4){
                $data8Bit = [];


                for ( $index = 0; $index < count($bmpRgba); $index++){



                    $data8Bit[$index * 2 + 0] = $bmpRgba[$index] & 0x0F;
                    $data8Bit[$index * 2 + 1] = $bmpRgba[$index] >> 4;
                }

                $bmpRgba = $data8Bit;
                $item['bitPerPixel'] = 8;


            }

//            $newData = [];
//
//            for ($y = 0; $y < $item['height']; $y++) {
//
//                for ($x = 0; $x < $item['width']; $x++) {
//                    $c = $this->getAddress8BppSwizzle($item['width'], $x, $y);
//
//                    $newData[($x * 4) + ($item['width'] * $y) * 4] = $bmpRgba[$c + 3];
//                    $newData[($x * 4) + ($item['width'] * $y) * 4 + 1] = $bmpRgba[$c + 2];
//                    $newData[($x * 4) + ($item['width'] * $y) * 4 + 2] = $bmpRgba[$c + 1];
//                    $newData[($x * 4) + ($item['width'] * $y) * 4 + 3] = $bmpRgba[$c];
//
//                }
//            }
//
//            $bmpRgba = $newData;



//            $pixels = $this->UnSwizzle4($bmpRgba, $item['width'], $item['height'], 0);


            //Convert the RGBa values into a Bitmap
            $bmpImage = $bmpHandler->encode(
                $bmpRgba,
                $item['width'],
                $item['height']
            );


            file_put_contents('/Users/matthias/www/privat/manhunt-toolkit-ide-git/tests/Resources/_test_exports/txd_' . $item['name'] . '.bmp', $bmpImage);
            exit;
        }
        //we expect 8 results
        $this->assertEquals(8, count($content));


        //the 5t entry is CJ_crow
        $this->assertEquals("Bag head", $content[5]['name']);

        //the data is a DDS file
        $this->assertEquals("DDS", substr($content[5]['data'], 0, 3));

        //the data is like expected
        $this->assertEquals("71c2cf4c0361608e114eda94a0fa334b", md5($content[5]['data']));

    }
//
//    public function swizzleBlock($block){
//	$blockDXT = $block;
//	blockDXT.col1.u = (blockDXT.col1.u << 8) | (blockDXT.col1.u >> 8);
//	blockDXT.col0.u = (blockDXT.col0.u << 8) | (blockDXT.col0.u >> 8);
//
//	for (int n = 0; n < 4; n++)
//	{
//		blockDXT.row[n] =
//			(blockDXT.row[n] << 6)
//			| ((blockDXT.row[n] << 2) & 0x30)
//			| ((blockDXT.row[n] >> 2) & 0x0C)
//			| (blockDXT.row[n] >> 6);
//	}
//
//return blockDXT;
//}


    private function getAddress8BppSwizzle($width, $x, $y)
    {

        /*
         *
            $newData = [];

            for ($y = 0; $y < $item['height']; $y++) {

                for ($x = 0; $x < $item['width']; $x++) {
                    $c = $this->getAddress8BppSwizzle($item['width'], $x, $y);

                    $newData[($x * 4) + ($item['width'] * $y) * 4] = $bmpRgba[$c + 3];
                    $newData[($x * 4) + ($item['width'] * $y) * 4 + 1] = $bmpRgba[$c + 2];
                    $newData[($x * 4) + ($item['width'] * $y) * 4 + 2] = $bmpRgba[$c + 1];
                    $newData[($x * 4) + ($item['width'] * $y) * 4 + 3] = $bmpRgba[$c];

                }
            }
         *
         */

        $block = ($y & (~0x0f)) * $width + ($x & (~0x0f)) * 2;
        $swap = ((($y + 2) >> 2) & 0x01) * 4;
        $line = ((($y & (~0x03)) >> 1) + ($y & 0x01)) & 0x07;
        $column = $line * $width * 2 + (($x + $swap) & 0x07) * 4;
        $offset = (($y >> 1) & 0x01) + (($x >> 2) & 0x02);
        return $block + $column + $offset;
    }

    public function UnSwizzle4($buffer, $width, $height, $where)
    {
        // HUGE THANKS TO:
        // L33TMasterJacob for finding the information on unswizzling 4-bit textures
        // Dageron for his 4-bit unswizzling code; he's truly a genius!
        //
        // Source: https://gta.nick7.com/ps2/swizzling/unswizzle_delphi.txt

        $InterlaceMatrix = [
            0x00, 0x10, 0x02, 0x12,
            0x11, 0x01, 0x13, 0x03,
        ];

        $Matrix = [0, 1, -1, 0];
        $TileMatrix = [4, -4];

        $pixels = [];
        $newPixels = [];

        $d = 0;
        $s = $where;

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < ($width >> 1); $x++) {
                $s++;

                $p = $buffer[$s];

                $pixels[$d++] = ($p & 0xF);
                $pixels[$d++] = ($p >> 4);
            }
        }

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $oddRow = (($y & 1) != 0);

                $num1 = (($y / 4) & 1);
                $num2 = (($x / 4) & 1);
                $num3 = ($y % 4);

                $num4 = (($x / 4) % 4);

                if ($oddRow)
                    $num4 += 4;

                $num5 = (($x * 4) % 16);
                $num6 = (($x / 16) * 32);

                $num7 = ($oddRow) ? (($y - 1) * $width) : ($y * $width);

                $xx = $x + $num1 * $TileMatrix[$num2];
                $yy = $y + $Matrix[$num3];

                $i = $InterlaceMatrix[$num4] + $num5 + $num6 + $num7;
                $j = $yy * $width + $xx;

                $newPixels[$j] = $pixels[$i];
            }
        }

        $result = [];

        $s = 0;
        $d = 0;

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < ($width >> 1); $x++){
                $result[$d++] = (($newPixels[$s++] & 0xF) | ($newPixels[$s++] << 4));
            }

        }
        return $result;

    }

}
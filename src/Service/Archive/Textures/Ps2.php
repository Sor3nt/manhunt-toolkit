<?php
namespace App\Service\Archive\Textures;

use App\Service\NBinary;

class Ps2 extends Image {


    private $alphaEncodingTable = [
        0,   1,   1,   2,   2,   3,   3,   4,   4,   5,   5,   6,   6,   7,   7,   8,
        8,   9,   9,   10,  10,  11,  11,  12,  12,  13,  13,  14,  14,  15,  15,  16,
        16,  17,  17,  18,  18,  19,  19,  20,  20,  21,  21,  22,  22,  23,  23,  24,
        24,  25,  25,  26,  26,  27,  27,  28,  28,  29,  29,  30,  30,  31,  31,  32,
        32,  33,  33,  34,  34,  35,  35,  36,  36,  37,  37,  38,  38,  39,  39,  40,
        40,  41,  41,  42,  42,  43,  43,  44,  44,  45,  45,  46,  46,  47,  47,  48,
        48,  49,  49,  50,  50,  51,  51,  52,  52,	 53,  53,  54,  54,  55,  55,  56,
        56,  57,  57,  58,  58,  59,  59,  60,  60,  61,  61,  62,  62,  63,  63,  64,
        64,  65,  65,  66,  66,  67,  67,  68,  68,  69,  69,  70,  70,  71,  71,  72,
        72,  73,  73,  74,  74,  75,  75,  76,  76,  77,  77,  78,  78,  79,  79,  80,
        80,  81,  81,  82,  82,  83,  83,  84,  84,  85,  85,  86,  86,  87,  87,  88,
        88,  89,  89,  90,  90,  91,  91,  92,  92,  93,  93,  94,  94,  95,  95,  96,
        96,  97,  97,  98,  98,  99,  99,  100, 100, 101, 101, 102, 102, 103, 103, 104,
        104, 105, 105, 106, 106, 107, 107, 108, 108, 109, 109, 110, 110, 111, 111, 112,
        112, 113, 113, 114, 114, 115, 115, 116, 116, 117, 117, 118, 118, 119, 119, 120,
        120, 121, 121, 122, 122, 123, 123, 124, 124, 125, 125, 126, 126, 127, 127, 128
    ];

    private $alphaDecodingTable = [

        0,   2,   4,   6,   8,   10,  12,  14,  16,  18,  20,  22,  24,  26,  28,  30,
        32,  34,  36,  38,  40,  42,  44,  46,  48,  50,  52,  54,  56,  58,  60,  62,
        64,  66,  68,  70,  72,  74,  76,  78,  80,  82,  84,  86,  88,  90,  92,  94,
        96,  98,  100, 102, 104, 106, 108, 110, 112, 114, 116, 118, 120, 122, 124, 126,
        128, 129, 131, 133, 135, 137, 139, 141, 143, 145, 147, 149, 151, 153, 155, 157,
        159, 161, 163, 165, 167, 169, 171, 173, 175, 177, 179, 181, 183, 185, 187, 189,
        191, 193, 195, 197, 199, 201, 203, 205, 207, 209, 211, 213, 215, 217, 219, 221,
        223, 225, 227, 229, 231, 233, 235, 237, 239, 241, 243, 245, 247, 249, 251, 253,
        255

    ];

    private function unswizzle( $texture, $bmpRgba ){

        $result = [];

        for ($y = 0; $y < $texture['height']; $y++){

            for ($x = 0; $x < $texture['width']; $x++) {
                $block_loc = ($y&(~0x0F))*$texture['width'] + ($x&(~0x0F))*2;
                $swap_sel = ((($y+2)>>2)&0x01)*4;
                $ypos = ((($y&(~3))>>1) + ($y&1))&0x07;
                $column_loc = $ypos*$texture['width']*2 + (($x+$swap_sel)&0x07)*4;
                $byte_sum = (($y>>1)&1) + (($x>>2)&2);
                $swizzled = $block_loc + $column_loc + $byte_sum;

                $result[$y*$texture['width']+$x] = $bmpRgba[$swizzled];
            }

        }

        return $result;
    }

    public function getPaletteSize( $format ){
        switch ($format){
            case "20000000":
                return 64;
            case "40000000":
                return 64;
            case "80000000":
                return 1024;
            case "00010000":
                return 1024;
            default:
                throw new \Exception(sprintf("Unknown raster format %s", $format));
                break;
        }

    }

    public function getRasterSize( $format, $width, $height ){
        switch ($format){
            case "20000000":
                return ($width * $height) / 2;
            case "40000000":
                return ($width * $height) / 2;
            case "80000000":
                return ($width * $height);
            case "00010000":
                return ($width * $height);
            default:
                throw new \Exception(sprintf("Unknown raster format %s", $format));
                break;
        }

    }


    public function convertToRgba($texture ){


        $palette = $this->decode32ColorsToRGBA( new NBinary($texture['palette']));

        if ($texture['bitPerPixel'] == 4) {

            $bmpRgba = $this->convertIndexed4ToRGBA(
                $texture['data'],
                ($texture['width'] * $texture['height']),
                $palette
            );
        }else if ($texture['bitPerPixel'] == 8){

            $palette = $this->paletteUnswizzle($palette);

            $bmpRgba = $this->convertIndexed8ToRGBA(
                $texture['data'],
                $palette
            );


        }else{
            throw new \Exception(sprintf("Unknown bitPerPixel format %s", $texture['bitPerPixel']));
        }

        if ($texture['rasterFormat'] == "00010000" && $texture['bitPerPixel'] == 4) {
        }else{
            $bmpRgba = $this->unswizzle($texture, $bmpRgba);

        }

        return $bmpRgba;

    }

    private function paletteUnswizzle($palette){

        // I know its a ugly solution but it works for now....

        return [


            $palette[0],
            $palette[1],
            $palette[2],
            $palette[3],
            $palette[4],
            $palette[5],
            $palette[6],
            $palette[7],


            $palette[16],
            $palette[17],
            $palette[18],
            $palette[19],
            $palette[20],
            $palette[21],
            $palette[22],
            $palette[23],


            $palette[8],
            $palette[9],
            $palette[10],
            $palette[11],
            $palette[12],
            $palette[13],
            $palette[14],
            $palette[15],





            $palette[24],
            $palette[25],
            $palette[26],
            $palette[27],
            $palette[28],
            $palette[29],
            $palette[30],
            $palette[31],



            $palette[32],
            $palette[33],
            $palette[34],
            $palette[35],
            $palette[36],
            $palette[37],
            $palette[38],
            $palette[39],





            $palette[48],
            $palette[49],
            $palette[50],
            $palette[51],
            $palette[52],
            $palette[53],
            $palette[54],
            $palette[55],



            $palette[40],
            $palette[41],
            $palette[42],
            $palette[43],
            $palette[44],
            $palette[45],
            $palette[46],
            $palette[47],

            $palette[56],
            $palette[57],
            $palette[58],
            $palette[59],
            $palette[60],
            $palette[61],
            $palette[62],
            $palette[63],



            $palette[64],
            $palette[65],
            $palette[66],
            $palette[67],
            $palette[68],
            $palette[69],
            $palette[70],
            $palette[71],




            $palette[80],
            $palette[81],
            $palette[82],
            $palette[83],
            $palette[84],
            $palette[85],
            $palette[86],
            $palette[87],



            $palette[72],
            $palette[73],
            $palette[74],
            $palette[75],
            $palette[76],
            $palette[77],
            $palette[78],
            $palette[79],


            $palette[88],
            $palette[89],
            $palette[90],
            $palette[91],
            $palette[92],
            $palette[93],
            $palette[94],
            $palette[95],



            $palette[96],
            $palette[97],
            $palette[98],
            $palette[99],
            $palette[100],
            $palette[101],
            $palette[102],
            $palette[103],



            $palette[112],
            $palette[113],
            $palette[114],
            $palette[115],
            $palette[116],
            $palette[117],
            $palette[118],
            $palette[119],


            $palette[104],
            $palette[105],
            $palette[106],
            $palette[107],
            $palette[108],
            $palette[109],
            $palette[110],
            $palette[111],




            $palette[120],
            $palette[121],
            $palette[122],
            $palette[123],
            $palette[124],
            $palette[125],
            $palette[126],
            $palette[127],



            $palette[128],
            $palette[129],
            $palette[130],
            $palette[131],
            $palette[132],
            $palette[133],
            $palette[134],
            $palette[135],




            $palette[144],
            $palette[145],
            $palette[146],
            $palette[147],
            $palette[148],
            $palette[149],
            $palette[150],
            $palette[151],

            $palette[136],
            $palette[137],
            $palette[138],
            $palette[139],
            $palette[140],
            $palette[141],
            $palette[142],
            $palette[143],




            $palette[152],
            $palette[153],
            $palette[154],
            $palette[155],
            $palette[156],
            $palette[157],
            $palette[158],
            $palette[159],



            $palette[160],
            $palette[161],
            $palette[162],
            $palette[163],
            $palette[164],
            $palette[165],
            $palette[166],
            $palette[167],




            $palette[176],
            $palette[177],
            $palette[178],
            $palette[179],
            $palette[180],
            $palette[181],
            $palette[182],
            $palette[183],



            $palette[168],
            $palette[169],
            $palette[170],
            $palette[171],
            $palette[172],
            $palette[173],
            $palette[174],
            $palette[175],


            $palette[184],
            $palette[185],
            $palette[186],
            $palette[187],
            $palette[188],
            $palette[189],
            $palette[190],
            $palette[191],



            $palette[192],
            $palette[193],
            $palette[194],
            $palette[195],
            $palette[196],
            $palette[197],
            $palette[198],
            $palette[199],




            $palette[208],
            $palette[209],
            $palette[210],
            $palette[211],
            $palette[212],
            $palette[213],
            $palette[214],
            $palette[215],



            $palette[200],
            $palette[201],
            $palette[202],
            $palette[203],
            $palette[204],
            $palette[205],
            $palette[206],
            $palette[207],


            $palette[216],
            $palette[217],
            $palette[218],
            $palette[219],
            $palette[220],
            $palette[221],
            $palette[222],
            $palette[223],



            $palette[224],
            $palette[225],
            $palette[226],
            $palette[227],
            $palette[228],
            $palette[229],
            $palette[230],
            $palette[231],




            $palette[240],
            $palette[241],
            $palette[242],
            $palette[243],
            $palette[244],
            $palette[245],
            $palette[246],
            $palette[247],



            $palette[232],
            $palette[233],
            $palette[234],
            $palette[235],
            $palette[236],
            $palette[237],
            $palette[238],
            $palette[239],


            $palette[248],
            $palette[249],
            $palette[250],
            $palette[251],
            $palette[252],
            $palette[253],
            $palette[254],
            $palette[255],


        ];
    }


    private function convertIndexed8ToRGBA( $indexed4Data, $palette ){

        $result = [];

        $binary = new NBinary( $indexed4Data );

        for ($i = 0; $i < $binary->length(); $i++) {
            $src = $binary->consume(1, NBinary::U_INT_8);

            $result[] = $palette[$src];
        }

        return $result;

    }


    private function convertIndexed4ToRGBA( $indexed4Data, $count, $palette ){
        $result = [];

        $binary = new NBinary( $indexed4Data );

        for ($i = 0; $i < $count; $i = $i + 2) {
            $val = $binary->consume(1, NBinary::U_INT_8);

            $result[] = $palette[$val & 0x0F];
            $result[] = $palette[$val >> 4];
        }

        return $result;

    }


    private function decode32ColorsToRGBA( NBinary $colors){

        $result = [];

        while ($colors->remain()) {
            $dst = [];
            $dst[] = $colors->consume(1, NBinary::U_INT_8); //r
            $dst[] = $colors->consume(1, NBinary::U_INT_8); //g
            $dst[] = $colors->consume(1, NBinary::U_INT_8); //b
            $alpha = $colors->consume(1, NBinary::U_INT_8); //a
//
            $dst[] = $alpha > 0x80 ? 255 : $this->alphaDecodingTable[$alpha];

            $result[] = $dst;
        }

        return $result;
    }




}
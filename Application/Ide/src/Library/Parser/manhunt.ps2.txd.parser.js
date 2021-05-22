
function Playstation2Texture(ARGUMENTS) {

    const alphaDecodingTable = [

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


    function unswizzlePsp(texture, bmpRgba, as4Bit) {

        if (texture.width <= 16) return bmpRgba;

        let BlockWidth = as4Bit ? 32 : 16;
        let BlockHeight = 8;

        if (texture.width === 16){
            BlockWidth = 16;
        }

        let BlockSize = BlockHeight * BlockWidth;

        let start = 0;
        let end = bmpRgba.length;

        let unswizzled = [];
        bmpRgba.forEach(function (item) {
            unswizzled.push([0,0,0,0]);
        });
        let swizzled = bmpRgba;

        let size = end - start;
        let blockCount = size / BlockSize;
        let blocksPerRow = texture.width / BlockWidth;

        for (let  block = 0; block < blockCount; ++block)
        {
            let by = parseInt((block / blocksPerRow) * BlockHeight);
            let bx = parseInt((block % blocksPerRow) * BlockWidth);

            for (let y = 0; y < BlockHeight; y++)
            {

                for (let x = 0; x < BlockWidth; x++)
                {
                    unswizzled[start + (by + y) * texture.width + bx + x] =
                        swizzled[start + block * BlockSize + y * BlockWidth + x];
                }
            }
        }

        return unswizzled;
    }


    function unswizzlePs2(texture, bmpRgba) {

        let result = [];

        for (let y = 0; y < texture.height; y++){

            for (let x = 0; x < texture.width; x++) {
                let block_loc = (y&(~0x0F))*texture.width + (x&(~0x0F))*2;
                let swap_sel = (((y+2)>>2)&0x01)*4;
                let ypos = (((y&(~3))>>1) + (y&1))&0x07;
                let column_loc = ypos*texture.width*2 + ((x+swap_sel)&0x07)*4;
                let byte_sum = ((y>>1)&1) + ((x>>2)&2);
                let swizzled = block_loc + column_loc + byte_sum;

                result[y*texture.width+x] = bmpRgba[swizzled];
            }

        }

        return result;
    }


    function getPaletteSize(format, bpp) {

        if (format === "00010000" && bpp === 8) return 1024;
        if (format === "10000000" && bpp === 8) return 1024;
        if (format === "20000000" && bpp === 8) return 1024;
        if (format === "40000000" && bpp === 8) return 1024;
        if (format === "80000000" && bpp === 32) return 1024;
        if (format === "80000000" && bpp === 8) return 1024;
        if (format === "80000000" && bpp === 4) return 1024;
        if (format === "00010000" && bpp === 32) return 1024;


        if (format === "08000000" && bpp === 4) return 64;
        if (format === "10000000" && bpp === 4) return 64;
        if (format === "20000000" && bpp === 4) return 64;
        if (format === "40000000" && bpp === 4) return 64;
        if (format === "00010000" && bpp === 4) return 64;
        if (format === "00020000" && bpp === 8) return 1024;

        console.error("Unknown palette format " + format + " bpp:" + bpp);
        die;
    }


    function getRasterSize (format, width, height, bpp) {

        if (format === "80000000" && bpp === 32) return width * height;
        if (format === "80000000" && bpp === 8) return width * height;
        if (format === "08000000" && bpp === 4) return (width * height) / 2;
        if (format === "10000000" && bpp === 4) return (width * height) / 2;
        if (format === "80000000" && bpp === 4) return (width * height) / 2;
        if (format === "00010000" && bpp === 8) return width * height;
        if (format === "20000000" && bpp === 4) return (width * height) / 2;
        if (format === "00010000" && bpp === 4) return width * height;
        if (format === "40000000" && bpp === 4) return (width * height) / 2;
        if (format === "40000000" && bpp === 8) return width * height;
        if (format === "20000000" && bpp === 8) return width * height;
        if (format === "10000000" && bpp === 8) return width * height;
        if (format === "00010000" && bpp === 32) return width * height;
        if (format === "00020000" && bpp === 8) return width * height;

        console.error("Unknown raster format " + format + " bpp:" + bpp);
        die;
    }



    function convertToRgba(texture, platform) {

        let palette = false;
        if (texture.palette){
            palette = decode32ColorsToRGBA( texture.palette);
        }

        let is4Bit = texture.bitPerPixel === 4;

        let bmpRgba;
        if (texture.bitPerPixel === 4) {

            if (palette){
                bmpRgba = convertIndexed4ToRGBA(
                    texture.data,
                    (texture.width * texture.height),
                    palette
                );

            }else{
                console.error("todo 4bit no palette");
                die;
            }

        }else if (texture.bitPerPixel === 8){

            if (palette) {
                if (platform === "ps2") {
                    palette = paletteUnswizzle(palette);
                }

                bmpRgba = convertIndexed8ToRGBA(
                    texture.data,
                    palette
                );
            }else{
                console.error("todo 8bit no palette");
                die;
            }

        }else if (texture.bitPerPixel === 32){

            bmpRgba = decode32ColorsToRGBA( new NBinary(texture.data));

        }else{
            console.error("Unknown bitPerPixel format " + texture.bitPerPixel);
            die;
        }

        if (platform === "ps2" && texture.swizzleMask & 0x1 !== 0) {
            bmpRgba = unswizzlePs2(texture, bmpRgba);
        }else if (platform === "psp" || platform === "psp001"){
            bmpRgba = unswizzlePsp(texture, bmpRgba, is4Bit);
        }

        //flat the rgba array

        let rgbaFlat = [];
        bmpRgba.forEach(function (block) {
            rgbaFlat.push(block[0],block[1],block[2],block[3]);
        });


        return rgbaFlat;

    }


    function paletteUnswizzle(palette) {


        //Ruleset:

        /*
             * 1. first 8 colors stay
             *
             * 2. next 8 colors are twisted with the followed 8 colors
             * 3. 16 colors stay
             *
             * 4. goto step 2
             */

        let newPalette = [];

        var i,j,chunk = 8;
        let palChunks = [];
        for (i=0,j=palette.length; i<j; i+=chunk) {
            palChunks.push(palette.slice(i,i+chunk));
        }

        // let palChunks = array_chunk(palette, 8);

        let current = 0;
        let swapCount = 2;

        while(current < palChunks.length){

            let chunk = palChunks[current];

            if (current === 0){
                newPalette.push(chunk);
                current++;
                swapCount = 2;
                continue;
            }


            if (swapCount === 2){
                newPalette.push(palChunks[current + 1]);
                newPalette.push(palChunks[current]);
                current++;
                swapCount = 0;
            }else{
                newPalette.push(chunk);
                swapCount++;
            }

            current++;
        }

        let finalPalette = [];
        newPalette.forEach(function (chunk) {
            chunk.forEach(function (rgba) {
                finalPalette.push(rgba);
            });
        });

        return finalPalette;
    }



    function convertIndexed8ToRGBA(indexed4Data, palette) {

        let result = [];

        let binary = new NBinary( indexed4Data );

        for (i = 0; i < binary.length(); i++) {
            let src = binary.consume(1, 'uint8');
            result.push(palette[src]);
        }

        return result;

    }



    function convertIndexed4ToRGBA(indexed4Data, count, palette) {

        let result = [];

        let binary = new NBinary( indexed4Data );

        for (i = 0; i < count; i = i + 2) {
            let val = binary.consume(1, 'uint8');

            result.push(palette[val & 0x0F]);
            result.push(palette[val >> 4]);
        }

        return result;

    }



    function decode32ColorsToRGBA(colors) {

        let result = [];

        while (colors.remain()) {
            let dst = [];

            dst.push(colors.consume(1, 'uint8')); //r

            dst.push(colors.consume(1, 'uint8')); //g

            dst.push(colors.consume(1, 'uint8')); //b

            let alpha = colors.consume(1, 'uint8'); //a

            //

            dst.push(alpha > 0x80 ? 255 : alphaDecodingTable[alpha]);

            result.push(dst);
        }

        return result;
    }


    return {
        convertToRgba: convertToRgba
    }

}




MANHUNT.parser.manhuntPs2Txd = function (binary) {


    function parseHeader() {
        return {
            id: binary.consume(4, 'int32'),
            size: binary.consume(4, 'uint32'),
            version: binary.consume(4, 'uint32')
        };
    }
    
    function readChunk(){
        let header = parseHeader();
        if (header.size === 0) return {type: 'na'};

        switch (header['id']){
            case 3:
            case 22:
                binary.seek(4 * 4); //id3: skip chunk + unknown, id22: skip chunk, texture count and deviceId
                break;

            case 21:

                binary.seek(20);  // skip platform

                let nameHeader = parseHeader(binary);
                let name = binary.consume(nameHeader['size'], 'nbinary');
                name = name.getString(0);
                let alphaNameHeader = parseHeader(binary);
                let alphaName = binary.consume(alphaNameHeader['size'], 'nbinary');
                alphaName = alphaName.getString(0);

                binary.seek(6 * 4);// skip 2 chunks

                let width = [binary.consume(4, 'uint32')];
                let height = [binary.consume(4, 'uint32')];
                let depth = binary.consume(4, 'uint32');
                let rasterFormat = binary.consume(4, 'uint32');

                binary.seek(8 * 4);//4*uiTex + 4*miptbp
                let dataSize = binary.consume(4, 'uint32');
                binary.seek(6 * 4);//paletteDataSize, uiGpuDataAlignedSize, uiSkyMipmapVal, chunk header

                let hasHeader = (rasterFormat & 0x20000);

                let blockEnd = binary.current() + dataSize;

                let texels = [];
                let swizzleWidth = [];
                let swizzleHeight = [];
                let i = 0;
                while(binary.current() < blockEnd){

                    if (i > 0) {
                        width.push(width[i-1]/2);
                        height.push(height[i-1]/2);
                    }

                    if (hasHeader){
                        binary.seek(8 * 4);
                        swizzleWidth.push(binary.consume(4, 'uint32'));
                        swizzleHeight.push(binary.consume(4, 'uint32'));
                        binary.seek(6 * 4);
                        dataSize = binary.consume(4, 'uint32') * 0x10;
                        binary.seek(3 * 4);
                    }else{
                        swizzleWidth.push(width[i]);
                        swizzleHeight.push(height[i]);
                        dataSize = height[i]*height[i]*depth/8;
                    }

                    texels.push(binary.consume(dataSize, 'nbinary'));

                    i++;
                }

                let palette = false;
                if (rasterFormat & 0x2000 || rasterFormat & 0x4000) {
                    let unkh2 = 0;
                    let unkh3 = 0;
                    let unkh4 = 0;
                    if (hasHeader){
                        binary.seek(8 * 4);
                        unkh2 = binary.consume(4, 'uint32');
                        unkh3 = binary.consume(4, 'uint32');
                        binary.seek(6 * 4);
                        unkh4 = binary.consume(4, 'uint32');
                        binary.seek(3 * 4);
                    }

                    let paletteSize = (rasterFormat & 0x2000) ? 0x100 : 0x10;
                    palette = binary.consume(paletteSize * 4, "nbinary");

                    if (unkh2 === 8 && unkh3 === 3 && unkh4 === 6)
                        binary.seek(0x20);
                }

                return {
                    type: 'texture',
                    swizzleMask: swizzleHeight[0] !== height[0] ? 0x1 : 0x00,
                    name: name,
                    width: width[0],
                    height: height[0],
                    bitPerPixel: depth,
                    rasterFormat: rasterFormat,
                    alphaName: alphaName,
                    palette: palette,
                    data: texels[0].data
                };

        }

        return {type: 'na'};
    }

    let textures = [];
    let psImage = new Playstation2Texture();

    do{

        let result = readChunk();
        if (result.type === "texture"){

            textures.push({
                format: THREE.RGBAFormat,
                name: result.name,
                width: result.width,
                height: result.height,
                data: new Uint8Array( psImage.convertToRgba(result, 'ps2') )
            } );
        }
        
    }while(binary.remain() > 0);

    return textures;
};
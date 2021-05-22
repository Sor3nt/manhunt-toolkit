MANHUNT.parser.tex = function (binary,platform) {


    function getRasterSize( format, width, height, bpp ){

        if (format === 128 && bpp === 32) return width * height;
        if (format === 128 && bpp === 8) return width * height;
        if (format === "08000000" && bpp === 4) return (width * height) / 2;
        if (format === 16 && bpp === 4) return (width * height) / 2;
        if (format === 128 && bpp === 4) return (width * height) / 2;
        if (format === "00010000" && bpp === 8) return width * height;
        if (format === 32 && bpp === 4) return (width * height) / 2;
        if (format === "00010000" && bpp === 4) return width * height;
        if (format === 64 && bpp === 4) return (width * height) / 2;
        if (format === 64 && bpp === 8) return width * height;
        if (format === 32 && bpp === 8) return width * height;
        if (format === 16 && bpp === 8) return width * height;
        if (format === "00010000" && bpp === 32) return width * height;
        if (format === "00020000" && bpp === 8) return width * height;

        console.error("Unknown raster format " + format + " bpp:" + bpp);
        die;
    }


    function getPaletteSize( format, bpp ){
        // if (format === "00010000" && bpp === 8) return 1024;
        if (format === 16 && bpp === 8) return 1024;
        if (format === 32 && bpp === 8) return 1024;
        if (format === 64 && bpp === 8) return 1024;
        if (format === 128 && bpp === 32) return 1024;
        if (format === 128 && bpp === 8) return 1024;
        // if (format === "80000000" && bpp === 4) return 1024;
        // if (format === "00010000" && bpp === 32) return 1024;
        //
        //
        if (format === 128 && bpp === 4) return 64;
        if (format === 16 && bpp === 4) return 64;
        if (format === 32 && bpp === 4) return 64;
        if (format === 64 && bpp === 4) return 64;
        // if (format === "00010000" && bpp === 4) return 64;
        // if (format === "00020000" && bpp === 8) return 1024;

        console.error("Unknown palette format " + format + " bpp:" + bpp);
        die;
    }
    
    function parseTexture( startOffset, binary ){

        binary.setCurrent(startOffset);
        let texture;

        if (platform === "psp001"){

            texture = {
                'prevOffset'        : binary.consume(4, 'int32'),
                'nextOffset'        : binary.consume(4, 'int32'),
                'name'              : binary.consume(64, 'nbinary').getString(0, false),

                'width'             : binary.consume(4, 'int32'),
                'height'            : binary.consume(4, 'int32'),
                'bitPerPixel'       : binary.consume(4, 'int32'),
                'rasterFormat'      : binary.consume(4, 'int32'),

                'pixelFormat'       : binary.consume(4,  'int32'),
                'mipMapCount'       : binary.consume(1,  'int8'),
                'swizzleMask'       : binary.consume(1,  'int8'),
                'padding'           : binary.consume(2, 'uint16'),

                'dataOffset'        : binary.consume(4, 'int32'),
                'paletteOffset'     : binary.consume(4, 'int32'),

                'palette'           : false
            };

            if (texture.paletteOffset > 0){
                binary.setCurrent(texture.paletteOffset);

                texture.palette = binary.consume(
                    getPaletteSize(texture.rasterFormat, texture.bitPerPixel),
                    'nbinary'
                );
            }

            binary.setCurrent(texture.dataOffset);

            texture.data = binary.consume(
                getRasterSize(texture.rasterFormat, texture.width, texture.height, texture.bitPerPixel),
                'nbinary'
            );

        }else{

            texture = {
                'nextOffset'        : binary.consume(4, 'int32'),
                'prevOffset'        : binary.consume(4, 'int32'),
                'name'              : binary.consume(32, 'nbinary').getString(0, false),
                'alphaFlags'        : binary.consume(32, 'dataview'),
                'width'             : binary.consume(4, 'int32'),
                'height'            : binary.consume(4, 'int32'),
                'bitPerPixel'       : binary.consume(4, 'int32'),
                'pitchOrLinearSize' : binary.consume(4, 'int32'),
                'flags'             : binary.consume(4,  'dataview'),
                'mipMapCount'       : binary.consume(1,  'int8'),
                'unknown'           : binary.consume(3,  'dataview'),
                'dataOffset'        : binary.consume(4, 'int32'),
                'paletteOffset'     : binary.consume(4, 'int32'),
                'size'              : binary.consume(4, 'int32'),
                'unknown2'          : binary.consume(4, 'dataview')
            };


            binary.setCurrent(texture.dataOffset);

            texture.data = binary.consume(texture['size'], 'arraybuffer');

        }


        return texture;
    }


    function unpack(binary){


        var header = {
            'magic'             : binary.consume(4,  'string'),
            'constNumber'       : binary.consume(4, 'int32'),
            'fileSize'          : binary.consume(4, 'int32'),
            'indexTableOffset'  : binary.consume(4, 'int32'),
            'indexTableOffset2' : binary.consume(4, 'int32'),
            'numIndex'          : binary.consume(4, 'int32'),
            'unknown'           : binary.consume(8,  'dataview'),
            'numTextures'       : binary.consume(4, 'int32'),
            'firstOffset'       : binary.consume(4, 'int32'),
            'lastOffset'       : binary.consume(4, 'int32')
        };

        var currentOffset = header.firstOffset;

        var textures = [];
        while(header.numTextures > 0) {
            var texture = parseTexture(currentOffset, binary);

            if (texture.width <= 2 && texture.height <= 2){
                currentOffset = texture.nextOffset;

                header.numTextures--;
                continue;
            }

            textures.push(
                {
                    name: texture.name,
                    data: texture.data,
                }
            );

            currentOffset = texture.nextOffset;
            if (currentOffset === 36) return textures;

            header.numTextures--;
        }
        return textures;
    }

    return unpack(binary);

};
MANHUNT.fileLoader.TEX = function () {

    function parseTexture( startOffset, binary ){

        binary.setCurrent(startOffset);

        var texture = {
            'nextOffset'        : binary.consume(4, 'int32'),
            'prevOffset'        : binary.consume(4, 'int32'),
            'name'              : binary.consume(32, 'arraybuffer'),
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
                currentOffset = texture['nextOffset'];

                header.numTextures--;
                continue;
            }

            var name = new NBinary(texture.name);

            textures.push(
                {
                    name: name.getString(0, false),
                    data: texture.data,
                }
            );

            currentOffset = texture.nextOffset;

            header.numTextures--;
        }

        return textures;
    }

    function Manhunt1(binary) {


        function chunk(binary, result){
            if (binary.remain() === 0) return;

            var id = binary.consume(4, 'int32');
            var size = binary.consume(4, 'int32');
            var version = binary.consume(4, 'int32');
            var data;

            switch (id) {
                //txd_file_t
                case 22:
                    data = new NBinary(binary.consume(size, 'arraybuffer'));
                    return chunk(data,result); //pass the whole file (data)

                case 1:

                    //txd_info_t
                    if (size === 4){
                        data = new NBinary(binary.consume(size, 'arraybuffer'));

                        result.textureCount = data.consume(2, 'uint16');
                        console.log("TEX COUNT", result.textureCount);
                        data.consume(2, 'uint16'); //skip

                        return chunk(binary, result); //reuse given data from case 22

                        //txd_texture_data_t
                    }else{
                        data = binary;


                        var platformId = data.consume(4, 'int32');
                        var textureFormat = data.consume(4, 'int32');

                        console.log("platformId", platformId);
                        console.log("textureFormat", textureFormat);

                        var name = new NBinary(data.consume(32, 'arraybuffer'));
                        name = name.getString(0);
                        var name2 = data.consume(32, 'string');


                        var rasterFormat = data.consume(4, 'uint32');
                        var d3dFormat = data.consume(4, 'uint32');

                        var width = data.consume(2, 'uint16');
                        var height = data.consume(2, 'uint16');

                        var depth = data.consume(1, 'uint8');
                        var numLevels = data.consume(1, 'uint8');
                        var rasterType = data.consume(1, 'uint8');
                        var bitFlag = data.consume(1, 'uint8');

                        var bufferSize = data.consume(4, 'uint32');

                        data.setCurrent(data.current() + 12);
// console.log("d3dFormat", d3dFormat, "depth", depth, "bitFlag", bitFlag, "rasterType", rasterType);

                        var texture = data.consume(bufferSize, 'arraybuffer');

                        var format;
                        switch ( rasterFormat & 0xf00 ) {
                            case 0x100:
                                // blockBytes = 8;
                                format = THREE.RGBA_S3TC_DXT1_Format;
                                // format = THREE.RGBA_S3TC_DXT1_Format;
                                break;
                            case 0x200:
                                format = THREE.RGB_S3TC_DXT1_Format;
                                break;

                            case 0x300:
                                format = THREE.RGBA_S3TC_DXT3_Format;
                                break;

                            default:
                                console.log("decode not dxt", rasterFormat & 0xf00);
                                break;
                        }

                        result.texture.push({
                            compressed: (bitFlag & 0x8) >> 3 !== 0,
                            format: format,
                            name: name,
                            width: width,
                            height: height,
                            data: new Uint8Array( texture )
                        } );

                        result.textureCount--;

                        return;

                    }

                //txd_texture_t
                case 21:
                    data = new NBinary(binary.consume(size, 'arraybuffer'));
                    chunk(data, result); //process texture content

                    return chunk(binary, result);

                //txd_extra_info_t
                case 3:
                    size > 0 && (data = new NBinary(binary.consume(size, 'arraybuffer')));
                    return chunk(binary, result);

            }

            return;
        }

        var result = {
            raw: [],
            texture: []
        };
        chunk(binary, result);

        // var textures = [];
        // for(var i = 0; i < result.texture.length; i++){
        //     var textureWithHeaderChunk = chunk(data);
        //     var textureChunk = chunk(textureWithHeaderChunk);
        //
        //
        // }

        return result.texture;

    }

    var loader = new THREE.FileLoader();
    loader.setResponseType( 'arraybuffer' );

    return {
        load: function (file, callback ) {

            loader.load(
                file,
                function ( data ) {

                    var binary = new NBinary(data);

                    var textures = unpack(binary);

                    // var gameId = binary.consume(4, 'uint32');
                    // binary.setCurrent(0);
                    // var textures;
                    //
                    // if (gameId === 1413759828){ //TCDT => Manhunt 2
                    //     textures = Manhunt2(binary);
                    // }else{
                    //     textures = Manhunt1(binary);
                    // }


                    var ddsLoader = new DDSLoader();
                    var result = [];
                    textures.forEach(function (texture) {

                        var parsed = ddsLoader.parse(texture.data);

                        var realTexture = new THREE.CompressedTexture();
                        realTexture.format = parsed.format;
                        realTexture.mipmaps = parsed.mipmaps;
                        realTexture.image = parsed.mipmaps[0];
                        realTexture.wrapS =  THREE.RepeatWrapping;
                        realTexture.wrapT =  THREE.RepeatWrapping;

                        realTexture.needsUpdate = true;
                        realTexture.name = texture.name;


                        if (parsed.format === THREE.RGBA_S3TC_DXT5_Format){

                            //enable opacity
                            realTexture.magFilter = THREE.LinearFilter;
                            realTexture.minFilter = THREE.LinearFilter;
                        }

                        result.push(realTexture);

                    });

                    callback(result);

                }
            );

        }
    };

};
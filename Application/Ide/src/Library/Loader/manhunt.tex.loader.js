MANHUNT.fileLoader.TEX = function () {

    function Manhunt2(binary){

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

        return unpack(binary);

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
                        data.consume(2, 'uint16'); //skip

                        return chunk(binary, result); //reuse given data from case 22

                    //txd_texture_data_t
                    }else{
                        data = binary;


                        var version = data.consume(4, 'int32');
                        var filter_flags = data.consume(4, 'int32');

                        var name = new NBinary(data.consume(32, 'arraybuffer'));
                        name = name.getString(0);
                        var alpha_name = data.consume(32, 'string');

                        var rasterFormat = data.consume(4, 'uint32');
                        var direct3d_texture_format = data.consume(4, 'uint32');

                        var width = data.consume(2, 'uint16');
                        var height = data.consume(2, 'uint16');

                        var depth = data.consume(1, 'uint8');
                        var mipmap_count = data.consume(1, 'uint8');

                        var texcode_type = data.consume(1, 'uint8');
                        var flags = data.consume(1, 'uint8');

                        // var palette

                        var bufferSize = data.consume(4, 'uint32');

                        var texture = data.consume(bufferSize, 'dataview');

                        var format;


                        switch ( rasterFormat & 0xf00 ) {
                            case 0x100:
                                texture = MANHUNT.converter.dxt.decodeBC1(texture, width, height);
                                format = THREE.RGBFormat;
                                break;
                            case 0x200:
                                format = THREE.RGB_S3TC_DXT1_Format;

                                //TODO: i am not sure why i am not be able to apply the data as THREE DXT Format
                                texture = MANHUNT.converter.dxt.decodeBC1(texture, width, height);
                                format = THREE.RGBAFormat;
                                break;

                            case 0x300:
                                //TODO: i am not sure why i am not be able to apply the data as THREE DXT Format
                                texture = MANHUNT.converter.dxt.decodeBC2(texture, width, height, true);
                                format = THREE.RGBAFormat;

                                break;

                            default:
                                console.log("decode not dxt", rasterFormat & 0xf00);
                                break;
                        }

                        result.texture.push({
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
        }

        var result = {
            raw: [],
            texture: []
        };

        chunk(binary, result);

        return result.texture;

    }

    return {
        load: function (level, file, callback ) {

            MANHUNT.api.load(
                'manhunt2',
                file,
                function ( data ) {

                    var binary = new NBinary(data);
                    var gameId = binary.consume(4, 'uint32');
                    binary.setCurrent(0);
                    var textures;

                    if (gameId === 1413759828){ //TCDT => Manhunt 2
                        textures = Manhunt2(binary);
                    }else{
                        textures = Manhunt1(binary);
                    }

                    var ddsLoader = new DDSLoader();
                    var result = [];
                    textures.forEach(function (texture) {
                        var parsed;
                        var realTexture;

                        if (gameId === 1413759828) { //TCDT => Manhunt 2
                            parsed = ddsLoader.parse(texture.data);
                            realTexture = new THREE.CompressedTexture(parsed.mipmaps, parsed.width, parsed.height);
                            realTexture.format = parsed.format;
                        }else{
                            realTexture = new THREE.DataTexture(texture.data, texture.width, texture.height, texture.format);
                        }

                        if (parsed.format === THREE.RGBA_S3TC_DXT5_Format){
                            realTexture.magFilter = THREE.LinearFilter;
                            realTexture.minFilter = THREE.LinearFilter;
                        }

                        realTexture.wrapS =  THREE.RepeatWrapping;
                        realTexture.wrapT =  THREE.RepeatWrapping;
                        realTexture.needsUpdate = true;
                        realTexture.name = texture.name;

                        result.push(realTexture);

                    });

                    callback(result);

                }
            );

        }
    };

};
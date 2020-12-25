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


    var loader = new THREE.FileLoader();
    loader.setResponseType( 'arraybuffer' );

    return {
        load: function (file, callback ) {

            loader.load(
                file,
                function ( data ) {

                    var binary = new NBinary(data);
                    var textures = unpack(binary);

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
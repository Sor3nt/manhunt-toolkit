MANHUNT.parser.txd = function (binary) {

    // let textures2 = Renderware.getTextures(binary);
    //
    // return textures2;


    function ReadChunk() {
        return {
            id: binary.consume(4, 'int32'),
            size: binary.consume(4, 'uint32'),
            version: binary.consume(4, 'uint32')
        };
    }

    let textures = [];
    let textureCount = false;

    while(binary.remain() > 0){
        let header = ReadChunk();

        if (header.version === 268828671 ) {

            if (header.id === 22){
                let txd_info_t = ReadChunk();
                if (txd_info_t.id === 1){
                    textureCount = binary.consume(2, 'int16');
                    binary.consume(2, 'int16'); // unk
                }
            }else if (header.id === 21){
                let entry = {
                    rasterFormat: 512 //TODO
                };

                let platformHeader = ReadChunk();
                let platform = binary.consume(platformHeader.size, 'arraybuffer');

                let nameHeader = ReadChunk();
                let name = binary.consume(nameHeader.size, 'nbinary');
                entry.name = name.getString(0);

                let blockHeader = ReadChunk();
                let blockData = binary.consume(blockHeader.size, 'nbinary');

                let dataHeader = ReadChunk();
                entry.data = binary.consume(dataHeader.size, 'dataview');

                textures.push(entry);

            }else if (header.id === 3){

                if (header.size > 0){
                    let extraHeader = ReadChunk();

                    if (extraHeader.id === 272){
                        binary.consume(extraHeader.size, 'dataview');
                    }
                }

            }else{
                console.error('[MANHUNT.parser.txd] unknown chunkId ', header.id);
            }
        }else if (header.version === 201523199 ){

            if (header.id === 22){
                let txd_info_t = ReadChunk();
                if (txd_info_t.id === 1){
                    textureCount = binary.consume(2, 'int16');
                    binary.consume(2, 'int16'); // unk
                }
            }else if (header.id === 21){
                let entry = {
                    rasterFormat: 512 //TODO
                };

                let platformHeader = ReadChunk();
                let platform = binary.consume(platformHeader.size, 'arraybuffer');

                let nameHeader = ReadChunk();
                let name = binary.consume(nameHeader.size, 'nbinary');
                entry.name = name.getString(0);

                ReadChunk(); // sizeHeader
                binary.consume(2, 'int16'); // sectionSize
                binary.consume(2, 'int16'); // dataSize

                let dataHeader = ReadChunk();
                entry.data = binary.consume(dataHeader.size, 'dataview');

                textures.push(entry);

            }else if (header.id === 3){

                if (header.size > 0){
                    let extraHeader = ReadChunk();

                    if (extraHeader.id === 272){
                        binary.consume(extraHeader.size, 'dataview');
                    }
                }

            }else{
                console.error('[MANHUNT.parser.txd] unknown chunkId ', header.id);
            }
        }else if (header.version === 402915327 ){

            if (header.id === 22){
                let txd_info_t = ReadChunk();
                if (txd_info_t.id === 1){
                    textureCount = binary.consume(2, 'int16');
                    binary.consume(2, 'int16'); // unk
                }
            }else if (header.id === 21){
                let txd_texture_data_t = ReadChunk();
                if (txd_texture_data_t.id === 1){

                    let entry = {};

                    if (textureCount === false){
                        textureCount = 1;
                        let platform = binary.consume(txd_texture_data_t.size, 'int8');
                        txd_texture_data_t = ReadChunk();
                    }else{
                        entry.version = binary.consume(4, 'int32');
                        entry.filterFlags = binary.consume(4, 'int32');
                    }

                    let name = binary.consume(32, 'nbinary');
                    entry.name = name.getString(0);

                    let alpha_name = binary.consume(32, 'nbinary');
                    entry.alphaName = alpha_name.getString(0);

                    entry.rasterFormat = binary.consume(4, 'uint32');
                    entry.d3dTextureFormat = binary.consume(4, 'uint32');

                    entry.width = binary.consume(2, 'uint16');
                    entry.height = binary.consume(2, 'uint16');

                    entry.depth = binary.consume(1, 'uint8');
                    entry.mipmap_count = binary.consume(1, 'uint8');

                    entry.texcode_type = binary.consume(1, 'uint8');
                    entry.flags = binary.consume(1, 'uint8');

                    // var palette

                    var bufferSize = binary.consume(4, 'uint32');
                    entry.data = binary.consume(bufferSize, 'dataview');

                    textures.push(entry);
                }
            }else if (header.id === 3){
                binary.consume(header.size, 'dataview'); //extra
            }else{
                console.error('[MANHUNT.parser.txd] unknown chunkId ', header.id);
            }
        }else{


            binary.seek(-12);
            let parsed = Renderware.parse(binary);
            console.log(parsed);
            die;

            console.error('[MANHUNT.parser.txd] unknown version ', header.version);
        }
    }

    if (textures.length !== textureCount){
        console.warn('[MANHUNT.parser.txd] Texture count did not match with parsed data!');
    }

    var threeTextures = [];
    textures.forEach(function (texture) {
        let rgba;

        switch ( texture.rasterFormat & 0xf00 ) {
            case 0x100:
                rgba = MANHUNT.converter.dxt.decodeBC1(texture.data, texture.width, texture.height);
                format = THREE.RGBFormat;
                break;
            case 0x200:
                format = THREE.RGB_S3TC_DXT1_Format;

                //TODO: i am not sure why i am not be able to apply the data as THREE DXT Format
                rgba = MANHUNT.converter.dxt.decodeBC1(texture.data, texture.width, texture.height);
                format = THREE.RGBAFormat;
                break;

            case 0x300:
                //TODO: i am not sure why i am not be able to apply the data as THREE DXT Format
                rgba = MANHUNT.converter.dxt.decodeBC2(texture.data, texture.width, texture.height, true);
                format = THREE.RGBAFormat;

                break;

            default:
                console.error("decode not dxt", texture.rasterFormat & 0xf00);
                debugger;
                break;
        }

        threeTextures.push({
            format: format,
            name: texture.name,
            width: texture.width,
            height: texture.height,
            data: new Uint8Array( rgba )
        } );
    });

    return threeTextures;
};
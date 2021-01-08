MANHUNT.parser.txd = function (binary) {


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


};
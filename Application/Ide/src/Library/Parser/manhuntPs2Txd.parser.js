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
                    palette = binary.consume(paletteSize * 4, "dataview");

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

    do{

        let result = readChunk();
        if (result.type === "texture"){
// console.log(result.data);
            textures.push({
                format: THREE.RGBAFormat,
                name: result.name,
                width: result.width,
                height: result.height,
                data: new Uint8Array( result.data )
            } );
        }
        
    }while(binary.remain() > 0);

    return textures;
};
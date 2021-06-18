import Renderware from "./../Renderware.js";
import Helper from './../../../Helper.js'
import Chunk from "./Chunk.js";
const assert = Helper.assert;

export default class TextureNative extends Chunk{

    result = {
        platform: null,
        filterFlags: null,
        name: null,
        alphaName: null,
        rasterFormat: null,
        width: [],
        height: [],
        depth: null,
        rasterType: null,
        dxtCompression: null,
        hasAlpha: null,
        mipmap: [],
        palette: null,
        swizzleMask: null,

        //PC only
        d3dTextureFormat: null,

        //PS2 only
        swizzleWidth: [],
        swizzleHeight: [],

        //Versionn 784
        addrModeU: null,
        addrModeV: null,

        chunks: []
    };

    parse(){
        let struct = this.processChunk(this.binary);
        assert(struct.type, Renderware.CHUNK_STRUCT);

        if (struct.header.version === 784){

            this.result.platform = "pc";
            this.result.filterFlagsUnnk = struct.binary.consume(1, 'uint8');
            this.result.addrModeU = struct.binary.consume(1, 'uint8');
            this.result.addrModeV = struct.binary.consume(1, 'uint8');
            struct.binary.seek(1); //padding

            this.result.filterFlags = struct.binary.consume(4, 'uint32');
            this.result.name = struct.binary.consume(128, 'nbinary').getString(0);
            this.result.alphaName = struct.binary.consume(128, 'nbinary').getString(0);

            this.result.rasterFormat = struct.binary.consume(4, 'uint32');
            this.result.hasAlpha = struct.binary.consume(4, 'uint32');
            this.result.width = [struct.binary.consume(2, 'uint16')];
            this.result.height = [struct.binary.consume(2, 'uint16')];
            this.result.depth = struct.binary.consume(1, 'uint8');
            let mipmapCount = struct.binary.consume(1, 'uint8');
            this.result.rasterType = struct.binary.consume(1, 'uint8');
            assert(this.result.rasterType, 4, "RasterType should be always 4 but it is " + this.result.rasterType);
            this.result.dxtCompression = struct.binary.consume(1, 'uint8');
            //
            // console.log(this.result);
            // die;
            let size = struct.binary.consume(4, 'uint32');
            // let bufferSize = struct.binary.consume(size, 'nbinary');


            let paletteSize =
                (
                    this.result.rasterFormat & Renderware.RASTER_PAL8) ?
                    0x100 : (
                        this.result.rasterFormat & Renderware.RASTER_PAL4 ?
                            0x20 :
                            0
                    )
            ;

            this.result.palette = false;
            if (paletteSize > 0){
                this.result.palette = struct.binary.consume(paletteSize, 'nbinary');
            }

            this.result.mipmap = [];
            for (let i = 0; i < mipmapCount; i++) {
                if (i !== 0) {
                    this.result.width.push(this.result.width[i-1]/2);
                    this.result.height.push(this.result.height[i-1]/2);
                }

                let dataSizes = this.result.width[i] * this.result.height[i];
                if (this.result.dxtCompression === 0)
                    dataSizes *= (this.result.depth/8);
                else if (this.result.dxtCompression === 0xC)
                    dataSizes /= 2;

                this.result.mipmap.push(struct.binary.consume(dataSizes, 'dataview'));

            }

            this.validateParsing(struct);

            let extension = this.processChunk(this.binary);
            assert(extension.type, Renderware.CHUNK_EXTENSION);
            assert(extension.header.size, 0);

            this.validateParsing(this);

            return;
        }


        this.result.platform = struct.binary.consume(4, 'uint32');

        switch (this.result.platform) {
            case Renderware.PLATFORM_OGL:

                console.log("OGL");
                die;
                break;

            case Renderware.PLATFORM_PS2:
            case Renderware.PLATFORM_PS2FOURCC:
                this.parsePs2();
                break;
            case Renderware.PLATFORM_XBOX:
                this.parseXbox(struct);
                break;

            case Renderware.PLATFORM_D3D8:
            case Renderware.PLATFORM_D3D9:
                this.parsePc(struct);

                break;

            default:
                console.error("Platform not supported ", this.result.platform);
                debugger;
                break;
        }


        this.validateParsing(this);
    }

    parsePc(struct){

        this.result.filterFlags = struct.binary.consume(4, 'uint32');
        this.result.name = struct.binary.consume(32, 'nbinary').getString(0);
        this.result.alphaName = struct.binary.consume(32, 'nbinary').getString(0);
        this.result.rasterFormat = struct.binary.consume(4, 'uint32');
        this.result.d3dTextureFormat = struct.binary.consume(4, 'uint32');
        this.result.width = [struct.binary.consume(2, 'uint16')];
        this.result.height = [struct.binary.consume(2, 'uint16')];
        this.result.depth = struct.binary.consume(1, 'uint8');
        let mipmapCount = struct.binary.consume(1, 'uint8');

        this.result.rasterType = struct.binary.consume(1, 'uint8');
        this.result.dxtCompression = struct.binary.consume(1, 'uint8');

        this.result.hasAlpha = false;
        if (this.result.platform === Renderware.PLATFORM_D3D9){
            this.result.hasAlpha = this.result.dxtCompression & 0x1;
        }

        let bufferSize = struct.binary.consume(4, 'uint32');
        assert(bufferSize, struct.binary.remain(), "remained data does not match!");

        this.result.mipmap = [];
        for (let i = 0; i < mipmapCount; i++) {
            if (i !== 0) {
                this.result.width.push(this.result.width[i-1]/2);
                this.result.height.push(this.result.height[i-1]/2);
            }

            let dataSize = (this.result.height[i]*this.result.height[i]) / 2;
            this.result.mipmap.push(struct.binary.consume(dataSize, 'dataview'));

        }

        while(this.binary.remain() > 0){
            this.result.chunks.push(this.processChunk(this.binary));
        }

        this.validateParsing(struct);

    }

    parseXbox(struct){

        this.result.filterFlags = struct.binary.consume(4, 'uint32');

        this.result.name = struct.binary.consume(32, 'nbinary').getString(0);
        this.result.alphaName = struct.binary.consume(32, 'nbinary').getString(0);
        this.result.rasterFormat = struct.binary.consume(4, 'uint32');
        this.result.hasAlpha = struct.binary.consume(4, 'uint32');
        this.result.width.push(struct.binary.consume(2, 'uint16'));
        this.result.height.push(struct.binary.consume(2, 'uint16'));
        this.result.depth = struct.binary.consume(1, 'uint8');
        let mipmapCount = struct.binary.consume(1, 'uint8');
        this.result.rasterType = struct.binary.consume(1, 'uint8');
        assert(this.result.rasterType, 4, "RasterType should be always 4 but it is " + this.result.rasterType);
        this.result.dxtCompression = struct.binary.consume(1, 'uint8');

        let paletteSize =
            (
                this.result.rasterFormat & Renderware.RASTER_PAL8) ?
                0x100 : (
                    this.result.rasterFormat & Renderware.RASTER_PAL4 ?
                        0x20 :
                        0
                )
        ;

        this.result.palette = false;
        if (paletteSize > 0){
            this.result.palette = this.binary.consume(paletteSize, 'nbinary');
        }

        this.result.mipmap = [];
        for (let i = 0; i < mipmapCount; i++) {
            if (i !== 0) {
                this.result.width.push(this.result.width[i-1]/2);
                this.result.height.push(this.result.height[i-1]/2);

                // DXT compression works on 4x4 blocks,
                // no smaller values allowed
                if (this.result.dxtCompression) {
                    if (this.result.width[i] < 4)
                        this.result.width[i] = 4;
                    if (this.result.height[i] < 4)
                        this.result.height[i] = 4;
                }
            }

            let dataSizes = this.result.width[i] * this.result.height[i];
            if (this.result.dxtCompression === 0)
                dataSizes *= (this.result.depth/8);
            else if (this.result.dxtCompression === 0xC)
                dataSizes /= 2;

            this.result.mipmap.push(struct.binary.consume(dataSizes, 'dataview'));

        }

        //unkown 4 bytes at the end of the struct
        //todo guess this is just a bug... i miss somewhere 4bytes -.-
        assert(struct.binary.remain(), 4);
        struct.binary.seek(4);

        assert(struct.binary.remain(), 0, 'CHUNK_TEXTURENATIVE XBOX struct: Unable to parse fully the data! Remain ' + struct.binary.remain());

        let extension = this.processChunk(this.binary);
        assert(extension.type, Renderware.CHUNK_EXTENSION);
        assert(extension.header.size, 0);

        this.validateParsing(struct);

    }

    parsePs2(){
        let name = this.processChunk(this.binary);
        assert(name.type, Renderware.CHUNK_STRING);
        this.result.name = name.data.name;

        let alphaName = this.processChunk(this.binary);
        assert(alphaName.type, Renderware.CHUNK_STRING);
        this.result.alphaName = alphaName.data.name;


        let struct = this.processChunk(this.binary);
        assert(struct.type, Renderware.CHUNK_STRUCT);

        let dataSize;
        {
            let structHeader = this.processChunk(struct);
            assert(structHeader.type, Renderware.CHUNK_STRUCT);

            this.result.width.push(structHeader.binary.consume(4, 'uint32'));
            this.result.height.push(structHeader.binary.consume(4, 'uint32'));
            this.result.depth = structHeader.binary.consume(4, 'uint32');
            this.result.rasterFormat = structHeader.binary.consume(4, 'uint32');

            structHeader.binary.seek(8 * 4);//4*uiTex + 4*miptbp
            dataSize = structHeader.binary.consume(4, 'uint32');
            structHeader.binary.seek(3 * 4);//paletteDataSize, uiGpuDataAlignedSize, uiSkyMipmapVal

            this.validateParsing(structHeader);
        }

        let hasHeader = (this.result.rasterFormat & 0x20000);

        {

            let structBody = this.processChunk(struct);
            assert(structBody.type, Renderware.CHUNK_STRUCT);

            let blockEnd = structBody.binary.current() + dataSize;

            let i = 0;
            while(structBody.binary.current() < blockEnd){

                if (i > 0) {
                    this.result.width.push(this.result.width[i-1]/2);
                    this.result.height.push(this.result.height[i-1]/2);
                }

                let dataSize;

                if (hasHeader){
                    structBody.binary.seek(8 * 4);
                    this.result.swizzleWidth.push(structBody.binary.consume(4, 'uint32'));
                    this.result.swizzleHeight.push(structBody.binary.consume(4, 'uint32'));
                    structBody.binary.seek(6 * 4);
                    dataSize = structBody.binary.consume(4, 'uint32') * 0x10;
                    structBody.binary.seek(3 * 4);
                }else{
                    this.result.swizzleWidth.push(this.result.width[i]);
                    this.result.swizzleHeight.push(this.result.height[i]);
                    dataSize = this.result.height[i]*this.result.height[i]*this.result.depth/8;
                }

                this.result.mipmap.push(structBody.binary.consume(dataSize, 'nbinary'));

                i++;
            }

            let palette = false;
            if (this.result.rasterFormat & 0x2000 || this.result.rasterFormat & 0x4000) {
                let unkh2 = 0;
                let unkh3 = 0;
                let unkh4 = 0;
                if (hasHeader){
                    structBody.binary.seek(8 * 4);
                    unkh2 = structBody.binary.consume(4, 'uint32');
                    unkh3 = structBody.binary.consume(4, 'uint32');
                    structBody.binary.seek(6 * 4);
                    unkh4 = structBody.binary.consume(4, 'uint32');
                    structBody.binary.seek(3 * 4);
                }

                let paletteSize = (this.result.rasterFormat & 0x2000) ? 0x100 : 0x10;
                palette = structBody.binary.consume(paletteSize * 4, "nbinary");

                if (unkh2 === 8 && unkh3 === 3 && unkh4 === 6)
                    structBody.binary.seek(0x20);

            }

            this.result.palette = palette;

            this.validateParsing(structBody);
        }

        this.result.swizzleMask = this.result.swizzleHeight[0] !== this.result.height[0] ? 0x1 : 0x00;

    }
}
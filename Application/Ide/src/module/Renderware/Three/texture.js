
import Renderware from "./../Renderware.js";
import Helper from './../../../Helper.js';
const assert = Helper.assert;


export default class NormalizeTexture{

    constructor( tree ){
        assert(tree.type, Renderware.CHUNK_TEXDICTIONARY, "convert: Container is not a Renderware.CHUNK_TEXDICTIONARY it is " + tree.type);
        this.tree = tree;

        this.textures = Renderware.findChunks(this.tree, Renderware.CHUNK_TEXTURENATIVE);
    }



    normalize(){
        let threeTextures = [];
        this.textures.forEach(function (texture) {
            let rgba, format;

            texture = texture.result;

            switch (texture.platform) {
                case Renderware.PLATFORM_PS2:
                case Renderware.PLATFORM_PS2FOURCC:
                    //
                    // threeTextures.push({
                    //     format: THREE.RGBAFormat,
                    //     name: texture.name,
                    //     width: texture.width[0],
                    //     height: texture.height[0],
                    //     data: new Uint8Array( psImage.convertToRgba(result, 'ps2') )
                    // } );

                    break;
                // case Renderware.PLATFORM_XBOX:
                //     this.parseXbox(struct);
                //     break;

                case Renderware.PLATFORM_D3D8:
                case Renderware.PLATFORM_D3D9:

                    switch ( texture.rasterFormat & 0xf00 ) {
                        case Renderware.RASTER_1555:
                            rgba = MANHUNT.converter.dxt.decodeBC1(texture.mipmap[0], texture.width[0], texture.height[0]);
                            format = THREE.RGBFormat;
                            break;
                        case Renderware.RASTER_565:
                            format = THREE.RGB_S3TC_DXT1_Format;

                            //TODO: i am not sure why i am not be able to apply the data as THREE DXT Format
                            rgba = MANHUNT.converter.dxt.decodeBC1(texture.mipmap[0], texture.width[0], texture.height[0]);
                            format = THREE.RGBAFormat;
                            break;

                        case Renderware.RASTER_4444:
                            //TODO: i am not sure why i am not be able to apply the data as THREE DXT Format
                            rgba = MANHUNT.converter.dxt.decodeBC2(texture.mipmap[0], texture.width[0], texture.height[0], true);
                            format = THREE.RGBAFormat;

                            break;
                        case Renderware.RASTER_8888:
                            rgba = texture.mipmap[0].buffer;
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
                        width: texture.width[0],
                        height: texture.height[0],
                        data: new Uint8Array( rgba )
                    } );
                    break;

                default:
                    console.error("Platform not supported ", texture.platform);
                    debugger;
                    break;
            }


        });


        return threeTextures;
    }
}
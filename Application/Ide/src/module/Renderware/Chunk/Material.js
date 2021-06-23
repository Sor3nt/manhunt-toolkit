import Renderware from "./../Renderware.js";
import Chunk from "./Chunk.js";
import Helper from './../../../Helper.js'
const assert = Helper.assert;

export default class Material extends Chunk{

    result = {
        flags: null,
        rgba: null,
        surfaceProp: {
            ambient: null,
            diffuse: null,
            specular: null
        },

        chunks: []
    };

    parse(){

        let struct = this.processChunk(this.binary);
        assert(struct.type, Renderware.CHUNK_STRUCT);

        this.result.flags = struct.binary.consume(4, 'int32');
        this.result.rgba = struct.binary.readColorRGBA();
        struct.binary.seek(4); //unused
        let hasTexture = struct.binary.consume(4, 'int32') !== 0;

        //if version > 0x30400
        this.result.surfaceProp.ambient = struct.binary.consume(4, 'float32');
        this.result.surfaceProp.diffuse = struct.binary.consume(4, 'float32');
        this.result.surfaceProp.specular = struct.binary.consume(4, 'float32');
        //}
        this.validateParsing(struct);

        if (hasTexture){
            let texture = this.processChunk(this.binary);
            assert(texture.type, Renderware.CHUNK_TEXTURE);
            this.result.chunks.push(texture);

            if (typeof this.rootData.material === "undefined") this.rootData.material = [];
            this.rootData.material.push(texture.result.chunks[0].result.name);
        }

        let extension = this.processChunk(this.binary);
        assert(extension.type, Renderware.CHUNK_EXTENSION);
        this.result.chunks.push(extension);

        this.validateParsing(this);


        this.rootData.materials.push(this.result);
    }

}
import Renderware from "./../Renderware.js";
import Chunk from "./Chunk.js";
import Helper from './../../../Helper.js'
const assert = Helper.assert;

export default class MatList extends Chunk{

    parse(){

        let struct = this.processChunk(this.binary);
        assert(struct.type, Renderware.CHUNK_STRUCT);

        let materialCount = struct.binary.consume(4, 'int32'); //numMaterials
        struct.binary.seek(materialCount * 4); // constant

        this.validateParsing(struct);

        for (let i = 0; i < materialCount; i++) {
            this.result.chunks.push(this.processChunk(this.binary));
        }

        this.validateParsing(this);
    }

}
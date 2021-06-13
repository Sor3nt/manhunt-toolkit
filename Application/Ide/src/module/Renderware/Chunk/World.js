import Renderware from "./../Renderware.js";
import Chunk from "./Chunk.js";
import Helper from './../../../Helper.js'
const assert = Helper.assert;

export default class World extends Chunk{

    result = {
        faceCount: null,
        vertexCount: null,
        sectors: null,

        chunks: []
    };

    parse(){
        let struct = this.processChunk(this.binary);
        assert(struct.type, Renderware.CHUNK_STRUCT);

        struct.binary.seek(4 * 4);
        this.result.faceCount = struct.binary.consume(4, 'uint32');
        this.result.vertexCount = struct.binary.consume(4, 'uint32');
        struct.binary.seek(4);
        this.result.sectorCount = struct.binary.consume(4, 'uint32');

        struct.binary.seek(32);

        this.validateParsing(struct);

        while(this.binary.remain() > 0){
            this.result.chunks.push(this.processChunk(this.binary));
        }

        this.validateParsing(this);

    }

}
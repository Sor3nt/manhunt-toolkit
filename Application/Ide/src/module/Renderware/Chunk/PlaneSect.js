import Renderware from "./../Renderware.js";
import Chunk from "./Chunk.js";
import Helper from './../../../Helper.js'
const assert = Helper.assert;

export default class PlaneSect extends Chunk{

    parse(){
        let struct = this.processChunk(this.binary);
        assert(struct.type, Renderware.CHUNK_STRUCT);

        this.result.sectorType = struct.binary.consume(4, 'uint32');
        this.result.value = struct.binary.consume(4, 'float32');
        this.result.leftIsWorldSector = struct.binary.consume(4, 'uint32');
        this.result.rightIsWorldSector = struct.binary.consume(4, 'uint32');
        this.result.leftValue = struct.binary.consume(4, 'float32');
        this.result.rightValue = struct.binary.consume(4, 'float32');

        this.validateParsing(struct);

        while(this.binary.remain() > 0) {
            let chunk = this.processChunk(this.binary);
            this.result.chunks.push(chunk);
        }

        this.validateParsing(this);
    }

}
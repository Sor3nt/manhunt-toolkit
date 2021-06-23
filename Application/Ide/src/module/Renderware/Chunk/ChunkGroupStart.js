
import Chunk from "./Chunk.js";
import Renderware from "../Renderware.js";

export default class ChunkGroupStart extends Chunk{

    parse() {
        this.result.flag = this.binary.consume(4, 'uint32');

        let string = this.processChunk(this.binary);
        assert(string.type, Renderware.CHUNK_STRING);

        this.validateParsing(this);

    }

}
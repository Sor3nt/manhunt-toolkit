
import Chunk from "./Chunk.js";
import Renderware from "../Renderware.js";

export default class Image extends Chunk{

    result = {
        width: null,
        height: null,
        depth: null,
        format: null,
        chunks: []
    };

    parse(){

        let struct = this.processChunk(this.binary);
        assert(struct.type, Renderware.CHUNK_STRUCT);

        this.width = struct.binary.consume(4, 'uint32');
        this.height = struct.binary.consume(4, 'uint32');
        this.depth = struct.binary.consume(4, 'uint32');
        this.format = struct.binary.consume(4, 'uint32');

        this.validateParsing(struct);

        this.data = this.binary.consume(this.binary.remain(), 'nbinary');

        this.validateParsing(this);

    }

}
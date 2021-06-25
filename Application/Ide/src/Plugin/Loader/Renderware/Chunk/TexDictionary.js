import Renderware from "./../Renderware.js";
import Chunk from "./Chunk.js";
import Helper from './../../../../Helper.js'
const assert = Helper.assert;

export default class TexDictionary extends Chunk{

    result = {
        textureCount: null,
        chunks: []
    };

    parse(){

        let struct = this.processChunk(this.binary);
        assert(struct.type, Renderware.CHUNK_STRUCT);

        this.result.textureCount = struct.binary.consume(2, 'int16');
        struct.binary.seek(2); // unk
        this.validateParsing(struct);


        for(let i = 0; i < this.result.textureCount; i++){
            let chunk = this.processChunk(this.binary);
            assert(chunk.type, Renderware.CHUNK_TEXTURENATIVE);
            this.result.chunks.push(chunk);
        }

        let extension = this.processChunk(this.binary);
        assert(extension.type, Renderware.CHUNK_EXTENSION);
        this.validateParsing(extension);

        this.validateParsing(this);
    }

}
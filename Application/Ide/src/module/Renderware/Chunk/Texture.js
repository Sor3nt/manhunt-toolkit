import Renderware from "./../Renderware.js";
import Chunk from "./Chunk.js";
import Helper from './../../../Helper.js'
const assert = Helper.assert;

export default class Texture extends Chunk{

    result = {
        filterFlag: null,
        chunks: []
    };

    parse(){
        {
            let struct = this.processChunk(this.binary);
            assert(struct.type, Renderware.CHUNK_STRUCT);

            this.result.filterFlag = struct.binary.consume(2, 'int16');
            struct.binary.consume(2, 'int16'); //unknown
            this.validateParsing(struct);
        }

        let textureName = this.processChunk(this.binary);
        assert(textureName.type, Renderware.CHUNK_STRING);
        this.result.chunks.push(textureName);

        let alphaTextureName = this.processChunk(this.binary);
        assert(alphaTextureName.type, Renderware.CHUNK_STRING);
        this.result.chunks.push(alphaTextureName);

        let extension = this.processChunk(this.binary);
        assert(extension.type, Renderware.CHUNK_EXTENSION);

        //sometimes is this block not correct padded
        //size was corrected while parsing
        if (this.binary.remain() === 4)
            this.binary.seek(4);

        this.validateParsing(this);
    }
}
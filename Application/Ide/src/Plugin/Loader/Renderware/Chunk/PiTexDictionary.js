
import Chunk from "./Chunk.js";

export default class PiTexDictionary extends Chunk{


    parse() {

        let unkShort = this.binary.consumeMulti(2, 2, 'uint16');
        let count = this.binary.consume(4, 'uint32');


        while(this.binary.remain() > 0){
            this.result.chunks.push( this.processChunk(this.binary) );
        }

        this.validateParsing(this);

    }



}
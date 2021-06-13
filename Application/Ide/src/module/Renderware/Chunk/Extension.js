
import Chunk from "./Chunk.js";

export default class Extension extends Chunk{

    parse(){
        while(this.binary.remain() > 0){
            this.result.chunks.push(this.processChunk(this.binary));
        }

        this.validateParsing(this);
    }

}
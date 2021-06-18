
import Chunk from "./Chunk.js";

export default class Extension extends Chunk{

    parse(){

        if(this.header.version === 784)
            return this.binary.seek(this.header.size);

        while(this.binary.remain() > 0){
            this.result.chunks.push(this.processChunk(this.binary));
        }

        this.validateParsing(this);
    }

}

import Chunk from "./Chunk.js";

export default class RwString extends Chunk{

    result = {
        name: null,
        chunks: []
    };

    parse(){
        this.result.name = this.binary.getString(0,true);
        this.validateParsing(this);
    }

}
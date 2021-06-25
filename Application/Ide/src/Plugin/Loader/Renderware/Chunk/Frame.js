import Chunk from "./Chunk.js";

export default class Frame extends Chunk{

    result = {
        name: null,
        chunks: []
    };

    parse(){
        this.result.name = this.binary.getString(0);
        this.validateParsing(this);

        this.rootData.frameNames.push(this.result.name);
    }

}
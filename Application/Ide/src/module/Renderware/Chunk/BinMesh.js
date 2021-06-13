
import Chunk from "./Chunk.js";

export default class BinMesh extends Chunk{

    result = {
        faceType: null,
        faces: [],
        materialIds: [],
        chunks: []
    };

    parse(){
        this.result.faceType = this.binary.consume(4, 'uint32');
        let splitCount = this.binary.consume(4, 'uint32');
        this.binary.seek(4); //FaceCount

        let hasData = this.header.size > 12+splitCount*8;

        for(let i = 0; i < splitCount; i++){
            let splitFaceCount = this.binary.consume(4, 'uint32'); //numIndices
            this.result.materialIds.push(this.binary.consume(4, 'uint32') + 1);

            if (!hasData) continue;

            for (let i = 0; i < splitFaceCount; i++) {
                if (this.rootData.hasNativeGeometry){
                    this.result.faces.push(this.binary.consume(2, 'uint16') + 1);
                }else{
                    this.result.faces.push(this.binary.consume(4, 'uint32') + 1);
                }
            }
        }

        this.validateParsing(this);
    }
}
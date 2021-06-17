
import Chunk from "./Chunk.js";

export default class BinMesh extends Chunk{

    result = {
        faceType: null,
        splitCount: null,
        faceCount: null,
        faces: [],
        materialIds: [],
        splitFaceCount: [],
        chunks: []
    };

    parse(){
        this.result.faceType = this.binary.consume(4, 'uint32');
        this.result.splitCount = this.binary.consume(4, 'uint32');
        this.result.faceCount = this.binary.consume(4, 'uint32');

        let hasData = this.header.size > 12+this.result.splitCount*8;

        for(let i = 0; i < this.result.splitCount; i++){
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

            this.result.splitFaceCount.push(splitFaceCount);
        }

        this.validateParsing(this);
        this.updateRootData()
    }

    updateRootData(){
        this.rootData.binMesh = this.result;
    }
}
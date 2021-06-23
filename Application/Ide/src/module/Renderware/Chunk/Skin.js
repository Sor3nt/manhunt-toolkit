
import Renderware from "./../Renderware.js";
import Chunk from "./Chunk.js";

export default class Skin extends Chunk{
    result = {
        hasSkin: null,
        skinPLG: {
            boneids: [],
            weights: [],
            inverseMatrix: []
        },
        chunks: []
    };
    
    parse(){

        this.result.hasSkin = true;

        if (this.rootData.hasNativeGeometry){
            console.warn("UNTESTED SECTIOIN!! CHUNK_SKIN hasNativeGeometry");

            //todo that is not correct... we never need a -4
            let platform = this.binary.consume(4, 'uint32');
            this.binary.seek(-4);
            if (platform === Renderware.PLATFORM_OGL || platform === Renderware.PLATFORM_PS2 || platform === Renderware.PLATFORM_XBOX){
                this.result.chunks.push(this.processChunk(this.binary));
            }else{
                //unknown native data format
                console.error('CHUNK_SKIN: Unknown Platform !');
                this.binary.consume(this.binary.remain(), 'nbinary');
            }
        }else{

            let boneCount = this.binary.consume(1, 'uint8');
            let usedIdCount = this.binary.consume(1, 'uint8');
            this.binary.seek(2); //maxWeightsPerVertex
            this.binary.seek(usedIdCount);

            for (let i = 0; i < this.rootData.vertexCount; i++) {
                this.result.skinPLG.boneids.push([
                    this.binary.consume(1, 'uint8'),
                    this.binary.consume(1, 'uint8'),
                    this.binary.consume(1, 'uint8'),
                    this.binary.consume(1, 'uint8')
                ]);
            }

            for (let i = 0; i < this.rootData.vertexCount; i++) {
                this.result.skinPLG.weights.push(this.binary.readFloats(4));
            }

            for (let i = 0; i < boneCount; i++) {
                this.result.skinPLG.inverseMatrix.push(this.binary.readFloats(16));
            }

            while(this.binary.remain() > 0){
                this.result.chunks.push(this.processChunk(this.binary));
            }
        }

        this.validateParsing(this);
        this.rootData.skins.push(this.result);
    }

}
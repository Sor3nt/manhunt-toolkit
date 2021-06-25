import Chunk from "./Chunk.js";

export default class HAnim extends Chunk{

    result = {
        boneId: null,
        boneCount: null,
        bones: [],
        chunks: []
    };

    parse(){

        this.binary.seek(4); // hAnimVersion (in all GTAs - 1.0 (0x100))
        this.result.boneId = this.binary.consume(4, 'int32');
        this.result.boneCount = this.binary.consume(4, 'uint32');
        
        if (typeof this.rootData.boneIdArray === "undefined")
            this.rootData.boneIdArray = [];

        this.rootData.boneIdArray.push(this.result.boneId);

        if (this.result.boneCount > 0) {

            /*
                 flags
                     1   POPPARENTMATRIX  - this flag must be set for bones which don't have child bones
                     2   PUSHPARENTMATRIX - this flag must be set for all bones, except those which are the latest in a particular hierarchical level
                     8   unknown flag
              */

            this.binary.seek(4);
            this.binary.seek(4); //keyFrameSize

            if (typeof this.rootData.hAnimBoneArray === "undefined")
                this.rootData.hAnimBoneArray = [];

            for (let i = 0; i < this.result.boneCount; i++) {
                let animBone = {
                    boneId   : this.binary.consume(4, 'int32'),
                    boneIndex: this.binary.consume(4, 'uint32'),
                    boneType : this.binary.consume(4, 'uint32'),
                };

                this.result.bones.push(animBone);
                this.rootData.hAnimBoneArray.push(animBone);
            }
        }

        this.validateParsing(this);
    }

}
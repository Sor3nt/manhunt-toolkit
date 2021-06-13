import Chunk from "./Chunk.js";

export default class ReflectionMat extends Chunk{

    result = {
        environmentMapScaleX: null,
        environmentMapScaleY: null,
        environmentMapOffsetX: null,
        environmentMapOffsetY: null,
        reflectionIntensity: null,
        environmentTexturePtr: null,

        chunks: []

    };

    parse(){

        this.result.environmentMapScaleX  = this.binary.consume(4, 'float32');
        this.result.environmentMapScaleY  = this.binary.consume(4, 'float32');
        this.result.environmentMapOffsetX = this.binary.consume(4, 'float32');
        this.result.environmentMapOffsetY = this.binary.consume(4, 'float32');
        this.result.reflectionIntensity   = this.binary.consume(4, 'float32');
        this.result.environmentTexturePtr = this.binary.consume(4, 'float32');

        this.validateParsing(this);
    }

}
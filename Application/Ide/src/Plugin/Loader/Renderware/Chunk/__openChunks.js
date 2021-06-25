import {
    CHUNK_AUDIOCONTAINER, CHUNK_AUDIODATA, CHUNK_AUDIOHEADER,
    CHUNK_HANIMANIMATION,
    CHUNK_IMAGE,
    CHUNK_PITEXDICTIONARY,
    CHUNK_STRUCT, CHUNK_TOC
} from "../../../Constants";

rwChunks[CHUNK_IMAGE] = function (header, rwData) {

    let struct = rwData.processChunk();
    assert(struct.type, CHUNK_STRUCT);

    rwData.data.head = struct.binary.consume(struct.header.size);
    assert(struct.binary.remain(), 0, 'CHUNK_IMAGE struct: Unable to parse fully the data! Remain ' + struct.binary.remain());


    //rwData.binary = image datta


    return rwData;

};

rwChunks[CHUNK_PITEXDICTIONARY] = function (header, rwData) {

    rwData.data.unkInt16 = [
        rwData.binary.consume(2, 'uint16'),
        rwData.binary.consume(2, 'uint16'),
    ];

    rwData.data.count = rwData.binary.consume(4, 'uint32');


    let image = rwData.processChunk();
    assert(image.type, CHUNK_IMAGE);

    return rwData;
};

//sound related
rwChunks[2050] = function (header, rwData) {

    while(rwData.binary.remain() > 0){
        let chunk = rwData.processChunk();
        //contains chunk_2051 or chunk_2052
        rwData.chunks.push(chunk);
    }

    return rwData;
};

//sound related
rwChunks[2051] = function (header, rwData) {

    rwData.data.unknown = rwData.binary.consume(header.size, 'nbinary');

    assert(rwData.binary.remain(), 0, '2051 struct: Unable to parse fully the data! Remain ' + rwData.binary.remain());

    return rwData;
};

//sound related
rwChunks[2052] = function (header, rwData) {

    rwData.data.unknown = rwData.binary.consume(header.size, 'nbinary');

    assert(rwData.binary.remain(), 0, '2051 struct: Unable to parse fully the data! Remain ' + rwData.binary.remain());

    return rwData;
};

//sound related
rwChunks[2057] = function (header, rwData) {

    let unknownChunk = rwData.processChunk();
    assert(unknownChunk.type, 2058);

    while(rwData.binary.remain() > 0){
        let chunk = rwData.processChunk();
        rwData.chunks.push(chunk);
    }

    return rwData;
};

//sound related
rwChunks[2058] = function (header, rwData) {

    rwData.binary.seek(52);

    rwData.data.name = rwData.binary.consume(16, 'nbinary').getString(0);

    assert(rwData.binary.remain(), 0, '2058 struct: Unable to parse fully the data! Remain ' + rwData.binary.remain());

    return rwData;
};

//sound related
rwChunks[2060] = function (header, rwData) {

    let count = rwData.binary.consume(4, 'uint32');

    for(let i = 0;i < count; i++){
        let unknown2050 = rwData.processChunk();
        assert(unknown2050.type, 2050);
        rwData.chunks.push(unknown2050);
    }

    assert(rwData.binary.remain(), 0, '2060 struct: Unable to parse fully the data! Remain ' + rwData.binary.remain());

    return rwData;
};

rwChunks[CHUNK_HANIMANIMATION] = function (header, rwData) {
    //code based on .version === 469893165
    assert(header.version, 469893165, "Code is only tested on version 469893165!");

    rwData.binary.seek(4); // unknown

    //Size, in bytes, of the interpolated keyframe structure.
    let keyFrameSize = rwData.binary.consume(4, 'uint32');
    assert(keyFrameSize, 2, "KeyFrame size is not 2 ! Todo");

    //Number of keyframes in the animation
    let numFrames = rwData.binary.consume(4, 'uint32');

    //Specifies details about animation - relative translation modes etc.
    rwData.binary.seek(4); // flags

    //Duration of animation in seconds
    rwData.data.duration = rwData.binary.consume(4, 'float32');

    //Pointer to the animation keyframes
    rwData.data.keyframes = rwData.binary.consume(4, 'int32');

    //Pointer to custom data for this animation
    rwData.data.customData = rwData.binary.consume(4, 'int32');

    rwData.binary.seek(3*4);
    rwData.binary.seek(2);

    let frames = {};
    let target = 0;
    for(let i = 0; i < numFrames - 1; i++){
        let entry = {
            boneId: target,
            time : rwData.binary.consume(4, 'float32'),
            matrix: [
                rwData.binary.consume(2, 'uint16') / 2048 / 30,
                rwData.binary.consume(2, 'uint16') / 2048 / 30,
                rwData.binary.consume(2, 'uint16') / 2048 / 30,
                rwData.binary.consume(2, 'uint16') / 2048 / 30,
                rwData.binary.consume(2, 'uint16') / 2048 / 30,
                rwData.binary.consume(2, 'uint16') / 2048 / 30,
                rwData.binary.consume(2, 'uint16') / 2048 / 30,
                rwData.binary.consume(2, 'uint16') / 2048 / 30,
                rwData.binary.consume(2, 'uint16') / 2048 / 30,
            ]
        };

        if (typeof frames[target] === "undefined" )
            frames[target] = [];

        frames[target].push(entry);

        if (target % 36 === 0 && target !== 0)
            target = 0;
        else
            target++;

    }
    rwData.data.frames = frames;
    rwData.data.matrix = rwData.binary.readFloats(6);

    assert(rwData.binary.remain(), 0, 'CHUNK_HANIMANIMATION struct: Unable to parse fully the data! Remain ' + rwData.binary.remain());

    return rwData;
};


rwChunks[CHUNK_AUDIOCONTAINER] = function (header, rwData) {

    let audioHeader = rwData.processChunk();
    assert(audioHeader.type, CHUNK_AUDIOHEADER);
    rwData.data.audioHeader = audioHeader.data;

    let audioData = rwData.processChunk();
    assert(audioData.type, CHUNK_AUDIODATA);
    rwData.data.audioData = audioData.binary;

    assert(rwData.binary.remain(), 0, '264: Unable to parse fully the data!');

    return rwData;
};

rwChunks[264] = function (header, rwData) {

    let struct = rwData.processChunk();
    assert(struct.type, CHUNK_TOC);

    rwData.data.flag = rwData.binary.consume(1, 'uint8');

    assert(rwData.binary.remain(), 0, '264: Unable to parse fully the data!');
    return rwData;
};


rwChunks[CHUNK_AUDIOHEADER] = function (header, rwData) {

    rwData.binary.seek(4); //headerSize
    rwData.binary.seek(28); //unkown
    let segmentCount = rwData.binary.consume(4, 'uint32');
    rwData.binary.seek(4); //unkown
    let numberOfTracks = rwData.binary.consume(4, 'uint32');
    rwData.binary.seek(20); //unkown
    rwData.binary.seek(16); //unkown

    let name = rwData.binary.consume(16, 'nbinary').getString(0);
    // console.log(segmentCount,  numberOfTracks, name);

    // assert(rwData.binary.remain(), 0, 'CHUNK_AUDIOHEADER: Unable to parse fully the data! Remain ' + rwData.binary.remain());

    //unknown data block
    return rwData;
};

rwChunks[524] = function (header, rwData) {
    //unknown data block
    return rwData;
};


rwChunks[CHUNK_AUDIODATA] = function (header, rwData) {

    //todo, huge data block left
    return rwData;
};
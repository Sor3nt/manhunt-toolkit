MANHUNT.fileLoader.IFP = function () {

    function readANPKIndex(binary) {
        var anpk_magic = binary.consume(4, 'int32');
        var numANPK = binary.consume(4, 'int32');
        var ANPK = {
            anpkName: [],
            anpkOffset: [],
            frameTimeCount: [],
        };

        for (var j = 0; j < numANPK; j++) {

            var NAME_magic = binary.consume(4, 'int32');
            var AnimNameLen = binary.consume(4, 'int32');
            var AnimName = binary.consume(AnimNameLen - 1, 'string');
            var pad = binary.consume(1, 'int8');

            ANPK.anpkOffset.push(binary.current());
            ANPK.anpkName.push(AnimName);

            var numBones = binary.consume(4, 'int32');
            var chunkSize = binary.consume(4, 'int32');
            var times = binary.consume(4, 'float32');
            var ANPKType = binary.consume(4, 'string');
            ANPK.frameTimeCount.push(times);

            binary.setCurrent(binary.current() - 4);

            if (ANPKType === "SEQT") {
                binary.setCurrent(binary.current() + (chunkSize + numBones * 13));
            } else if (ANPKType === "SEQU") {
                binary.setCurrent(binary.current() + (chunkSize + numBones * 9));
            }

            var unk = binary.consume(4, 'int32');
            var pecTime = binary.consume(4, 'float32');
            var perEntrySize = binary.consume(4, 'int32');
            var numEntry = binary.consume(4, 'uint32');
            var pecSize = perEntrySize * numEntry;

            binary.setCurrent(binary.current() + pecSize);
        }

        return ANPK
    }

    function readStrmAnimBinIndex(binary) {
        var IFPEntryArray = [];
        var IFPEntryIndexArray = [];
        var i, ANPK, nextoffset;

        var numExec = binary.consume(4, 'uint32');
        var numEnvExec = binary.consume(4, 'uint32');
        for (i = 0; i < numExec; i++) {
            ANPK = {
                anpkName: [],
                anpkOffset: []
            };

            var tempAnpk = [];

            IFPEntryArray.push(
                "Execution" + binary.consume(4, 'uint32')
            );

            var JumpExectuionOffset = binary.consume(4, 'uint32');
            var JumpExectuionSize = binary.consume(4, 'uint32');
            var WhileLevelExecOffset = binary.consume(4, 'uint32');
            var WhileLevelExecSize = binary.consume(4, 'uint32');
            var YellowLevelExecOffset = binary.consume(4, 'uint32');
            var YellowLevelExecSize = binary.consume(4, 'uint32');
            var RedLevelExecOffset = binary.consume(4, 'uint32');
            var RedLevelExecSize = binary.consume(4, 'uint32');
            nextoffset = binary.current();

            if (JumpExectuionOffset > 0) {
                binary.setCurrent(JumpExectuionOffset);
                tempAnpk.push(readANPKIndex(binary));
            }

            if (WhileLevelExecOffset > 0) {
                binary.setCurrent(WhileLevelExecOffset);
                tempAnpk.push(readANPKIndex(binary));
            }

            if (YellowLevelExecOffset > 0) {
                binary.setCurrent(YellowLevelExecOffset);
                tempAnpk.push(readANPKIndex(binary));
            }

            if (RedLevelExecOffset > 0) {
                binary.setCurrent(RedLevelExecOffset);
                tempAnpk.push(readANPKIndex(binary));
            }

            for (var j = 0; j < tempAnpk.length; j++) {
                for (var jj = 0; jj < tempAnpk[j].AnpkName.length; jj++) {
                    ANPK.anpkName.push(tempAnpk[j].AnpkName[jj]);
                    ANPK.anpkOffset.push(tempAnpk[j].AnpkOffset[jj]);
                }
            }

            binary.setCurrent(nextoffset);

            IFPEntryIndexArray.push(ANPK);
        }

        for (i = 0; i < numEnvExec; i++) {

            var ExecutionID = binary.consume(4, 'uint32');
            var EnvExecOffset = binary.consume(4, 'uint32');
            var EnvExecSize = binary.consume(4, 'uint32');
            nextoffset = binary.current();
            IFPEntryArray.push(
                "Environmental Exec" + ExecutionID
            );

            if (EnvExecOffset > 0) {
                binary.setCurrent(EnvExecOffset);
                ANPK = readANPKIndex(binary);
                IFPEntryIndexArray.push(ANPK);

                binary.setCurrent(nextoffset);
            }

        }

        return [IFPEntryArray, IFPEntryIndexArray];
    }

    function getANPKAnim(binary, anpkOffset, groupName, animName) {

        binary.setCurrent(anpkOffset);

        var resultBones = [];

        var numBones = binary.consume(4, 'int32');
        var chunkSize = binary.consume(4, 'int32');
        var times = binary.consume(4, 'float32');

        for (var b = 0; b < numBones; b++) {

            var ANPKType = binary.consume(4, 'string');

            var boneId = binary.consume(2, 'int16');
            var frameType = binary.consume(1, 'int8');
            var frames = binary.consume(2, 'uint16');

            var frameTime = 0.0;
            var startTime = (binary.consume(2, 'int16')) / 2048.0 * 30.0;


            var resultBone = {
                'boneId' : boneId,
                'frameType' : frameType,
                'startTime' : startTime,
                'frames' : [],
                'direction': []
            };


            if (frameType > 2) {
                //rX rY rZ rW quat
                resultBone.direction = [
                    binary.consume(2, 'int16') / 2048.0,
                    binary.consume(2, 'int16') / 2048.0,
                    binary.consume(2, 'int16') / 2048.0,
                    binary.consume(2, 'int16') / 2048.0,
                ];

            }else if(startTime === 0){
                //back to starttime
                binary.setCurrent(binary.current() - 2);
            }

            var resultFrames = { frames: [] };
            var resultFrame;
            for (var i = 0; i < frames; i++) {
                resultFrame = {
                    time: 0,
                    quat: [],
                    position: [],
                };

                var curtime;

                if (startTime === 0) {

                    if (frameType === 3 && i === 0) {
                        curtime = 0.0;
                    } else {
                        curtime = binary.consume(2, 'uint16') / 2048.0 * 30.0;
                    }

                    frameTime += curtime;
                } else {
                    if (startTime < 1) startTime = 1;

                    if ((frames === 0) && (startTime === (times*30))){
                        frameTime = (i) + startTime;
                    }else{
                        frameTime = (i) + startTime - 1
                    }
                }

                resultFrame.time =  frameTime;

                if (frameType < 3) {
                    //rX rY rZ rW quat
                    resultFrame.quat = [
                        binary.consume(2, 'int16') / 4096.0,
                        binary.consume(2, 'int16') / 4096.0,
                        binary.consume(2, 'int16') / 4096.0,
                        binary.consume(2, 'int16') / 4096.0,
                    ];

                }

                if (frameType > 1) {
                    //tX tY tZ
                    resultFrame.position = [
                        binary.consume(2, 'int16') / 2048.0,
                        binary.consume(2, 'int16') / 2048.0,
                        binary.consume(2, 'int16') / 2048.0,
                    ];
                }

                resultFrames.frames.push(resultFrame);
            }

            //fix for three.js, we need the last frame
            if (frameTime < times * 30){
                resultFrames.frames[resultFrames.frames.length - 1].time = times * 30;

                console.log("not at the end", frameTime, times * 30, resultFrame);
            }

            if (ANPKType === "SEQT") {
                resultFrames.lastFrameTime = binary.consume(4, 'float32');
            }

            resultBone.frames.push(resultFrames);
            resultBones.push(resultBone);
        }

        return convertBonesToAnimation(resultBones, animName, times);
    }

    function getBoneNameByBoneId(boneId) {
        var mapping = {
            BONE_JAW: 0,
            BONE_LEFT_BROW: 1,
            BONE_LIP_CORNER_R: 2,
            BONE_LIP_CORNER_L: 3,
            BONE_RIGHT_BROW: 4,
            Bone_Root: 5,
            Bip01: 1000,
            Bip01_Head: 1001,
            Bip01_L_Calf: 1002,
            Bip01_L_Clavicle: 1003,
            Bip01_L_Finger0: 1004,
            Bip01_L_Finger1: 1005,
            Bip01_L_Finger01: 1006,
            Bip01_L_Finger2: 1008,
            Bip01_L_Finger11: 1011,
            Bip01_L_Finger21: 1013,
            Bip01_L_Foot: 1019,
            Bip01_L_Forearm: 1020,
            Bip01_L_Hand: 1021,
            Bip01_L_Thigh: 1023,
            Bip01_L_Toe0: 1024,
            Bip01_L_UpperArm: 1039,
            Bip01_Neck: 1040,
            Bip01_Pelvis: 1045,
            Bip01_R_Calf: 1056,
            Bip01_R_Clavicle: 1057,
            Bip01_R_Finger0: 1058,
            Bip01_R_Finger1: 1059,
            Bip01_R_Finger01: 1060,
            Bip01_R_Finger2: 1062,
            Bip01_R_Finger11: 1065,
            Bip01_R_Finger21: 1067,
            Bip01_R_Foot: 1073,
            Bip01_R_Forearm: 1074,
            Bip01_R_Hand: 1075,
            Bip01_R_Thigh: 1077,
            Bip01_R_Toe0: 1078,
            Bip01_R_UpperArm: 1093,
            Bip01_Spine: 1094,
            Bip01_Spine1: 1095,
            Bip01_Spine2: 1096,
            Back_Weapon_Slot: 3333,
            Left_Weapon_Slot: 4444,
            Right_Weapon_Slot: 5555,
            Lure_Slot: 6666,
            STRAP1: 7777,
            STRAP2: 8888
        };

        for(var i in mapping){
            if (!mapping.hasOwnProperty(i)) continue;
            if (mapping[i] === boneId) return i;
        }

        return boneId;
    }

    function convertBonesToAnimation( bones, animName, duration) {

        var animation = {
            name: animName,
            duration: duration,
            tracks: []
        };

        for(var i in bones){
            if (!bones.hasOwnProperty(i)) continue;

            var bone = bones[i];
            var name = getBoneNameByBoneId(bone.boneId);

            var trackPosition = {
                name: name + '.position',
                times: [],
                values: [],
                type: "vector"
            };

            var trackQuaternion = {
                name: name + '.quaternion',
                times: [],
                values: [],
                type: "quaternion"
            };

            bone.frames[0].frames.forEach(function (frame) {

                if (frame.quat.length > 0){
                    trackQuaternion.times.push(frame.time / 30);
                    trackQuaternion.values.push(
                        frame.quat[0] * -1,
                        frame.quat[1] * -1,
                        frame.quat[2] * -1,
                        frame.quat[3]
                    );

                }
                if (frame.position.length > 0){
                    trackPosition.times.push(frame.time / 30);
                    trackPosition.values.push(
                        frame.position[0],
                        frame.position[1],
                        frame.position[2]
                    );
                }
            });

            if (trackPosition.values.length > 0){
                animation.tracks.push(trackPosition);
            }

            if (trackQuaternion.values.length > 0)
                animation.tracks.push(trackQuaternion);

        }
        return THREE.AnimationClip.parse( animation );
    }

    var loader = new THREE.FileLoader();
    loader.setResponseType('arraybuffer');

    return {
        load: function (file, callback) {

            loader.load(
                file,
                function (data) {

                    var IFPEntryArray = [];
                    var IFPEntryIndexArray = [];

                    var binary = new NBinary(data);

                    var Idstring = binary.consume(4, 'int32');

                    //ifp
                    if (Idstring === 0x54434e41) {
                        var numBlock = binary.consume(4, 'int32');

                        for (var i = 0; i < numBlock; i++) {
                            var BLOC = binary.consume(4, 'int32');
                            var bNameLen = binary.consume(4, 'int32');

                            var blockName = binary.consume(bNameLen - 1, 'string');
                            var pad = binary.consume(1, 'int8');
                            IFPEntryArray.push(blockName);

                            var ANPK = readANPKIndex(binary);
                            IFPEntryIndexArray.push(ANPK);
                        }


                        //strmanim_pc.bin
                    } else if (Idstring === 1) {

                        var result = readStrmAnimBinIndex(binary);
                        IFPEntryArray = result[0];
                        IFPEntryIndexArray = result[1];
                    }

                    callback({
                        groupName: IFPEntryArray,
                        groupEntries: IFPEntryIndexArray,
                        find: function (group, name) {

                            var groupIndex = -1;
                            IFPEntryArray.forEach(function (groupName, index) {
                                if (groupName === group) groupIndex = index;
                            });


                            if (groupIndex === -1){
                                console.log('[MANHUNT.loader.ifp] Unable to locate animation group ', group);
                                return false;
                            }
                            var groupName = IFPEntryArray[groupIndex];

                            var clip = false;
                            IFPEntryIndexArray[groupIndex].anpkName.forEach(function (animName, index) {
                                if (animName === name){
                                    clip = getANPKAnim(binary, IFPEntryIndexArray[groupIndex].anpkOffset[index], groupName, name);
                                }
                            });

                            if (clip === false){
                                console.log('[MANHUNT.loader.ifp] Unable to locate animation clip ', name);
                                return false;
                            }

                            return clip;
                        }
                    });
                }
            );

        }
    };

};
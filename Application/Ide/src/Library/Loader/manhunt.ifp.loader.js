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



            let testVersion = binary.consume(4, 'string');
            binary.setCurrent( binary.current() - 4);

            var times = 10;
            var ANPKType;
            var mh064Patch = false;
            if (testVersion === "SEQT" || testVersion === "SEQU"){
                ANPKType = testVersion;
                mh064Patch = true;
            }else{
                times = binary.consume(4, 'float32');
                ANPKType = binary.consume(4, 'string');
            }

            ANPK.frameTimeCount.push(times);

            binary.setCurrent(binary.current() - 4);

            let patchOffset = 0;
            if (mh064Patch){
                patchOffset = 4; //we have no frameTimeCount field
            }

            if (ANPKType === "SEQT") {
                binary.setCurrent(binary.current() + (chunkSize + numBones * 13) + patchOffset);
            } else if (ANPKType === "SEQU") {
                binary.setCurrent(binary.current() + (chunkSize + numBones * 9) + patchOffset);
            }else{
                console.error("[MANHUNT.ifp.loader] Parsing error, assume SEQT or SEQU got ", ANPKType, binary.current());
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

    function getANPKAnim(convertNames, game, binary, anpkOffset, groupName, animName) {

        binary.setCurrent(anpkOffset);

        var resultBones = [];

        var numBones = binary.consume(4, 'int32');
        var chunkSize = binary.consume(4, 'int32');




        let testVersion = binary.consume(4, 'string');
        binary.setCurrent( binary.current() - 4);

        var times = false;
        if (testVersion === "SEQT" || testVersion === "SEQU"){
        }else{
            times = binary.consume(4, 'float32');
        }

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

            //fix for the ps2 0.64, they dont use a time value
            if (times === false){
                times = resultFrames.frames[resultFrames.frames.length - 1].time / 30;
            }

            //fix for three.js, we need the last frame
            if (frameTime < times * 30){
                resultFrames.frames[resultFrames.frames.length - 1].time = times * 30;
            }

            if (ANPKType === "SEQT") {
                resultFrames.lastFrameTime = binary.consume(4, 'float32');
            }

            resultBone.frames.push(resultFrames);
            resultBones.push(resultBone);
        }

        return convertBonesToAnimation(convertNames, game, resultBones, animName, times);
    }

    function getBoneNameByBoneId(game, boneId) {
        let mappingManhunt = {
            'Bip01': 1000,
            'Bip01 Head': 1001,
            'Bip01 L Calf': 1002,
            'Bip01 L Clavicle': 1003,
            'Bip01 L Finger0': 1004,
            'Bip01 L Finger1': 1005,
            'Bip01 L Finger01': 1006,
            'Bip01 L Finger2': 1008,
            'Bip01 L Finger3': 1009,
            'Bip01 L Finger11': 1011,
            'Bip01 L Finger21': 1013,
            'Bip01 L Finger31': 1015,
            'Bip01 L Foot': 1019,
            'Bip01 L Forearm': 1020,
            'Bip01 L Hand': 1021,
            'Bip01 L Thigh': 1023,
            'Bip01 L Toe0': 1024,
            'Bip01 L UpperArm': 1039,
            'Bip01 Neck': 1040,
            'Bip01 Pelvis': 1045,
            'Bip01 R Calf': 1056,
            'Bip01 R Clavicle': 1057,
            'Bip01 R Finger0': 1058,
            'Bip01 R Finger1': 1059,
            'Bip01 R Finger01': 1060,
            'Bip01 R Finger2': 1062,
            'Bip01 R Finger3': 1063,
            'Bip01 R Finger11': 1065,
            'Bip01 R Finger21': 1067,
            'Bip01 R Finger31': 1069,
            'Bip01 R Foot': 1073,
            'Bip01 R Forearm': 1074,
            'Bip01 R Hand': 1075,
            'Bip01 R Thigh': 1077,
            'Bip01 R Toe0': 1078,
            'Bip01 R UpperArm': 1093,
            'Bip01 Spine': 1094,
            'Bip01 Spine1': 1095,
            'Bip01 Spine2': 1096,
            'Arm Comp Bone 1': 2003,
            'Arm Comp Bone 2': 2222,
            'Back Weapon Slot': 3333,
            'Left Weapon Slot': 4444,
            'Right Weapon Slot': 5555,
            'Lure Slot': 6666,
            'Strap Bone 1': 7777,
            'Strap Bone 2': 8888,
            'Neck Compensator': 9999,
            'Player_Bod': 10000,
            'Bip01 HeadNub': 10001,
            'Neck Compensator Dummy': 10002,
            'Bip01 L Finger0Nub': 10003,
            'Bip01 L Finger1Nub': 10004,
            'Bip01 L Finger2Nub': 10005,
            'Bip01 L Finger3Nub': 10006,
            'Arm Comp Bone 2 Dummy': 10007,
            'Bip01 R Finger0Nub': 10008,
            'Bip01 R Finger1Nub': 10009,
            'Bip01 R Finger2Nub': 10010,
            'Bip01 R Finger3Nub': 10011,
            'Arm Comp Bone 1 Dummy': 10012,
            'Strap Bone 1 Dummy': 10013,
            'Strap Bone 2 Dummy': 10014,
            'Bip01 L Toe0Nub': 10015,
            'Bip01 R Toe0Nub': 10016
        };

        let mappingManhunt2 = {
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

        if (game === "mh2"){
            for(let i in mappingManhunt2){
                if (!mappingManhunt2.hasOwnProperty(i)) continue;
                if (mappingManhunt2[i] === boneId) return i;
            }

        }else{
            for(let i in mappingManhunt){
                if (!mappingManhunt.hasOwnProperty(i)) continue;
                if (mappingManhunt[i] === boneId) return i;
            }

        }

        console.warn("[MANHUNT.fileLoader.IFP] unable to map ", game, "boneId", boneId);

        return boneId;
    }

    function convertBonesToAnimation(convertNames, game, bones, animName, duration) {

        var animation = {
            name: animName,
            duration: duration,
            tracks: []
        };

        for(var i in bones){
            if (!bones.hasOwnProperty(i)) continue;

            var bone = bones[i];
            var name;

            if (convertNames){
                name = getBoneNameByBoneId(game === "mh1" ? "mh2" : "mh1", bone.boneId);

            }else{
                name = getBoneNameByBoneId(game, bone.boneId);

            }


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

            bone.frames[0].frames.forEach(function (frame, i) {
                // if (i > 0) return;

                if (frame.quat.length > 0){
                    trackQuaternion.times.push(frame.time / 30);

                    let vec4 = new THREE.Quaternion(
                        frame.quat[0] * -1,
                        frame.quat[1] * -1,
                        frame.quat[2] * -1,
                        frame.quat[3]
                    );

                    if (convertNames){
                        if (name === "Bip01_Spine"){
                            vec4.multiply(new THREE.Quaternion(-0.500398, -0.500001, 0.4996, 0.500001));

                            trackPosition.times.push(frame.time / 30);
                            trackPosition.values.push(
                               0,0,0
                            );
                        }

                    }

                    trackQuaternion.values.push(
                        vec4.x, vec4.y, vec4.z, vec4.w
                    );
                }

                if (frame.position.length > 0){
                    trackPosition.times.push(frame.time / 30);

                    let vec3 = new THREE.Vector3(
                        frame.position[0],
                        frame.position[1],
                        frame.position[2]
                    );

                    if (convertNames && name === "Bip01_L_Clavicle"){
                        vec3.add(new THREE.Vector3(0.170165,-2.68221e-07,0.0150331));
                    }

                    if (convertNames && name === "Bip01_R_Clavicle"){
                        vec3.add(new THREE.Vector3(0.170165,-2.75671e-07,0.0150331));
                    }

                    trackPosition.values.push(
                        vec3.x, vec3.y, vec3.z
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

    return {
        load: function (level, file, callback) {

            MANHUNT.api.load(
                level._gameId,
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

                        getNamesByGroup: function ( group ) {
                            var groupIndex = -1;
                            IFPEntryArray.forEach(function (groupName, index) {
                                if (groupName === group) groupIndex = index;
                            });

                            if (groupIndex === -1){
                                console.error('[MANHUNT.fileLoader.IFP] unable to find group ', group, 'available', IFPEntryArray);
                            }

                            return IFPEntryIndexArray[groupIndex].anpkName;
                        },

                        find: function (group, name, convert) {

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
                                    clip = getANPKAnim(convert, level._game, binary, IFPEntryIndexArray[groupIndex].anpkOffset[index], groupName, name);
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
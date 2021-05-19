RW.convert.animation = function (tree) {
    assert(tree.type, CHUNK_ANIMANIMATION, "convert: Container is not a CHUNK_ANIMANIMATION it is " + tree.typeName);

    var animation = {
        name: "test",
        duration: tree.data.duration,
        tracks: []
    };

    for(var boneId in tree.data.frames){
        if (!tree.data.frames.hasOwnProperty(boneId)) continue;

        let frame = tree.data.frames[boneId];

        var name;
        // name = getBoneNameByBoneId(game, bone.boneId);

        name = "Bone" + boneId;


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

        frame.forEach(function (frame) {

            trackQuaternion.times.push(frame.time);

            let vec4 = new THREE.Quaternion(
                frame.matrix[0],
                frame.matrix[1],
                frame.matrix[2],
                frame.matrix[3]
            );

            trackQuaternion.values.push(
                vec4.x, vec4.y, vec4.z, vec4.w
            );

            trackPosition.times.push(frame.time);

            let vec3 = new THREE.Vector3(
                frame.matrix[4],
                frame.matrix[5],
                frame.matrix[6],
            );

            trackPosition.values.push(
                vec3.x, vec3.y, vec3.z
            );
        });

        if (trackPosition.values.length > 0){
            animation.tracks.push(trackPosition);
        }

        if (trackQuaternion.values.length > 0)
            animation.tracks.push(trackQuaternion);

    }

    return THREE.AnimationClip.parse( animation );


};

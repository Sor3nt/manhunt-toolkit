MANHUNT.parser.dff = function (binary) {
    var modelName;

    function cClump(){
        return {
            id: binary.consume(4, 'int32'),
            size: binary.consume(4, 'uint32'),
            version: binary.consume(4, 'uint32')
        };
    }


    function rHAnimPLG() {

        var boneDataAry = [];
        if(binary.consume(4, 'int32') !== 256)
            return console.log('[ManhuntDff] rHAnimPLG, assume 256.');

        var frameBoneId = binary.consume(4, 'int32');
        var boneCount = binary.consume(4, 'uint32');

        if (frameBoneId === -1) return false;
        if (boneCount === 0) return [frameBoneId];

        binary.seek(8);

        for(var i = 0; i < boneCount; i++){
            boneDataAry.push({
                boneId: binary.consume(4, 'uint32'),
                boneIndex: binary.consume(4, 'uint32'),
                boneType: binary.consume(4, 'uint32'),
            });
        }

        return [frameBoneId, boneDataAry];

    }

    function rFrameList() {
        var clump = cClump();
        if (clump.id !== 14)
            return console.log('[ManhuntDff] frame list data, assume 14.');

        clump = cClump();
        if (clump.id !== 1)
            return console.log('[ManhuntDff] frame list data, assume 1.');

        var frameCount = binary.consume(4, 'int32');

        var i, frameAry = [];

        for(i = 0; i < frameCount; i++){
            frameAry.push({
                matrix: [
                    binary.consume(4, 'float32'), binary.consume(4, 'float32'), binary.consume(4, 'float32'), 0,
                    binary.consume(4, 'float32'), binary.consume(4, 'float32'), binary.consume(4, 'float32'), 0,
                    binary.consume(4, 'float32'), binary.consume(4, 'float32'), binary.consume(4, 'float32'), 0,
                    binary.consume(4, 'float32'), binary.consume(4, 'float32'), binary.consume(4, 'float32'), 1
                ],
                parentId: binary.consume(4, 'int32') + 1,
                unk: binary.consume(4, 'uint32')
            });
        }

        var boneInfos;
        for(i = 0; i < frameCount; i++){
            clump = cClump();
            if (clump.id !== 3)
                return console.log('[ManhuntDff] frame list ext data, assume 3. got',clump.id);

            if (clump.size !== 0){
                var loopEnd = binary.current() + clump.size;

                while(binary.current() < loopEnd){
                    clump = cClump();
                    switch(clump.id){

                        case 39056126:
                            frameAry[i].name = binary.consume(clump.size, 'nbinary').getString(0);
                            break;

                        case 286:
                            var res = rHAnimPLG();
                            if (res !== false){
                                frameAry[i].boneId = res[0];
                                if (res.length === 2){
                                    boneInfos = res[1];
                                }
                            }

                            break;

                        default:
                            console.log('[ManhuntDff] frame list ext data, skip, unknown section. len', stringLength);
                            binary.seek(stringLength);
                            break;
                    }

                }

            }else{
                if (clump.version === 0x1803FFFF){
                    frameAry[i].name = "Skin_Mesh";
                }

            }

        }

        frameAry.forEach(function (frameEntry, frameIndex) {
            if (typeof frameEntry.boneId === "undefined") return;

            boneInfos.forEach(function (info, index) {

                if (info.boneId !== frameEntry.boneId) return;

                frameAry[frameIndex].boneIndex = info.boneIndex;
                frameAry[frameIndex].boneType = info.boneType;

            });
        });

        return frameAry;

    }

    function getGeometryCount() {
        var clump = cClump();
        if (clump.id !== 26) return console.log('[ManhuntDff] geometry count, assume 26.');

        clump = cClump();
        if (clump.id !== 1) return console.log('[ManhuntDff] geometry count, assume 1.');

        return binary.consume(4, 'int32');
    }

    function rMaterialList() {

        var clump = cClump();
        if (clump.id !== 8) return console.log('[ManhuntDff] material, assume 8.');

        clump = cClump();
        if (clump.id !== 1) return console.log('[ManhuntDff] material, assume 1.');

        var materialCount = binary.consume(4, 'int32');

        for(var i = 0; i < materialCount; i++){
            binary.consume(4, 'int32');
        }

        var list = [];
        for(i = 0; i < materialCount; i++){
            list.push(rMaterial());
        }

        return list;

    }

    function rMaterial() {
        var result = {};
        var clump = cClump();
        if (clump.id !== 7) return console.log('[ManhuntDff] material, assume 7.');

        clump = cClump();
        if (clump.id !== 1) return console.log('[ManhuntDff] material, assume 1.');

        var unk = binary.consume(4, 'int32');

        result.color = binary.readColorRGBA();
        unk = binary.consume(4, 'int32');

        var textureCount = binary.consume(4, 'int32');
        result.light = {
            ambient: binary.consume(4, 'float32'),
            diffuse: binary.consume(4, 'float32'),
            specular: binary.consume(4, 'float32')
        };

        result.textures = [];
        for(var i = 0; i < textureCount; i++){
            result.textures.push(getTextureName());
        }

        clump = cClump();
        if (clump.id !== 3) return console.log('[ManhuntDff] material data, assume 3.');

        binary.seek(clump.size);

        return result;

    }

    function getTextureName() {
        var clump = cClump();
        if (clump.id !== 6) return console.log('[ManhuntDff] getTextureName data, assume 6.');

        clump = cClump();
        if (clump.id !== 1) return console.log('[ManhuntDff] getTextureName data, assume 1.');

        var TexFlag = binary.consume(4, 'int32');

        clump = cClump();
        if (clump.id !== 2) return console.log('[ManhuntDff] getTextureName data, assume 2.');

        var texName = binary.consume(clump.size, 'nbinary').getString(0);
        clump = cClump();
        if (clump.id !== 2) return console.log('[ManhuntDff] getTextureName data, assume 2.');

        var maskName = binary.consume(clump.size, 'string');

        clump = cClump();
        if (clump.id !== 3) return console.log('[ManhuntDff] getTextureName data, assume 3.');

        binary.seek(clump.size);

        return texName;
    }

    function rGeometry() {

        var result = { light: false };

        var clump = cClump();
        if (clump.id !== 15) return console.log('[ManhuntDff] geometry data, assume 15.');

        clump = cClump();
        if (clump.id !== 1) return console.log('[ManhuntDff] geometry data, assume 1.');

        var GeometryFlags = binary.consume(1, 'uint8');
        var unk = binary.consume(1, 'int8');
        var t2count = binary.consume(2, 'int16');

        var faceCount = binary.consume(4, 'uint32');
        var vertCount = binary.consume(4, 'uint32');
        var mtCount = binary.consume(4, 'uint32');

        if (clump.version === 0x1003FFFF || clump.version === 0x1803FFFF) {
        }else{
            result.light = {
                ambient: binary.consume(4, 'float32'),
                diffuse: binary.consume(4, 'float32'),
                specular: binary.consume(4, 'float32')
            };
        }

        result.cpvArray = [];
        if (GeometryFlags % 16 >= 8){
            for (var i = 0; i < vertCount; i++){
                result.cpvArray.push(binary.readColorRGBA());
            }
        }

        result.uvArray = [];
        if (t2count > 0 || GeometryFlags % 8 >= 4){
            for(i = 0; i < vertCount; i++){
                result.uvArray.push([
                    binary.consume(4, 'float32'),
                    binary.consume(4, 'float32')
                ]);
            }
        }

        result.uv2Array = [];
        if (t2count > 1){
            for(i = 0; i < vertCount; i++){
                result.uv2Array.push([
                    binary.consume(4, 'float32'),
                    binary.consume(4, 'float32')
                ]);
            }
        }

        //more UV maps....
        if (t2count > 2){
            for(i = 2; i < t2count; i++){
                for(i = 0; i < vertCount; i++){
                    binary.consume(4, 'float32');
                    binary.consume(4, 'float32');
                }
            }
        }


        result.faces = [];
        for(i = 0; i < faceCount; i++){
            var a2 = binary.consume(2, 'uint16');
            var a1 = binary.consume(2, 'uint16');
            var fg = binary.consume(2, 'uint16');
            var a3 = binary.consume(2, 'uint16');

            result.faces.push([a1,a2,a3])
        }

        result.bbox = {
            bounding: binary.readVector3(),
            radius: binary.consume(4, 'float32'),
            unk: [binary.consume(4, 'float32'),binary.consume(4, 'float32')]
        };

        result.vertices = [];
        for(i = 0; i < vertCount; i++){
            var vec3 = binary.readVector3();
            result.vertices.push(vec3);
        }

        result.normals = [];
        if (GeometryFlags % 32 >= 16){
            for (i = 0; i < vertCount; i++){
                result.normals.push(binary.readVector3());
            }
        }

        result.material = rMaterialList();

        if (binary.consume(4, 'int32') !== 3){
            return console.log('[ManhuntDff] material data, assume 3.');
        }

        //TODO
        var mExt = binary.consume(4, 'int32');
        ver = binary.consume(4, 'int32');
        binary.seek(mExt);

        return result;
    }

    var results = [];
    do{
        var result = {};
        var cur = binary.current();
        var objectClump = cClump();

        binary.seek(4);
        var dataLength = binary.consume(4, 'int32') / 4;
        binary.seek(4);

        var objectCount = binary.consume(4, 'int32');
        if (dataLength > 1) binary.seek(4 * (dataLength - 1));

        result.bones = rFrameList();

        //todo...
        if (result.bones[0].name === "Skin_Mesh"){
            result.name = result.bones[1].name;
        }else{
            result.name = result.bones[0].name;
        }

        var numGeo = getGeometryCount();

        result.geometry = [];
        for(var i = 0; i < numGeo; i++){
            result.geometry.push(rGeometry());
        }

        binary.setCurrent(cur + objectClump.size + 12);

        results.push(result);
    }while(binary.remain() > 0);

    return results;

};
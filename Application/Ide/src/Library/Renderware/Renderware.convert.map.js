RW.convert.map = function (tree, level) {
    console.log(tree);
    assert(tree.type, CHUNK_WORLD, "convert: Container is not a CHUNK_WORLD it is " + tree.typeName);

    function getChunk(rwData, type) {
        let found = getChunks(rwData, type);
        if (found === false) return false;

        if (found.length > 1){
            console.error("Tried to get exact one chunk with type", CHUNK_ID_NAME[type], 'but multiple found!');
            die;
        }

        return found[0] || false;
    }

    function getChunks(rwData, type) {
        let found = [];
        rwData.chunks.forEach(function (chunk) {
            if (chunk.type === type) found.push(chunk);
        });

        return found;
    }


    function getMaterialList( chunk ) {
        let chunkMaterialList = getChunk(chunk, CHUNK_MATLIST);
        let chunksMaterial = getChunks(chunkMaterialList, CHUNK_MATERIAL);

        let materials = [];
        chunksMaterial.forEach(function (material) {

            let _material = {
                diffuse: material.data.RGBA
            };

            let chunkTexture = getChunk(material, CHUNK_TEXTURE);
            if (chunkTexture !== false){
                assert(chunkTexture.type, CHUNK_TEXTURE);
                let chunksString = getChunks(chunkTexture, CHUNK_STRING);

                _material.TextureName = chunksString[0].data.name;
                if (chunksString[0].data.name)
                    _material.opacitymap = chunksString[1].data.name;
            }

            if (typeof _material.TextureName === "undefined"){
                materials.push(new THREE.MeshBasicMaterial({
                    transparent: false, //todo
                    vertexColors: THREE.VertexColors
                }));

            }else{
                var texture = level._storage.tex.find(_material.TextureName);
                var trans = false;
                if (texture.format === THREE.RGBAFormat) trans = true;

                materials.push(new THREE.MeshBasicMaterial({
                    // shading: THREE.SmoothShading,
                    map: texture,
                    transparent: false, //todo
                    vertexColors: THREE.VertexColors
                }));

            }

        });

        return materials;
    }
    function getGeometryValues( chunk ) {

        let result = [];
        chunk.chunks.forEach(function (_chunk) {
            if (_chunk.type === CHUNK_PLANESECT){
                let _val = getGeometryValues(_chunk);
                _val.forEach(function (val) {
                    if (typeof val.vertex !== "undefined")
                        result.push(val);
                });
            } else if (typeof _chunk.data.vertex !== "undefined"){
                result.push(_chunk.data);
            }

        });

        return result;
    }

    var rootMesh = new THREE.Mesh();
    let materialList = getMaterialList(tree);
    let geometryValues = getGeometryValues(tree);

    geometryValues.forEach(function (geometryValue) {
        var geometry = new THREE.Geometry();
        geometry.faces = geometryValue.faces;
        geometry.vertices = geometryValue.vertex;
        geometry.faceVertexUvs = [geometryValue.uvForFaces];
        geometry.uvsNeedUpdate = true;

        var section = new THREE.Mesh(geometry, materialList);

        rootMesh.children.push(section);
    });

    return rootMesh;

};


MANHUNT.converter.generic2mesh = function (level, model) {

    let group = new THREE.Group();
    group.userData.LODIndex = 0;
    group.name = model.name;

    model.objects.forEach(function (entry, index) {

        let geometry = new THREE.Geometry();

        geometry.faceVertexUvs = entry.faceVertexUvs;

        entry.material.forEach(function (material) {
            material.map = level._storage.tex.find(material.name);
            material.transparent = material.map.format === THREE.RGBA_S3TC_DXT5_Format;
            material.needsUpdate = true;
        });

        if (typeof entry.meshBone !== "undefined"){
            entry.vertices.forEach(function (vertex) {
                let vec = new THREE.Vector3( vertex.x, vertex.y, vertex.z );
                if (level._platform === "pc"){
                    vec = vec.applyMatrix4(entry.meshBone.matrixWorld);
                }
                geometry.vertices.push(
                    vec
                );
            });
        }else{
            geometry.vertices = entry.vertices;
        }

        geometry.faces = entry.faces;
        geometry.skinIndices = entry.skinIndices;
        geometry.skinWeights = entry.skinWeights;

        let bufferGeometry = new THREE.BufferGeometry();
        bufferGeometry.fromGeometry( geometry );


        let mesh = entry.skinning === true ?
            new THREE.SkinnedMesh(bufferGeometry, entry.material) :
            new THREE.Mesh(bufferGeometry, entry.material)
        ;

        //only the first LOD is visible (does not apply to player model)
        mesh.visible = index === 0;
        if (index === 0 && entry.skinning === true){
            let skeleton = model.skeleton.clone();
            mesh.add(skeleton.bones[0]);
            mesh.bind(skeleton);
        }

        group.add(mesh);
    });

    return group;

};

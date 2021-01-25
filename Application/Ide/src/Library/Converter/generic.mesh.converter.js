
MANHUNT.converter.generic2mesh = function (level) {

    let self = {

        convert: function(model){

            let group = new THREE.Group();
            group.userData.LODIndex = 0;
            group.name = model.name;

            model.objects.forEach(function (entry, index) {
    
                let geometry = new THREE.Geometry();
                geometry.colorsNeedUpdate = true;
                geometry.normalsNeedUpdate = true;
    
                geometry.faceVertexUvs = entry.faceVertexUvs;

                entry.material.forEach(function (material) {
                    material.map = level._storage.tex.find(material.name);
                    material.transparent = material.map.format === THREE.RGBA_S3TC_DXT5_Format;
                    material.needsUpdate = true;
                });

                geometry.vertices = entry.vertices;
                geometry.faces = entry.faces;
                geometry.skinIndices = entry.skinIndices;
                geometry.skinWeights = entry.skinWeights;

                let bufferGeometry = new THREE.BufferGeometry();
                bufferGeometry.fromGeometry( geometry );
    
                bufferGeometry.colorsNeedUpdate = true;
                bufferGeometry.computeBoundingSphere();
                
                let mesh = entry.skinning === true ?
                    new THREE.SkinnedMesh(bufferGeometry, entry.material) :
                    new THREE.Mesh(bufferGeometry, entry.material)
                ;

                mesh.visible = index === 0;
                if (index === 0 && entry.skinning === true) {
                    mesh.add(model.skeleton.bones[0]);
                    mesh.bind(model.skeleton);
                }

                group.add(mesh);
            });

            return group;
        }

    };


    return {
        convert: self.convert
    };
};

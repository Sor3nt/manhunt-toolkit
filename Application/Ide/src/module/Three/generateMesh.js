// import {
//     Geometry,
//     Group,
//     RGBA_S3TC_DXT5_Format,
//     Vector3,
//     BufferGeometry,
//     SkinnedMesh,
//     Mesh,
//     MeshBasicMaterial,
//     VertexColors
// } from 'three';

/**
 *
 * @param storageTexture {Default}
 * @param generic
 * @returns {Group}
 */
export default function generateMesh(storageTexture, generic){

    let group = new THREE.Group();
    group.userData.LODIndex = 0;
    group.name = generic.name;

    generic.objects.forEach(function (entry, index) {

        let geometry = new THREE.Geometry();
        let material = [];

        geometry.faceVertexUvs = entry.faceVertexUvs;

        entry.material.forEach(function (name) {
            material.push(new THREE.MeshBasicMaterial({
                // shading: THREE.SmoothShading,
                map: storageTexture.find(name),
                transparent: false, //todo
                vertexColors: THREE.VertexColors
            }));
         });

        if (typeof entry.meshBone !== "undefined"){
            entry.vertices.forEach(function (vertex) {
                let vec = new THREE.Vector3( vertex.x, vertex.y, vertex.z );

                //move matrix apply to parsing....
                // if (level.getPlatform() === "pc")
                    vec = vec.applyMatrix4(entry.meshBone.matrixWorld);

                geometry.vertices.push(vec);
            });
        }else{
            geometry.vertices = entry.vertices;
        }

        geometry.faces = entry.faces;
        if (typeof entry.skinIndices === "object")
            geometry.skinIndices = entry.skinIndices;

        if (typeof entry.skinWeights === "object")
            geometry.skinWeights = entry.skinWeights;

        let bufferGeometry = new THREE.BufferGeometry();
        bufferGeometry.fromGeometry( geometry );

        let mesh = entry.skinning === true ?
            new THREE.SkinnedMesh(bufferGeometry, material) :
            new THREE.Mesh(bufferGeometry, material)
        ;

        //only the first LOD is visible (does not apply to player generic)
        mesh.visible = index === 0;

        if (index === 0 && entry.skinning === true && typeof generic.skeleton !== "undefined"){
            let skeleton = generic.skeleton.clone();
            mesh.add(skeleton.bones[0]);
            mesh.bind(skeleton);
        }

        group.add(mesh);
    });

    return group;
}
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
 * @returns {THREE.Group}
 */
export default function generateMesh(storageTexture, generic){

    function generateMaterial(material, skinning){
        if (typeof material === "undefined" || material.length === 0) return [];

        let result = [];

        material.forEach(function (name) {

            if (typeof name === "undefined" || name === null){
                result.push(new THREE.MeshBasicMaterial({
                    transparent: false, //todo
                    vertexColors: THREE.VertexColors
                }));
                return;
            }

            result.push(new THREE.MeshBasicMaterial({
                // wireframe: true,
                map: storageTexture.find(name),
                skinning: skinning,
                transparent: false, //todo
                vertexColors: THREE.VertexColors
            }));
        });

        return result;
    }

    let group = new THREE.Group();
    group.userData.LODIndex = 0;
    group.name = generic.name;

    let material = [];
    if (typeof generic.objects[0].material === "undefined")
        material = generateMaterial(generic.material, generic.skinning || false);

    generic.objects.forEach(function (entry, index) {

        if (typeof entry.material !== "undefined")
            material = generateMaterial(entry.material, entry.skinning || false);


        let geometry = new THREE.Geometry();

        geometry.faceVertexUvs = entry.faceVertexUvs;
        geometry.faces = entry.faces;


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

        //only the first LOD is visible (does not apply to player or map)
        // mesh.visible = index === 0;

        if (index === 0 && entry.skinning === true && typeof generic.skeleton !== "undefined"){
            let skeleton = generic.skeleton.clone();
            mesh.add(skeleton.bones[0]);
            mesh.bind(skeleton);
        }

        group.add(mesh);
    });
    console.log(group);

    return group;
}
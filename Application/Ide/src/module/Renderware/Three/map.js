
import Renderware from "./../Renderware.js";
import Helper from './../../../Helper.js'
const assert = Helper.assert;


export default class NormalizeMap{

    constructor( tree ){
        assert(tree.type, Renderware.CHUNK_WORLD, "convert: Container is not a Renderware.CHUNK_WORLD it is " + tree.type);
        this.tree = tree;
    }

    getMaterialList( level ) {
        let chunkMaterialList = Renderware.findChunk(this.tree, Renderware.CHUNK_MATLIST);
        let chunksMaterial = Renderware.findChunks(chunkMaterialList, Renderware.CHUNK_MATERIAL);

        let materials = [];
        chunksMaterial.forEach(function (material) {

            let _material = {
                diffuse: material.result.rgba,
                textureName: null,
                opacitymap: null,
            };

            let chunkTexture = Renderware.findChunk(material, Renderware.CHUNK_TEXTURE);
            if (chunkTexture !== false){
                assert(chunkTexture.type, Renderware.CHUNK_TEXTURE);
                let chunksString = Renderware.findChunks(chunkTexture, Renderware.CHUNK_STRING);

                _material.textureName = chunksString[0].result.name;
                if (chunksString[0].result.name)
                    _material.opacitymap = chunksString[1].result.name;
            }



            if (typeof _material.textureName === "undefined" || _material.textureName == null){
                materials.push(new THREE.MeshBasicMaterial({
                    transparent: false, //todo
                    vertexColors: THREE.VertexColors
                }));

            }else{
                materials.push(new THREE.MeshBasicMaterial({
                    // shading: THREE.SmoothShading,
                    map: level._storage.tex.find(_material.textureName),
                    transparent: false, //todo
                    vertexColors: THREE.VertexColors
                }));

            }

        });

        return materials;
    }

    getGeometryValues( _chunk ) {

        let _this = this;
        let result = [];
        _chunk.result.chunks.forEach(function (chunk) {
            if (chunk.type === Renderware.CHUNK_PLANESECT){
                let _val = _this.getGeometryValues(chunk);
                _val.forEach(function (val) {
                    if (typeof val.vertex !== "undefined")
                        result.push(val);
                });
            } else if (typeof chunk.result.vertex !== "undefined" && chunk.result.vertex.length > 0) {
                result.push(chunk.result);
            }

        });

        return result;
    }

    normalize(level){
        let rootMesh = new THREE.Mesh();
        let materialList = this.getMaterialList(level);
        let geometryValues = this.getGeometryValues(this.tree);

        geometryValues.forEach(function (geometryValue) {


            let geometry = new THREE.Geometry();
            geometry.faces = geometryValue.faces;
            geometry.vertices = geometryValue.vertex;
            geometry.faceVertexUvs = [geometryValue.uvForFaces];
            geometry.uvsNeedUpdate = true;
            // let bufferGeo = new THREE.BufferGeometry();
            // bufferGeo.fromGeometry(geometry);
            // die;
            // bufferGeo.setAttribute( 'position', new THREE.Float32BufferAttribute( geometryValue.vertex, 3 ) );


            rootMesh.children.push(new THREE.Mesh(geometry, materialList));
        });

        return rootMesh;
    }

}
/**
 * BSP Reader based on the awesome work from Majest1c_R3 and Allen
 */
MANHUNT.fileLoader.BSP = function () {

    var loader = new THREE.FileLoader();
    loader.setResponseType('arraybuffer');

    var SceneRootBoundBox;
    var ParentSectorIndex;

    function loadNode(binary, NodeOffset) {
        binary.setCurrent(NodeOffset);

        var CompareCoordIndex = binary.consume(4, 'int32');

        if (CompareCoordIndex === -1) {

            var Padding = binary.consume(4, 'int32');
            var NumFaces = binary.consume(4, 'uint32');
            var NumVerts = binary.consume(4, 'uint32');

            var MeshBoundingBox = [
                binary.readVector3(4, 'float32', true), //MIN
                binary.readVector3(4, 'float32', true) //MAX
            ];

            var VertsOffset = binary.consume(4, 'uint32');
            binary.consume(4, 'int32'); //padding

            var PrelightOffset = binary.consume(4, 'uint32');
            binary.consume(4, 'int32'); //padding

            var FacesOffset = binary.consume(4, 'int32');
            var FacesGroupsOffset = binary.consume(4, 'int32');
            var GeometryOffset = binary.consume(4, 'int32');
            ParentSectorIndex = binary.consume(2, 'uint16');

            var MeshSize = new THREE.Vector3(
                (MeshBoundingBox[1].x - MeshBoundingBox[0].x),
                (MeshBoundingBox[1].y - MeshBoundingBox[0].y),
                (MeshBoundingBox[1].z - MeshBoundingBox[0].z)
            );

            var MeshPosition = new THREE.Vector3(
                ((MeshBoundingBox[1].x + MeshBoundingBox[0].x) / 2),
                ((MeshBoundingBox[1].y + MeshBoundingBox[0].y) / 2),
                ((MeshBoundingBox[1].z + MeshBoundingBox[0].z) / 2)
            );

            var ObjectBoundBox = new THREE.Mesh(
                new THREE.CubeGeometry(
                    MeshSize.x * 48,
                    MeshSize.y * 48,
                    MeshSize.z * 48,
                ),
                new THREE.MeshBasicMaterial({
                    wireframe	: true,
                    color: 0xff11ff
                })
            );

            ObjectBoundBox.position.copy(MeshPosition);
            ObjectBoundBox.position.multiply(new THREE.Vector3(48,48,48));
            ObjectBoundBox.name = "bbox";

            SceneRootBoundBox.children.push(ObjectBoundBox);

            var ColVerts = [];
            var ColPrelight = [];
            var ColFaces = [];
            var materialForFace = [];
            var i;

            if (NumVerts !== 0) {

                binary.setCurrent(VertsOffset);

                for (i = 0; i < NumVerts; i++) {
                    var vec3 = binary.readVector3(4, 'float32', true);
                    vec3.multiply(new THREE.Vector3(48,48,48));
                    ColVerts.push(vec3);
                }

                if (PrelightOffset !== 0) {
                    binary.setCurrent(PrelightOffset);

                    for (i = 0; i < NumVerts; i++) {
                        ColPrelight.push(binary.readColorRGBA());
                    }
                }
            }

            if (NumFaces !== 0) {
                binary.setCurrent(FacesOffset);

                for (i = 0; i < NumFaces; i++) {
                    var face3 = binary.readFace3(2, 'uint16');

                    if (ColPrelight.length !== 0) {
                        face3.vertexColors = [
                            ColPrelight[face3.a],
                            ColPrelight[face3.b],
                            ColPrelight[face3.c]
                        ];
                    }

                    ColFaces.push(face3);
                    materialForFace.push(binary.consume(2, 'uint16'));
                }

                var geometry = new THREE.Geometry();
                geometry.faces = ColFaces;
                geometry.vertices = ColVerts;

                var ColObject = new THREE.Mesh(geometry, new THREE.MeshBasicMaterial({
                    vertexColors: THREE.VertexColors
                }));

                ColObject.position.y += 1;
                ColObject.position.x += 1;
                ColObject.position.z += 1;

                ColObject.name = "preligh";
                SceneRootBoundBox.children.push(ColObject);
            }


        } else {
            var IfTrueOffset = binary.consume(4, 'uint32');
            var IfFalseOffset = binary.consume(4, 'uint32');
            var Compare = binary.readVector3();
            loadNode(binary, IfTrueOffset);
            loadNode(binary, IfFalseOffset);
        }
    }


    function loadSector(binary, SectorOffset) {
        binary.setCurrent(SectorOffset);

        var SectorIdent = binary.consume(4, 'int32');
        var NextSectorOffset = binary.consume(4, 'uint32');
        var SectorRenderTreeOffset = binary.consume(4, 'uint32');
        var SectorIndex = binary.consume(2, 'uint16');
        var NumAdjacentSectors = binary.consume(2, 'uint16');
        var AdjacentSectorsIndicesOffset = binary.consume(4, 'uint32');
        var AdjacentSectorsPortalsOffset = binary.consume(4, 'uint32');

        var AdjacentIDs = [];
        var i;
        if (NumAdjacentSectors !== 0) {
            binary.setCurrent(binary.current() + AdjacentSectorsIndicesOffset);

            for (i = 0; i < NumAdjacentSectors; i++) {
                AdjacentIDs[i] = binary.consume(1, 'uint8');
            }

            binary.setCurrent(AdjacentSectorsPortalsOffset);

            for (i = 0; i < NumAdjacentSectors; i++) {

                var PortalVerts = [];
                var PortalFaces = [];

                for (var v = 0; v < 4; v++) {

                    var vertex = binary.readVector3(4, 'float32', true, 2, 'int16');
                    vertex.multiply(new THREE.Vector3(48,48,48));
                    // vertex.x *= -1;
                    PortalVerts.push(vertex);
                }

                PortalFaces.push([1, 2, 3]);
                PortalFaces.push([1, 3, 4]);

                var geometry = new THREE.Geometry();
                geometry.faces = PortalFaces;
                geometry.vertices = PortalVerts;

                var material = new THREE.MeshBasicMaterial({
                    wireframe	: true,
                    color: 0xff11ff
                });

                var mesh = new THREE.Mesh(geometry, material);
                mesh.name = "Portal " + SectorIndex + " to " + AdjacentIDs[i];
                //todo: portal ?!
            }
        }

        loadNode(binary, SectorRenderTreeOffset);

        if (NextSectorOffset !== 0) loadSector(binary, NextSectorOffset);

    }

    function parseManhunt2(binary, isScene3){


        binary.setCurrent(48);

        var sceneBBox = [
            binary.readVector3(4, 'float32', true), //MIN
            binary.readVector3(4, 'float32', true) //MAX
        ];

        var materialsOffset = binary.consume(4, 'int32');
        var materialCount = binary.consume(4, 'int32');

        var SectorListOffset = false;

        if (isScene3) {

            var sceneBBoxSize = new THREE.Vector3(
                (sceneBBox[1].x - sceneBBox[0].x),
                (sceneBBox[1].y - sceneBBox[0].y),
                (sceneBBox[1].z - sceneBBox[0].z)
            );

            var sceneBBoxPosition = new THREE.Vector3(
                ((sceneBBox[1].x + sceneBBox[0].x) / 2),
                ((sceneBBox[1].y + sceneBBox[0].y) / 2),
                ((sceneBBox[1].z + sceneBBox[0].z) / 2)
            );

            SceneRootBoundBox	= new THREE.Mesh(
                new THREE.CubeGeometry(
                    sceneBBoxSize.x,
                    sceneBBoxSize.y,
                    sceneBBoxSize.z,
                ),
                new THREE.MeshBasicMaterial({
                    wireframe	: true
                })
            );
            SceneRootBoundBox.position.copy(sceneBBoxPosition);
            // SceneRootBoundBox.position.multiply(new THREE.Vector3(48,48,48));

            SceneRootBoundBox.name = 'scene3';

            SectorListOffset = binary.consume(4, 'int32');
            loadSector(binary, SectorListOffset);


            return SceneRootBoundBox;
        }

        var material = [];
        for (var i = 0; i < materialCount; i++) {
            binary.setCurrent(materialsOffset + 12 * i);
            binary.setCurrent(binary.consume(4, 'int32'));

            var texture = MANHUNT.level.getStorage('tex').find(binary.getString(0, false));
            material.push(new THREE.MeshBasicMaterial({
                map: texture,
                transparent: texture.format === THREE.RGBA_S3TC_DXT5_Format,
                vertexColors: THREE.VertexColors
            }));
        }

        binary.setCurrent(16);
        var mainfat_offset = binary.consume(4, 'int32');
        var mainfat_cnt = binary.consume(4, 'int32');

        binary.setCurrent(88);

        var fat_cntr = 0;
        var geom_cntr = 0;
        var meshRoot = new THREE.Mesh();
        while (fat_cntr !== mainfat_cnt) {

            var geometry = new THREE.Geometry();

            binary.setCurrent(mainfat_offset + 4 * fat_cntr); //FAT_OFFSET
            binary.setCurrent(binary.consume(4, 'int32')); //FAT_entry

            var Geom_offset = binary.consume(4, 'int32');
            binary.setCurrent(Geom_offset);
            var GeomIdent = binary.consume(4, 'int32');

            if (GeomIdent === 0x0045D454) {
                var normals = [];

                var materialForFace = [];
                // var materialBoundingForFace = [];

                geom_cntr += 1;

                var model_size = binary.consume(4, 'int32');
                var unknown = binary.consume(4, 'int32'); //zero
                var materials_count = binary.consume(4, 'int32'); //numMatrialID
                var fce_count = binary.consume(4, 'int32'); //numFaceIndex
                var boundingSphere = binary.readVector3();
                var boundingSphereRadius = binary.consume(4, 'float32');
                var boundingSphereScale = binary.readVector3();

                var vert_count = binary.consume(4, 'int32'); //numVertex
                var verts_offset = Geom_offset + 148 + materials_count * 44 + fce_count * 2;
                var facesOffset = Geom_offset + 148 + materials_count * 44;

                for (i = 0; i < materials_count; i++) {

                    binary.setCurrent(Geom_offset + 148 + i * 44);

                    var mat_boundingBox = [
                        binary.readVector3(),
                        binary.readVector3()
                    ];

                    var cur_mat_faces = binary.consume(2, 'uint16');
                    var cur_texture = binary.consume(2, 'uint16');
                    var cur_faces_skip = binary.consume(2, 'uint16');

                    cur_mat_faces = cur_mat_faces / 3;
                    cur_faces_skip = cur_faces_skip / 3;

                    for (var k = cur_faces_skip; k < (cur_faces_skip + cur_mat_faces); k++) {
                        materialForFace[k] = cur_texture;
                        // materialBoundingForFace[k] = [BoundingBoxMin, BoundingBoxMax];
                    }
                }

                binary.setCurrent(facesOffset);
                geometry.faces = binary.readFaces3(fce_count / 3, materialForFace);

                var uvArray = [];
                var cpvArray = [];
                binary.setCurrent(verts_offset);
                for (i = 0; i < vert_count; i++) {

                    geometry.vertices.push(binary.readVector3());
                    normals.push(
                        binary
                            .readVector3(2, 'int16', true)
                            .divide(new THREE.Vector3(32768.0, 32768.0, 32768.0))
                    );
                    cpvArray.push(binary.readColorBGRADiv255());


                    uvArray.push([
                        binary.consume(4, 'float32'),
                        binary.consume(4, 'float32')
                    ]);

                    binary.setCurrent(binary.current() + 8);
                }

                var uvForFaces = [];
                geometry.faces.forEach(function (face, faceIndex) {

                    face.vertexNormals = [
                        normals[face.a],
                        normals[face.b],
                        normals[face.c]
                    ];

                    face.vertexColors = [
                        cpvArray[face.a],
                        cpvArray[face.b],
                        cpvArray[face.c]
                    ];

                    uvForFaces[faceIndex] = [
                        new THREE.Vector2(
                            uvArray[face.a][0],
                            uvArray[face.a][1]
                        ),
                        new THREE.Vector2(
                            uvArray[face.b][0],
                            uvArray[face.b][1]
                        ),
                        new THREE.Vector2(
                            uvArray[face.c][0],
                            uvArray[face.c][1]
                        )
                    ];
                });

                geometry.faceVertexUvs = [uvForFaces];
                geometry.uvsNeedUpdate = true;


                // geometry.computeBoundingSphere();
                // geometry.computeFaceNormals();
                // geometry.computeVertexNormals();

                var mesh = new THREE.Mesh(geometry, material);
                mesh.scale.set(MANHUNT.scale, MANHUNT.scale, MANHUNT.scale);
                mesh.alphaTest = 0.5;
                mesh.colorsNeedUpdate = true;


                meshRoot.children.push(mesh);
            }

            fat_cntr += 1;
        }

        meshRoot.rotation.y = 270 * (Math.PI / 180); // convert vertical fov to radians

        return meshRoot;

    }

    function parseManhunt1(binary){

        function readBlock(){
            return [
                binary.consume(4, 'uint32'),
                binary.consume(4, 'uint32'),
                binary.consume(4, 'uint32')
            ];
        }

        function rTexture(){
            block = readBlock();
            if (block[0] !== 6) return console.log('[MANHUNT.fileLoader.BSP] Texture parsing issue, except 6 got ', block[0]);
            block = readBlock();
            if (block[0] !== 1) return console.log('[MANHUNT.fileLoader.BSP] Texture parsing issue, except 1 got ', block[0]);

            var texFlag = binary.consume(4, 'int32');
            block = readBlock();
            if (block[0] !== 2) return console.log('[MANHUNT.fileLoader.BSP] Texture parsing issue, except 2 got ', block[0]);

            var endTex = binary.current() + block[1];
            var name = binary.getString(0);

            binary.setCurrent(endTex);

            block = readBlock();
            binary.setCurrent(binary.current() + block[1]);

            block = readBlock();
            if (block[0] !== 3) return console.log('[MANHUNT.fileLoader.BSP] Texture parsing issue, except 3 got ', block[0]);
            if (block[1] !== 0) {
                binary.setCurrent(binary.current() + block[1])
            }

            return name;
        }

        function rMaterial(){
            block = readBlock();
            if (block[0] !== 7) return console.log('[MANHUNT.fileLoader.BSP] Material parsing issue, except 7 got ', block[0]);
            block = readBlock();
            if (block[0] !== 1) return console.log('[MANHUNT.fileLoader.BSP] Material parsing issue, except 1 got ', block[0]);

            binary.consume(4, 'int32');

            var mat = {
                color: binary.readColorRGBA()
            };

            binary.consume(4, 'int32');

            var textureCount = binary.consume(4, 'int32');
            mat.amb = binary.consume(4, 'float32');
            mat.spc = binary.consume(4, 'float32');
            mat.dif = binary.consume(4, 'float32');

            if (textureCount === 1){
                mat.name = rTexture();
            }

            block = readBlock();
            if (block[0] !== 3) return console.log('[MANHUNT.fileLoader.BSP] Material parsing issue, except 3 got ', block[0]);
            if (block[1] !== 0) binary.setCurrent( binary.current() + block[1]);

            return mat;
        }

        function rMaterialList(){
            //material list
            block = readBlock();
            if (block[0] !== 8) return console.log('[MANHUNT.fileLoader.BSP] Material List parsing issue, except 8 got ', block[0]);
            block = readBlock();
            if (block[0] !== 1) return console.log('[MANHUNT.fileLoader.BSP] Material List parsing issue, except 1 got ', block[0]);

            var materialList = [];
            var materialCount = binary.consume(4, 'int32');

            binary.setCurrent(binary.current() + (materialCount*4));

            for(i = 0; i < materialCount; i++){

                var mat = rMaterial();
                var texture = MANHUNT.level.getStorage('tex').find(mat.name);
                var trans = false;
                if (texture.format === THREE.RGBAFormat) trans = true;

                materialList.push(new THREE.MeshBasicMaterial({
                    // wireframe: true,
                     map: texture,
                    transparent: trans,
                    // vertexColors: THREE.VertexColors
                }));
            }

            return materialList;
        }

        var rootMesh = new THREE.Mesh();

        binary.setCurrent(40);
        var faceCount = binary.consume(4, 'uint32');
        var vertexCount = binary.consume(4, 'uint32');
        var unk = binary.consume(4, 'uint32');
        var sectors = binary.consume(4, 'uint32');
        var i, block;

        binary.setCurrent(binary.current() + 32);

        var materialList = rMaterialList();

        while(sectors > 0){
            block = readBlock();

            if (block[0] === 10) {
                block = readBlock();
                binary.setCurrent(binary.current() + block[1]);
            }else if (block[0] === 9){

                sectors--;
                var sectionEnd = binary.current() + block[1];

                block = readBlock();
                if (block[1] > 44) {
                    binary.consume(4, 'uint32');
                    var sectionFaceCount = binary.consume(4, 'uint32');
                    var sectionVertexCount = binary.consume(4, 'uint32');

                    var vertex = [];
                    binary.setCurrent(binary.current() + 32);
                    for(i = 0; i < sectionVertexCount; i++){
                        var vec = binary.readVector3();
                        var z = vec.z;
                        vec.z = vec.y * -1;
                        vec.y = z;
                        vertex.push(vec);
                    }

                    var cpvArray = [];
                    binary.setCurrent(binary.current() + (4*sectionVertexCount));
                    for(i = 0; i < sectionVertexCount; i++){
                        cpvArray.push(binary.readColorRGBA());
                    }

                    var uvArray = [];
                    for(i = 0; i < sectionVertexCount; i++){
                        uvArray.push([
                            binary.consume(4, 'float32'),
                            binary.consume(4, 'float32')
                        ]);
                    }

                    var faces = [];
                    var faceMaterial = [];
                    var uvForFaces = [];
                    for(i = 0; i < sectionFaceCount; i++){
                        var face;
                        if (block[2] === 0x1803FFFF) {
                            face = binary.readFace3(2, 'uint16');
                            face.materialIndex = binary.consume(2, 'uint16');
                            faces.push(face);
                            // faceMaterial.push(binary.consume(2, 'uint16'));
                        }else{
                            var matId = binary.consume(2, 'uint16');
                            face = binary.readFace3(2, 'uint16');
                            face.materialIndex = matId;
                            faces.push(face);
                        }

                        // face.vertexColors = [
                        //     cpvArray[face.a],
                        //     cpvArray[face.b],
                        //     cpvArray[face.c]
                        // ];

                        uvForFaces[i] = [
                            new THREE.Vector2(
                                uvArray[face.a][0],
                                uvArray[face.a][1]
                            ),
                            new THREE.Vector2(
                                uvArray[face.b][0],
                                uvArray[face.b][1]
                            ),
                            new THREE.Vector2(
                                uvArray[face.c][0],
                                uvArray[face.c][1]
                            )
                        ];
                    }


                    var geometry = new THREE.Geometry();
                    geometry.faces = faces;
                    geometry.vertices = vertex;
                    geometry.faceVertexUvs = [uvForFaces];
                    geometry.uvsNeedUpdate = true;

                    var section = new THREE.Mesh(geometry, materialList);

                    // section.scale.set(5, 5,5);
                    section.scale.set(MANHUNT.scale, MANHUNT.scale, MANHUNT.scale);

                    section.rotation.y = 270 * (Math.PI / 180); // convert vertical fov to radians

                    rootMesh.children.push(section);
                }

                binary.setCurrent(sectionEnd);
            }
        }


        return rootMesh;
    }

    return {
        load: function (file, callback) {

            loader.load(
                file,
                function (data) {

                    var isScene3 = file.indexOf('scene3') !== -1;

                    var binary = new NBinary(data);
                    var gameId = binary.consume(4, 'uint32');
                    var meshRoot;

                    if (gameId === 1465011268){ //DLRW => Manhunt 2
                        meshRoot = parseManhunt2(binary, isScene3);
                    }else if (gameId === 11) { //11 => Manhunt 1
                        meshRoot = parseManhunt1(binary);

                    }else{
                        console.log("[MANHUNT.fileLoader.BSP] Unsupported Map?! ", file, gameId);
                        callback([]);
                        return;
                    }



                    meshRoot.name = isScene3 ? 'scene3' : (file.indexOf('scene1') !== -1 ? 'scene1' : 'scene2');
                    callback([meshRoot]);
                }
            );
        }
    };
};

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

    return {
        load: function (file, callback) {

            loader.load(
                file,
                function (data) {

                    var isScene3 = file.indexOf('scene3') !== -1;

                    var binary = new NBinary(data);

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

                        callback([SceneRootBoundBox]);
                        return;
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

                    meshRoot.name = isScene3 ? 'scene3' : (file.indexOf('scene1') !== -1 ? 'scene1' : 'scene2');
                    callback([meshRoot]);
                }
            );
        }
    };
};

/*--------------------------------
	RenderWare DFF model Importer
	RenderWare Version 3.7.0.2
	Platform PC
	by Allen
	2020-12-13
    ported to JS by Sor3nt
    2021-01-17

	Support game:
		Agent Hugo
		Manhunt
--------------------------------*/

MANHUNT.parser.dff = function (binary) {

    let allBones = [];
    let allBonesMesh = [];
    let meshBone = {};

    const rpGEOMETRYPOSITIONS = 0x00000002;
    /**<This geometry has positions */
    const rpGEOMETRYTEXTURED = 0x00000004;
    /**<This geometry has only one set of
     texture coordinates. Texture
     coordinates are specified on a per
     vertex basis */
    const rpGEOMETRYPRELIT = 0x00000008;
    /**<This geometry has pre-light colors */
    const rpGEOMETRYNORMALS = 0x00000010;
    /**<This geometry has vertex normals */

    const rpGEOMETRYTEXTURED2 = 0x00000080;
    /**<This geometry has at least 2 sets of
     texture coordinates. */

    function ReadMatrix4x3() {
        return [
            binary.consume(4, 'float32'), binary.consume(4, 'float32'), binary.consume(4, 'float32'), 0,
            binary.consume(4, 'float32'), binary.consume(4, 'float32'), binary.consume(4, 'float32'), 0,
            binary.consume(4, 'float32'), binary.consume(4, 'float32'), binary.consume(4, 'float32'), 0,
            binary.consume(4, 'float32'), binary.consume(4, 'float32'), binary.consume(4, 'float32'), 1
        ]

    }

    function ReadMatrix4x4() {
        return [
            binary.consume(4, 'float32'), binary.consume(4, 'float32'), binary.consume(4, 'float32'), binary.consume(4, 'float32'),
            binary.consume(4, 'float32'), binary.consume(4, 'float32'), binary.consume(4, 'float32'), binary.consume(4, 'float32'),
            binary.consume(4, 'float32'), binary.consume(4, 'float32'), binary.consume(4, 'float32'), binary.consume(4, 'float32'),
            binary.consume(4, 'float32'), binary.consume(4, 'float32'), binary.consume(4, 'float32'), binary.consume(4, 'float32')
        ]

    }

    function ReadChunk() {
        return {
            id: binary.consume(4, 'int32'),
            size: binary.consume(4, 'uint32'),
            version: binary.consume(4, 'uint32')
        };
    }

    function ReadColor() {
        return binary.readColorRGBA();
    }

    function ReadFrameData() {
        return {
            matrix: ReadMatrix4x3(),
            ParentFrameID: binary.consume(4, 'int32') + 1,
            MatrixCreationFlags: binary.consume(4, 'int32')
        }
    }

    function ReadFrameDataList(FrameCount) {
        let FrameListArray = [];
        for (let i = 0; i < FrameCount; i++) {
            FrameListArray.push(ReadFrameData())
        }

        return FrameListArray;
    }

    function ReadFrameName(NameLength) {
        return binary.consume(NameLength, 'nbinary').getString(0);
    }

    function ReadrwString() {
        return ReadFrameName(ReadChunk().size);
    }

    function ReadHAnimPLG(BoneIDArray, HAnimBoneArray) {
        binary.consume(4, 'uint32');
        let BoneID = binary.consume(4, 'uint32'); //Animation Bone ID
        BoneIDArray.push(BoneID);

        let BoneCount = binary.consume(4, 'uint32');
        if (BoneCount > 0) {

            binary.consume(4, 'int32'); //flags
            binary.consume(4, 'int32'); //keyFrameSize

            for (let i = 0; i < BoneCount; i++) {
                HAnimBoneArray.push({
                    BoneID: binary.consume(4, 'int32'),
                    BoneIndex: binary.consume(4, 'uint32'),
                    BoneType: binary.consume(4, 'uint32'),
                });
            }
        }
    }

    function readUserDataPLG(index) {
        let numSet = binary.consume(4, 'int32');
        let boneName = "bone" + index;

        for (let i = 0; i < numSet; i++) {
            let typeNameLen = binary.consume(4, 'int32');
            binary.seek(typeNameLen);

            binary.consume(4, 'int32'); //u2
            binary.consume(4, 'int32'); //u3

            let nameLen = binary.consume(4, 'int32');
            if (nameLen > 0)
                boneName = binary.consume(nameLen, 'nbinary').getString(0);
        }

        return boneName;
    }

    function ReadFrameExt(FrameCount, FrameNameArray, BoneIDArray, HAnimBoneArray) {

        for (let i = 0; i < FrameCount; i++) {

            let frameExtHeader = ReadChunk();
            let hasName = false;

            if (frameExtHeader.size > 0) {
                let endOfs = binary.current() + frameExtHeader.size;

                while (binary.current() < endOfs) {
                    let Header = ReadChunk();

                    if (Header.id === 0x253F2FE) {
                        hasName = true;
                        FrameNameArray.push(ReadFrameName(Header.size));

                    } else if (Header.id === 0x11E) {
                        ReadHAnimPLG(BoneIDArray, HAnimBoneArray);

                    } else if (Header.id === 0x11F) {
                        hasName = true;
                        FrameNameArray.append(readUserDataPLG(i));
                    } else {
                        binary.seek(Header.size);
                    }
                }

                if (hasName === false)
                    FrameNameArray.push("bone" + i);
            } else {
                if (i === 0)
                    FrameNameArray.push("RootDummy");
                else
                    FrameNameArray.push("bone" + i);
            }
        }
    }

    function ReadFrameList() {
        ReadChunk(); // frameListHeader
        ReadChunk(); // FrameListStructHeader

        let FrameCount = binary.consume(4, 'uint32');
        let FrameDataListArray = ReadFrameDataList(FrameCount);
        let FrameNameArray = [];
        let HAnimBoneArray = [];
        let BoneIDArray = [];

        ReadFrameExt(
            FrameCount,
            FrameNameArray,
            BoneIDArray,
            HAnimBoneArray
        );

        let boneArray = [];
        let skinBoneArray = [];
        let bne = null;
        let i, j = 0;

        for (i = 0; i < FrameCount; i++) {
            bne = {
                name: FrameNameArray[i],
                userProp: {},
                frame: FrameDataListArray[i]
            };

            if (i > 0)
                bne.userProp.BoneID = BoneIDArray[i-1]; // this boneid is anim boneid

            boneArray.push(bne);
        }

        for (i = 0; i < FrameCount; i++) {
            bne = boneArray[i];
            let boneID = bne.userProp.BoneID;
            if (typeof boneID !== "undefined") {
                for (j = 0; j < HAnimBoneArray.length; j++) {
                    if (HAnimBoneArray[j].BoneID === boneID) {
                        bne.userProp.BoneIndex = HAnimBoneArray[j].BoneIndex;
                        bne.userProp.BoneType = HAnimBoneArray[j].BoneType;
                    }
                }
            }
        }

        for (i = 0; i < FrameCount; i++) {
            for (j = 0; j < FrameCount; j++) {
                bne = boneArray[j];
                let boneIndex = bne.userProp.BoneIndex;
                if (typeof boneIndex !== "undefined" && boneIndex === i)
                    skinBoneArray.push(bne);
            }

        }

        return {bones: boneArray, skinBones: skinBoneArray};
    }

    function ReadGeometryListSturct() {
        ReadChunk(); //GeometryListSturctHeader
        return binary.consume(4, 'uint32');
    }

    function ReadMaterialListStruct() {
        ReadChunk(); //header
        let MaterialCount = binary.consume(4, 'int32');
        binary.seek(MaterialCount * 4);

        return MaterialCount
    }

    function ReadTexture() {
        ReadChunk(); //textureHeader
        ReadChunk(); //textureStructHeader

        let Tex = {
            TextureFilter: null,
            U_addressing: null,
            V_addressing: null,
            UseMipLevelFlag: null,
            TextureName: null,
            AlphaTextureName: null
        };

        //ReadTextureStruct
        Tex.TextureFilter = binary.consume(1, 'int8');
        let UVaddressing = binary.consume(1, 'int8');

        let mode = binary.consume(2, 'int16');
        Tex.UseMipLevelFlag = (mode & -15)  === -15;
        Tex.U_addressing = (UVaddressing & -4) === -4;
        Tex.V_addressing = UVaddressing & 0xf;
        //end ReadTextureStruct

        Tex.TextureName = ReadrwString();
        Tex.AlphaTextureName = ReadrwString();

        let TextureExtension = ReadChunk();
        if (TextureExtension.size > 0)
            binary.seek(TextureExtension.size);

        return Tex;
    }

    function ReadSufaceProp() {
        return {
            ambient: binary.consume(4, 'float32'),
            diffuse: binary.consume(4, 'float32'),
            specular: binary.consume(4, 'float32')
        }
    }

    function ReadReflectionMaterial() {
        return {
            EnvironmentMapScaleX: binary.consume(4, 'float32'), // Environment Map Scale X
            EnvironmentMapScaleY: binary.consume(4, 'float32'), // Environment Map Scale Y
            EnvironmentMapOffsetX: binary.consume(4, 'float32'), // Environment Map Offset X
            EnvironmentMapOffsetY: binary.consume(4, 'float32'), // Environment Map Offset Y
            ReflectionIntensity: binary.consume(4, 'float32'), // Reflection Intensity (Shininess, 0.0-1.0)
            EnvironmentTexturePtr: binary.consume(4, 'float32') // Environment Texture Ptr, always 0 (zero)
        };
    }

    function ReadMaterialExtension() {
        let mtlExtHeader = ReadChunk();
        let endOffset = binary.current() + mtlExtHeader.size;

        if (mtlExtHeader.size > 0) {

            while (binary.current() < endOffset) {

                let header = ReadChunk();
                if (header.id === 0x0253F2FC) ReadReflectionMaterial();
                else binary.seek(header.size);
            }
        }
    }

    function ReadMaterial() {
        ReadChunk(); //MaterailHeader
        ReadChunk(); //MaterialStructHeader
        binary.consume(4, 'int32'); //flag
        let RGBA = ReadColor();
        binary.consume(4, 'int32'); //unused
        let HasTexture = binary.consume(4, 'int32');
        ReadSufaceProp(); //SufaceProp

        let mtl = {};
        mtl.diffuse = RGBA;
        if (HasTexture === 1) {
            let Tex = ReadTexture();

            mtl.TextureName = Tex.TextureName;
            if (Tex.AlphaTextureName !== "")
                mtl.opacitymap = Tex.AlphaTextureName;
        }

        ReadMaterialExtension();
        return mtl;
    }

    function ReadMaterialList() {
        let Mtl_Array = [];

        let MtlCount = ReadMaterialListStruct();
        for (let i = 0; i < MtlCount; i++) {
            Mtl_Array.push(ReadMaterial());
        }

        return Mtl_Array;
    }

    function ReadUV(VertexCount) {
        let UVArray = [];

        for (let i = 0; i < VertexCount; i++) {
            UVArray.push([
                binary.consume(4, 'float32'),
                binary.consume(4, 'float32')
            ]);
        }

        return UVArray;
    }

    function ReadVector3() {
        return binary.readVector3();
    }

    function ReadBoundingSphere() {
        return {
            position: binary.readVector3(),
            radius: binary.consume(4, 'float32')
        };
    }

    function ReadVertex(VertexCount) {
        let VertArray = [];
        for (let i = 0; i < VertexCount; i++) {
            VertArray.push(ReadVector3());
        }
        return VertArray;
    }

    function ReadNormal(VertexCount) {
        let NormalArray = [];
        for (let i = 0; i < VertexCount; i++) {
            NormalArray.push(ReadVector3());
        }
        return NormalArray;
    }

    function ReadFace(FaceCount) {
        let FaceMat = {
            Face: [],
            MatID: [],
        };

        for (let i = 0; i < FaceCount; i++) {

            let f2 = binary.consume(2, 'uint16');
            let f1 = binary.consume(2, 'uint16');
            let matID = binary.consume(2, 'uint16');
            let f3 = binary.consume(2, 'uint16');

            FaceMat.Face.push([f1, f2, f3]);
            FaceMat.MatID.push(matID);
        }

        return FaceMat;
    }

    function ReadVertexColor(VertexCount) {
        let VCArray = [];
        for (let i = 0; i < VertexCount; i++) {
            VCArray.push(ReadColor());
        }

        return VCArray;
    }

    function ReadSkinPLG(VertexCount) {
        let BoneCount = binary.consume(1, 'uint8');
        let UsedIDCount = binary.consume(1, 'uint8');
        binary.consume(2, 'int16'); // maxWeightsPerVertex; per vetex used max bone number and weights
        binary.seek(UsedIDCount);

        let SkinPLG = {boneids: [], weights: [], inverseMatrix: []};
        let Matrix_Array = [];

        for (let i = 0; i < VertexCount; i++) {
            SkinPLG.boneids.push([
                binary.consume(1, 'uint8'),
                binary.consume(1, 'uint8'),
                binary.consume(1, 'uint8'),
                binary.consume(1, 'uint8')
            ]);
        }

        for (let i = 0; i < VertexCount; i++) {

            SkinPLG.weights.push([
                binary.consume(4, 'float32'),
                binary.consume(4, 'float32'),
                binary.consume(4, 'float32'),
                binary.consume(4, 'float32')
            ]);
        }

        for (let i = 0; i < BoneCount; i++) {
            Matrix_Array.push(ReadMatrix4x4());
        }

        binary.seek(12);

        SkinPLG.inverseMatrix = Matrix_Array;

        return SkinPLG;
    }

    function ReadBinMeshPLG() {
        let FaceType = binary.consume(4, 'int32');
        let SplitCount = binary.consume(4, 'uint32');
        binary.consume(4, 'uint32'); //FaceCount

        let FaceMat = {Face: [], MatID: []};
        if (FaceType === 1) {
            for (let S = 0; S < SplitCount; S++) {
                let SplitFaceCount = binary.consume(4, 'uint32');
                let MatID = binary.consume(4, 'uint32') + 1;
                let FaceDirection = -1;
                let tempIDArray = [];

                for (let i = 0; i < SplitFaceCount; i++) {
                    tempIDArray.push(binary.consume(4, 'uint32') + 1);

                    if (i > 2) {
                        FaceDirection *= -1;
                        let f3 = tempIDArray[tempIDArray.length];
                        let f1 = tempIDArray[tempIDArray.length - 2];
                        let f2 = tempIDArray[tempIDArray.length - 1];

                        if (f3 !== f2 && f3 !== f1 && f1 !== f2) {
                            if (FaceDirection > 0) FaceMat.Face.push([f1, f2, f3]);
                            else FaceMat.Face.push([f2, f1, f3]);

                            FaceMat.MatID.push(MatID);
                        }

                        f1 = f2;
                        f2 = f3;
                    }
                }
            }
        }

        return FaceMat;
    }

    function ReadGeometry(Atomic) {

        let Vert_array = [];
        let Normal_array = [];
        let UV1_array = [];
        let UV2_array = [];
        let Face_array = [];
        let MatID_Array = [];
        let VColor_Array = [];

        ReadChunk(); //GeometryHeader
        ReadChunk(); //GeometryStructHeader

        let FormatFlags = binary.consume(2, 'uint16');
        binary.consume(1, 'int8'); //NumTexCoorsCustom
        binary.consume(1, 'int8');  //GeometryNativeFlags

        let FaceCount = binary.consume(4, 'uint32');
        let VertexCount = binary.consume(4, 'uint32');

        binary.consume(4, 'uint32'); //numMorphTargets

        let GeometryMeshes = (FormatFlags & rpGEOMETRYPOSITIONS) === rpGEOMETRYPOSITIONS;
        let GeometryTextured = (FormatFlags & rpGEOMETRYTEXTURED) === rpGEOMETRYTEXTURED;
        let GeometryPrelit = (FormatFlags & rpGEOMETRYPRELIT) === rpGEOMETRYPRELIT;
        let GeometryNormals = (FormatFlags & rpGEOMETRYNORMALS) === rpGEOMETRYNORMALS;
        // let GeometryLight = FormatFlags & rpGEOMETRYLIGHT === rpGEOMETRYLIGHT;
        // let GeometryModulateMaterialColor = FormatFlags & rpGEOMETRYMODULATEMATERIALCOLOR === rpGEOMETRYMODULATEMATERIALCOLOR;
        let GeometryTextured_2 = (FormatFlags & rpGEOMETRYTEXTURED2) === rpGEOMETRYTEXTURED2;
        //
        if (GeometryPrelit === true) VColor_Array = ReadVertexColor(VertexCount);
        if (GeometryTextured === true || GeometryTextured_2 === true) UV1_array = ReadUV(VertexCount);
        if (GeometryTextured_2 === true) UV2_array = ReadUV(VertexCount);

        if (GeometryMeshes === true) {
            let FaceAndMatIDArray = ReadFace(FaceCount);
            Face_array = FaceAndMatIDArray.Face;
            MatID_Array = FaceAndMatIDArray.MatID;
        }

        ReadBoundingSphere(); //BoundingSp

        binary.consume(4, 'int32'); // HasPositionFlag
        binary.consume(4, 'int32'); //HasNomralFlag
        if (GeometryMeshes === true) Vert_array = ReadVertex(VertexCount);
        if (GeometryNormals === true) Normal_array = ReadNormal(VertexCount);


        ReadChunk(); //MaterialListHeader
        let mtl = ReadMaterialList();

        let skinFlag = false;
        let skinPLG = {};
        let FaceMat = undefined;
        let binMeshFlag = undefined;
        let GeometryExtensionHeader = ReadChunk();

        if (GeometryExtensionHeader.size > 0) {
            let endofs = binary.current() + GeometryExtensionHeader.size;
            while (binary.current() < endofs) {
                let header = ReadChunk();
                switch (header.id) {
                    case 0x116:
                        skinFlag = true;
                        skinPLG = ReadSkinPLG(VertexCount);
                        break;

                    case 0x50E:
                        FaceMat = ReadBinMeshPLG();
                        binMeshFlag = true;
                        break;

                    default:
                        binary.seek(header.size);
                }
            }
        }

        let msh = {
            skinPLG: skinPLG,
            face: []
        };

        msh.skinned = skinFlag;

        if (GeometryMeshes === true) {
            msh.vertices = Vert_array;

            if (GeometryNormals === true) {
                msh.normal = Normal_array;
            }

            if (GeometryTextured === true || GeometryTextured_2 === true) {
                msh.uv1 = UV1_array;
                msh.face = Face_array;
                msh.materialPerFace = MatID_Array;
            }

            if (GeometryPrelit === true) {
                msh.cpv = VColor_Array;
            }

            if (GeometryTextured_2 === true) {
                msh.uv2 = UV2_array;
            }
        }
        msh.material = mtl;

        msh.parentFrameID = Atomic.FrameIndex;

        return msh;
    }

    function ReadGeometryList(GeometryCount, AtomicArray) {
        let GeometryListArray = [];
        for (let i = 0; i < GeometryCount; i++) {
            GeometryListArray.push(ReadGeometry(AtomicArray[i]));
        }

        return GeometryListArray;
    }

    function ReadAtomicExtension() {
        let result = {};
        let Header = ReadChunk();
        switch (Header.id) {
            case 0x120:
                result.MatFXenabled = binary.consume(4, 'int32'); // bool32- MatFX enabled MaterialEffectsPLG 0x120
                break;
            case 0x1F:
                result.RWpluginIdentifier = binary.consume(4, 'int32'); //(e.g. 0x0116 or 0x0120)
                result.extraData = binary.consume(4, 'int32');
                break;

            default:
                binary.seek(Header.size);

        }

        return result;
    }

    function ReadAtomic(numAtomics) {

        let AtomicArray = [];
        for (let i = 0; i < numAtomics; i++) {
            let Atomic = {
                FrameIndex: false,
                GeometryIndex: false,
                flags: false
            };

            ReadChunk();  //AtomicHeader
            ReadChunk(); //AtomicStructHeader

            Atomic.FrameIndex = binary.consume(4, 'int32');
            Atomic.GeometryIndex = binary.consume(4, 'int32');
            Atomic.flags = binary.consume(4, 'int32');
            binary.consume(4, 'int32'); //unused

            AtomicArray.push(Atomic);
            ReadAtomicExtension();
        }

        return AtomicArray;
    }

    function ReadClump(offset) {
        allBones = [];
        allBonesMesh = [];

        binary.setCurrent(offset);

        ReadChunk(); //clumpHeader
        ReadChunk(); //clumpStruct
        let numAtomics = binary.consume(4, 'int32');
        binary.consume(4, 'int32'); //numLights
        binary.consume(4, 'int32'); //numCameras


        let BoneArray = ReadFrameList();
        let GeometryListHeader = ReadChunk();
        let GeometryListOffset = binary.current();

        binary.seek(GeometryListHeader.size);
        let AtomicArray = ReadAtomic(numAtomics);

        binary.setCurrent(GeometryListOffset);

        let GeometryCount = ReadGeometryListSturct();
        let parsedObjects = ReadGeometryList(GeometryCount, AtomicArray);

        return normalizeResult(BoneArray, GeometryCount, parsedObjects);

    }


    function ReadClumpList() {

        let entries = [];

        let count = 1;
        while (binary.current() < binary.length()) {
            let offset = binary.current();
            let DFFname = "";
            let clumpChunk = ReadChunk();
            let next = binary.current() + clumpChunk.size;

            let clumpStruct = ReadChunk();
            binary.seek(clumpStruct.size);
            binary.seek(12);

            let FrameListStructHeader = ReadChunk();
            binary.seek(FrameListStructHeader.size);

            ReadChunk(); // extheader
            let header = ReadChunk();
            if (header.id === 0x11F) {
                DFFname = readUserDataPLG(1);
            } else if (header.id === 0x253F2FE) {
                DFFname = ReadFrameName(header.size);
            } else if (header.id === 3) {
                let endOfs = binary.current() + header.size;
                while (binary.current() < endOfs) {
                    let sHeader = ReadChunk();
                    if (sHeader.id === 0x253F2FE) {
                        DFFname = ReadFrameName(sHeader.size);
                    } else if (sHeader.id === 0x11F) {
                        DFFname = readUserDataPLG(1);
                    } else {

                        binary.seek(sHeader.size);
                    }
                }
            }

            (function (offset, name) {
                entries.push({
                    name: name,
                    offset: offset,
                    data: function(){
                        let mesh = ReadClump(offset);
                        mesh.name = DFFname;
                        return mesh;
                    }
                });
            })(offset, DFFname);

            binary.setCurrent(next);
            count += 1;
        }

        return entries;
    }

    function normalizeResult( BoneArray, GeometryCount, parsedObjects ) {


        function generateBoneStructure(BoneArray){

            BoneArray.bones.forEach(function (bone) {
                allBones.push(createBone(bone));
            });

            BoneArray.bones.forEach(function (bone, index) {
                BoneArray.bones.forEach(function (boneInner, indexInner) {
                    if (indexInner === 0) return;

                    if (index === boneInner.frame.ParentFrameID - 1){
                        allBones[index].add(allBones[indexInner]);
                    }
                });
            });

            if (BoneArray.skinBones.length > 0){
                BoneArray.skinBones.forEach(function (boneInner, index) {
                    BoneArray.bones.forEach(function (bone, indexInner) {
                        if (bone.name === boneInner.name ){
                            allBonesMesh.push(allBones[indexInner]);
                        }
                    });
                });
            }
        }

        function createBone( data ){

            let bone = new THREE.Bone();
            bone.name = data.name;

            bone.applyMatrix4(
                (new THREE.Matrix4()).fromArray(data.frame.matrix)
            );

            return bone;
        }

        let result = {
            skeleton: false,

            bones: [],
            objects: []
        };

        generateBoneStructure(BoneArray);

        result.skeleton = new THREE.Skeleton( allBones );
        result.skeleton.bones.forEach(function(bone){
            bone.updateWorldMatrix();
        });

        parsedObjects.forEach(function (parsedObject) {
            meshBone = result.skeleton.bones[parsedObject.parentFrameID];

            let genericObject = {
                material: [],
                skinning: parsedObject.skinned,
                meshBone: meshBone,

                faces: [],
                faceVertexUvs: [[]],

                vertices: [],
                skinIndices: [],
                skinWeights: [],
            };


            parsedObject.material.forEach(function (parsedMaterial) {

                //TODO diffuse color
                if (typeof parsedMaterial.TextureName === "undefined") return;

                let material = new THREE.MeshStandardMaterial();
                material.name = parsedMaterial.TextureName;
                material.skinning = genericObject.skinning;
                material.vertexColors = THREE.VertexColors;

                genericObject.material.push(material);
            });

            parsedObject.vertices.forEach(function (vertexInfo, index) {

                if (BoneArray.skinBones.length > 0 && typeof parsedObject.skinPLG.boneids !== "undefined") {

                    var indice = new THREE.Vector4(0,0,0,0);
                    indice.fromArray(parsedObject.skinPLG.boneids[index]);
                    genericObject.skinIndices.push(indice);

                    var weight = new THREE.Vector4(0,0,0,0);
                    weight.fromArray(parsedObject.skinPLG.weights[index]);
                    genericObject.skinWeights.push(weight);

                }

                genericObject.vertices.push(
                    new THREE.Vector3( vertexInfo.x, vertexInfo.y, vertexInfo.z )
                );

            });

            for(let x = 0; x < parsedObject.face.length; x++) {

                let face = new THREE.Face3(parsedObject.face[x][0], parsedObject.face[x][1], parsedObject.face[x][2]);

                face.materialIndex = parsedObject.materialPerFace[x];

                face.vertexNormals =[
                    parsedObject.normal[face.a],
                    parsedObject.normal[face.b],
                    parsedObject.normal[face.c]
                ];

                if(parsedObject.uv1.length > 0){
                    genericObject.faceVertexUvs[0].push([
                        new THREE.Vector2(
                            parsedObject.uv1[face.a][0],
                            parsedObject.uv1[face.a][1]
                        ),
                        new THREE.Vector2(
                            parsedObject.uv1[face.b][0],
                            parsedObject.uv1[face.b][1]
                        ),
                        new THREE.Vector2(
                            parsedObject.uv1[face.c][0],
                            parsedObject.uv1[face.c][1]
                        ),
                    ]);
                }

                genericObject.faces.push(face);
            }

            result.objects.push(genericObject);
        });

        if (allBonesMesh.length > 0){
            //we need to rebuild the skeleton based only on mesh bones otherwise the indices and weight orders are wrong
            result.skeleton = new THREE.Skeleton( allBonesMesh );
        }

        return result;
    }

    return ReadClumpList();
};
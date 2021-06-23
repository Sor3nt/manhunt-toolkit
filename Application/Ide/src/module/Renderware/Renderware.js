/*
    Thx to MAJEST1C_R3, Allen, Ermaccer and any other guys out there

    Chunk source: https://github.com/DanielSant0s/RWParser/tree/0ceb6752ed86cbf0299eac092b2480819039dda4
    Thanks for older stuff: https://github.com/kabbi/zanzarah-tools/blob/c1862c483dfa84783761273a87a0b334ba2ea705/bsp-parser.coffee

    Maybe helpful
        https://github.com/sigmaco/rwsrc-v37-pc/tree/master/core/src

        XBOX renderware stuff: https://github.com/aap/rwtools/blob/master/src/xboxnative.cpp
        GTA Wiki: https://gtamods.com/wiki/List_of_RW_section_IDs

        Anim Animation: https://gtamods.com/wiki/Anim_Animation_(RW_Section)
        Material effect: https://gtamods.com/wiki/Material_Effects_PLG_(RW_Section)

        https://github.com/leeao/Noesis-Plugins/blob/6a0447bb9369efdbca95111b38155f1263ec5fb2/Model/fmt_RenderWare_PS2_PC.py

 */



import Dummy from './Chunk/Dummy.js'

import AtomicSect from './Chunk/AtomicSect.js'
import Atomic from "./Chunk/Atomic.js";
import BinMesh from "./Chunk/BinMesh.js";
import TextureNative from './Chunk/TextureNative.js'
import TexDictionary from './Chunk/TexDictionary.js'
import FrameList from "./Chunk/FrameList.js";
import PlaneSect from "./Chunk/PlaneSect.js";
import Frame from "./Chunk/Frame.js";
import Toc from "./Chunk/Toc.js";
import HAnim from "./Chunk/HAnim.js";
import HAnimPlugin from "./Chunk/HAnimPlugin.js";
import Geometry from "./Chunk/Geometry.js";
import MatList from "./Chunk/MatList.js";
import Material from "./Chunk/Material.js";
import Texture from "./Chunk/Texture.js";
import RwString from "./Chunk/RwString.js";
import Skin from "./Chunk/Skin.js";
import Clump from "./Chunk/Clump.js";
import GeometryList from "./Chunk/GeometryList.js";
import Extension from "./Chunk/Extension.js";
import ReflectionMat from "./Chunk/ReflectionMat.js";
import World from "./Chunk/World.js";
import CollisPlugin from "./Chunk/CollisPlugin.js";
import MaterialEffects from "./Chunk/MaterialEffects.js";
import RightToRender from "./Chunk/RightToRender.js";
import VertexFormat from "./Chunk/VertexFormat.js";
import Chunk from "./Chunk/Chunk.js";

import Helper from './../../Helper.js'
import PiTexDictionary from "./Chunk/PiTexDictionary.js";
import ChunkGroupStart from "./Chunk/ChunkGroupStart.js";
import ChunkGroupEnd from "./Chunk/ChunkGroupEnd.js";
import PrtStdPlugin from "./Chunk/PrtStdPlugin.js";
import Image from "./Chunk/Image.js";
import UserDataPlugin from "./Chunk/UserDataPlugin.js";
const assert = Helper.assert;

export default class Renderware{

    static WORLDFLAGS = {
        rpWORLDTRISTRIP:				0x00000001, // This world's meshes can be rendered as tri strips
        rpWORLDPOSITIONS:				0x00000002, // This world has positions
        rpWORLDTEXTURED:				0x00000004, // This world has only one set of texture coordinates
        rpWORLDPRELIT:					0x00000008, // This world has luminance values
        rpWORLDNORMALS:					0x00000010, // This world has normals
        rpWORLDLIGHT:					0x00000020, // This world will be lit
        rpWORLDMODULATEMATERIALCOLOR:	0x00000040, // Modulate material color with vertex colors (pre-lit + lit)
        rpWORLDTEXTURED2:				0x00000080, // This world has 2 or more sets of texture coordinates
        rpWORLDNATIVE:					0x01000000,
        rpWORLDNATIVEINSTANCE:			0x02000000,
        rpWORLDSECTORSOVERLAP:			0x40000000, // Whether to store both vals, or only one
    };

    static NORMALSCALE = (1.0/128.0);
    static VERTSCALE1 = (1.0/128.0);
    static VERTSCALE2 = (1.0/1024.0);
    static UVSCALE = (1.0/4096.0);
    static FACETYPE_STRIP = 0x1;
    static FACETYPE_LIST = 0x0;

    static PLATFORM_OGL = 2;
    static PLATFORM_PS2    = 4;
    static PLATFORM_XBOX   = 5;
    static PLATFORM_D3D8   = 8;
    static PLATFORM_D3D9   = 9;
    static PLATFORM_PS2FOURCC = 0x00325350; /* "PS2\0" */

    static RASTER_DEFAULT = 0x0000;
    static RASTER_1555 = 0x0100;
    static RASTER_565 = 0x0200;
    static RASTER_4444 = 0x0300;
    static RASTER_LUM8 = 0x0400;
    static RASTER_8888 = 0x0500;
    static RASTER_888 = 0x0600;
    static RASTER_16 = 0x0700;
    static RASTER_24 = 0x0800;
    static RASTER_32 = 0x0900;
    static RASTER_555 = 0x0a00;

    // static RASTER_AUTOMIPMAP = 0x1000;
    static RASTER_PAL8 = 0x2000;
    static RASTER_PAL4 = 0x4000;
    // static RASTER_MIPMAP = 0x8000;
    // static RASTER_MASK = 0x0F00;

    static CHUNK_AUDIOCONTAINER  = 0x0000080d;
    static CHUNK_AUDIOHEADER     = 0x0000080e;
    static CHUNK_AUDIODATA       = 0x0000080f;
    static CHUNK_NAOBJECT = 0x0;
    static CHUNK_STRUCT = 0x1;
    static CHUNK_STRING = 0x2;
    static CHUNK_EXTENSION = 0x3;
    static CHUNK_CAMERA = 0x5;
    static CHUNK_TEXTURE = 0x6;
    static CHUNK_MATERIAL = 0x7;
    static CHUNK_MATLIST = 0x8;
    static CHUNK_ATOMICSECT = 0x9;
    static CHUNK_PLANESECT = 0xA;
    static CHUNK_WORLD = 0xB;
    static CHUNK_SPLINE = 0xC;
    static CHUNK_MATRIX = 0xD;
    static CHUNK_FRAMELIST = 0xE;
    static CHUNK_GEOMETRY = 0xF;
    static CHUNK_CLUMP = 0x10;
    static CHUNK_LIGHT = 0x12;
    static CHUNK_UNICODESTRING = 0x13;
    static CHUNK_ATOMIC = 0x14;
    static CHUNK_TEXTURENATIVE = 0x15;
    static CHUNK_TEXDICTIONARY = 0x16;
    static CHUNK_ANIMDATABASE = 0x17;
    static CHUNK_IMAGE = 0x18;
    static CHUNK_SKINANIMATION = 0x19;
    static CHUNK_GEOMETRYLIST = 0x1A;
    static CHUNK_ANIMANIMATION = 0x1B;
    static CHUNK_TEAM = 0x1C;
    static CHUNK_CROWD = 0x1D;
    static CHUNK_RIGHTTORENDER = 0x1F;
    static CHUNK_MTEFFECTNATIVE = 0x20;
    static CHUNK_MTEFFECTDICT = 0x21;
    static CHUNK_TEAMDICTIONARY = 0x22;
    static CHUNK_PITEXDICTIONARY = 0x23;
    static CHUNK_TOC = 0x24;
    static CHUNK_PRTSTDGLOBALDATA = 0x25;
    static CHUNK_ALTPIPE = 0x26;
    static CHUNK_PIPEDS = 0x27;
    static CHUNK_PATCHMESH = 0x28;
    static CHUNK_CHUNKGROUPSTART = 0x29;
    static CHUNK_CHUNKGROUPEND = 0x2A;
    static CHUNK_UVANIMDICT = 0x2B;
    static CHUNK_COLLTREE = 0x2C;
    static CHUNK_ENVIRONMENT = 0x2D;
    static CHUNK_COREPLUGINIDMAX = 0x2E;
    static CHUNK_METRICSPLUGIN = 0x101;
    static CHUNK_SPLINEPLUGIN = 0x102;
    static CHUNK_STEREOPLUGIN = 0x103;
    static CHUNK_VRMLPLG = 0x104;
    static CHUNK_MORPH = 0x105;
    static CHUNK_PVSPLUGIN = 0x106;
    static CHUNK_MEMLEAKPLUGIN = 0x107;
    static CHUNK_ANIMPLUGIN = 0x108;
    static CHUNK_GLOSSPLUGIN = 0x109;
    static CHUNK_LOGOPLUGIN = 0x10a;
    static CHUNK_MEMINFOPLUGIN = 0x10b;
    static CHUNK_RANDOMPLUGIN = 0x10c;
    static CHUNK_PNGIMAGEPLUGIN = 0x10d;
    static CHUNK_BONEPLUGIN = 0x10e;
    static CHUNK_VRMLANIMPLUGIN = 0x10f;
    static CHUNK_SKYMIPMAP = 0x110;
    static CHUNK_MRMPLUGIN = 0x111;
    static CHUNK_LODATMPLUGIN = 0x112;
    static CHUNK_MEPLUGIN = 0x113;
    static CHUNK_LTMAPPLUGIN = 0x114;
    static CHUNK_REFINEPLUGIN = 0x115;
    static CHUNK_SKIN = 0x116;
    static CHUNK_LABELPLUGIN = 0x117;
    static CHUNK_PARTICLES = 0x118;
    static CHUNK_GEOMTXPLUGIN = 0X119;
    static CHUNK_SYNTHCOREPLUGIN = 0X11a;
    static CHUNK_STQPPPLUGIN = 0X11b;
    static CHUNK_PARTPPPLUGIN = 0X11c;
    static CHUNK_COLLISPLUGIN = 0X11d;
    static CHUNK_HANIM = 0X11e;
    static CHUNK_USERDATAPLUGIN = 0X11f;
    static CHUNK_MATERIALEFFECTS = 0x120;
    static CHUNK_PARTICLESYSTEMPLUGIN = 0X121;
    static CHUNK_DMORPHPLUGIN = 0x122;
    static CHUNK_PATCHPLUGIN = 0x123;
    static CHUNK_TEAMPLUGIN = 0x124;
    static CHUNK_CROWDPPPLUGIN = 0x125;
    static CHUNK_MIPSPLITPLUGIN = 0x126;
    static CHUNK_ANISOTPLUGIN = 0x127;
    static CHUNK_GCNMATPLUGIN = 0x129;
    static CHUNK_GPVSPLUGIN = 0x12a;
    static CHUNK_XBOXMATPLUGIN = 0x12b;
    static CHUNK_MULTITEXPLUGIN = 0x12c;
    static CHUNK_CHAINPLUGIN = 0x12d;
    static CHUNK_TOONPLUGIN = 0x12e;
    static CHUNK_PTANKPLUGIN = 0x12f;
    static CHUNK_PRTSTDPLUGIN = 0x130;
    static CHUNK_PDSPLUGIN = 0x131;
    static CHUNK_PRTADVPLUGIN = 0x132;
    static CHUNK_NORMMAPPLUGIN = 0x133;
    static CHUNK_ADCPLUGIN = 0x134;
    static CHUNK_UVANIMPLUGIN = 0x135;
    static CHUNK_CHARSEPLUGIN = 0x180;
    static CHUNK_NOHSWORLDPLUGIN = 0x181;
    static CHUNK_IMPUTILPLUGIN = 0x182;
    static CHUNK_SLERPPLUGIN = 0x183;
    static CHUNK_OPTIMPLUGIN = 0x184;
    static CHUNK_TLWORLDPLUGIN = 0x185;
    static CHUNK_DATABASEPLUGIN = 0x186;
    static CHUNK_RAYTRACEPLUGIN = 0x187;
    static CHUNK_RAYPLUGIN = 0x188;
    static CHUNK_LIBRARYPLUGIN = 0x189;
    static CHUNK__2DPLUGIN = 0x190;
    static CHUNK_TILERENDPLUGIN = 0x191;
    static CHUNK_JPEGIMAGEPLUGIN = 0x192;
    static CHUNK_TGAIMAGEPLUGIN = 0x193;
    static CHUNK_GIFIMAGEPLUGIN = 0x194;
    static CHUNK_QUATPLUGIN = 0x195;
    static CHUNK_SPLINEPVSPLUGIN = 0x196;
    static CHUNK_MIPMAPPLUGIN = 0x197;
    static CHUNK_MIPMAPKPLUGIN = 0x198;
    static CHUNK__2DFONT = 0x199;
    static CHUNK_INTSECPLUGIN = 0x19a;
    static CHUNK_TIFFIMAGEPLUGIN = 0x19b;
    static CHUNK_PICKPLUGIN = 0x19c;
    static CHUNK_BMPIMAGEPLUGIN = 0x19d;
    static CHUNK_RASIMAGEPLUGIN = 0x19e;
    static CHUNK_SKINFXPLUGIN = 0x19f;
    static CHUNK_VCATPLUGIN = 0x1a0;
    static CHUNK__2DPATH = 0x1a1;
    static CHUNK__2DBRUSH = 0x1a2;
    static CHUNK__2DOBJECT = 0x1a3;
    static CHUNK__2DSHAPE = 0x1a4;
    static CHUNK__2DSCENE = 0x1a5;
    static CHUNK__2DPICKREGION = 0x1a6;
    static CHUNK__2DOBJECTSTRING = 0x1a7;
    static CHUNK__2DANIMPLUGIN = 0x1a8;
    static CHUNK__2DANIM = 0x1a9;
    static CHUNK__2DKEYFRAME = 0x1b0;
    static CHUNK__2DMAESTRO = 0x1b1;
    static CHUNK_BARYCENTRIC = 0x1b2;
    static CHUNK_PITEXDICTIONARYTK = 0x1b3;
    static CHUNK_TOCTOOLKIT = 0x1b4;
    static CHUNK_TPLTOOLKIT = 0x1b5;
    static CHUNK_ALTPIPETOOLKIT = 0x1b6;
    static CHUNK_ANIMTOOLKIT = 0x1b7;
    static CHUNK_SKINSPLITTOOKIT = 0x1b8;
    static CHUNK_CMPKEYTOOLKIT = 0x1b9;
    static CHUNK_GEOMCONDPLUGIN = 0x1ba;
    static CHUNK_WINGPLUGIN = 0x1bb;
    static CHUNK_GENCPIPETOOLKIT = 0x1bc;
    static CHUNK_LTMAPCNVTOOLKIT = 0x1bd;
    static CHUNK_FILESYSTEMPLUGIN = 0x1be;
    static CHUNK_DICTTOOLKIT = 0x1bf;
    static CHUNK_UVANIMLINEAR = 0x1c0;
    static CHUNK_UVANIMPARAM = 0x1c1;
    static CHUNK_BINMESH = 0x50E;
    // static CHUNK_NATIVEDATA = 0x510;
    static CHUNK_VERTEXFORMAT = 0x510;
    static CHUNK_SCRIPT = 0x704;
    static CHUNK_ASSET = 0x716;
    static CHUNK_CONTAINER = 0x71C;
    static CHUNK_PIPELINESET = 0x253F2F3;
    static CHUNK_SPECULARMAT = 0x253F2F6;
    static CHUNK_2DFX = 0x253F2F8;
    static CHUNK_NIGHTVERTEXCOLOR = 0x253F2F9;
    static CHUNK_COLLISIONMODEL = 0x253F2FA;
    static CHUNK_REFLECTIONMAT = 0x253F2FC;
    static CHUNK_MESHEXTENSION = 0x253F2FD;
    static CHUNK_FRAME = 0x253F2FE;

    static CHUNK_UNK809 = 0x809;
    static CHUNK_UNKA01 = 0xA01;
    static CHUNK_UNK80A = 0x80A; //Audio Settings ?
    static CHUNK_UNK80C = 0x80C; //Audio List
    static CHUNK_UNK802 = 0x802; //Audio Entry
    static CHUNK_UNK803 = 0x803; //Audio Entry Settings
    static CHUNK_UNK804 = 0x804; //Audio Data

    // static FLAGS_TRISTRIP   = 0x01;
    // static FLAGS_POSITIONS  = 0x02;
    // static FLAGS_TEXTURED   = 0x04;
    // static FLAGS_PRELIT     = 0x08;
    static FLAGS_NORMALS    = 0x10;
    // static FLAGS_LIGHT      = 0x20;
    // static FLAGS_MODULATEMATERIALCOLOR  = 0x40;
    // static FLAGS_TEXTURED2  = 0x80;



    // static rpGEOMETRYPOSITIONS = 0x00000002;
    /**<This geometry has positions */
    static rpGEOMETRYTEXTURED = 0x00000004;
    /**<This geometry has only one set of
     texture coordinates. Texture
     coordinates are specified on a per
     vertex basis */
    static rpGEOMETRYPRELIT = 0x00000008;
    /**<This geometry has pre-light colors */
    // static rpGEOMETRYNORMALS = 0x00000010;
    /**<This geometry has vertex normals */

    static rpGEOMETRYTEXTURED2 = 0x00000080;
    /**<This geometry has at least 2 sets of
     texture coordinates. */


    static handler = {
        [Renderware.CHUNK_NAOBJECT]        : Dummy,        //just skip
        [Renderware.CHUNK_GPVSPLUGIN]      : Dummy,        //unkown data
        [Renderware.CHUNK_STRUCT]          : Dummy,
        [Renderware.CHUNK_COLLISPLUGIN]    : CollisPlugin, //TODO
        [Renderware.CHUNK_ATOMICSECT]      : AtomicSect,
        [Renderware.CHUNK_ATOMIC]          : Atomic,
        [Renderware.CHUNK_RIGHTTORENDER]   : RightToRender,
        [Renderware.CHUNK_BINMESH]         : BinMesh,
        [Renderware.CHUNK_TEXTURENATIVE]   : TextureNative,
        [Renderware.CHUNK_TEXDICTIONARY]   : TexDictionary,
        [Renderware.CHUNK_FRAMELIST]       : FrameList,
        [Renderware.CHUNK_PLANESECT]       : PlaneSect,
        [Renderware.CHUNK_FRAME]           : Frame,
        [Renderware.CHUNK_TOC]             : Toc,
        [Renderware.CHUNK_HANIM]           : HAnim,
        // [Renderware.CHUNK_HANIMPLUGIN]     : HAnimPlugin,
        [Renderware.CHUNK_GEOMETRY]        : Geometry,
        [Renderware.CHUNK_MATLIST]         : MatList,
        [Renderware.CHUNK_MATERIAL]        : Material,
        [Renderware.CHUNK_MATERIALEFFECTS] : MaterialEffects,
        [Renderware.CHUNK_TEXTURE]         : Texture,
        [Renderware.CHUNK_STRING]          : RwString,
        [Renderware.CHUNK_SKIN]            : Skin,
        [Renderware.CHUNK_CLUMP]           : Clump,
        [Renderware.CHUNK_GEOMETRYLIST]    : GeometryList,
        [Renderware.CHUNK_EXTENSION]       : Extension,
        [Renderware.CHUNK_REFLECTIONMAT]   : ReflectionMat,
        [Renderware.CHUNK_WORLD]           : World,


        [Renderware.CHUNK_IMAGE]           : Image,
        [Renderware.CHUNK_PRTSTDPLUGIN]           : PrtStdPlugin,
        [Renderware.CHUNK_CHUNKGROUPSTART]           : ChunkGroupStart,
        [Renderware.CHUNK_CHUNKGROUPEND]           : ChunkGroupEnd,
        [Renderware.CHUNK_PITEXDICTIONARY]           : PiTexDictionary,
        [Renderware.CHUNK_VERTEXFORMAT]           : VertexFormat,
        [Renderware.CHUNK_USERDATAPLUGIN]           : UserDataPlugin,
        [Renderware.CHUNK_SKYMIPMAP]           : Dummy,
        [Renderware.CHUNK_DMORPHPLUGIN]           : Dummy,
        [Renderware.CHUNK_UVANIMPLUGIN]           : Dummy,
        [Renderware.CHUNK_LIGHT]           : Dummy,
        [Renderware.CHUNK_UNK809]           : Dummy,
        [Renderware.CHUNK_UNKA01]           : Dummy,
        [Renderware.CHUNK_UNK80A]           : Dummy,
        [Renderware.CHUNK_UNK80C]           : Dummy,
        [Renderware.CHUNK_UNK802]           : Dummy,
        [Renderware.CHUNK_UNK803]           : Dummy,
        [Renderware.CHUNK_UNK804]           : Dummy,
    };

    static getChunkNameById(chunkId){

        for(let i in Renderware){
            if (i.indexOf('CHUNK_') !== 0) continue;

            if (Renderware[i] === chunkId)
                return i;

        }

        return false;

    }

    /**
     *
     * @param chunk {Chunk}
     * @param type
     * @returns {boolean}|{Chunk}
     */
    static findChunk(chunk, type) {
        let found = Renderware.findChunks(chunk, type);
        if (found.length === 0) return false;

        if (found.length > 1){
            console.error("Tried to get exact one chunk with type " + type + " but multiple found!");
            debugger;
        }

        return found[0] || false;
    }

    /**
     *
     * @param chunk {Chunk}
     * @param type
     * @returns {[]}
     */
    static findChunks(chunk, type) {
        let found = [];
        chunk.result.chunks.forEach(function (_chunk) {
            if (_chunk.type === type) found.push(_chunk);
        });

        return found;
    }

    /**
     *
     * @param binary {NBinary}
     * @param rootData {Object}
     * @returns {Chunk}
     */
    static parse(binary, rootData){
        let chunk = Renderware.processChunk(binary, rootData);
        chunk.parse();
        return chunk;
    }

    static fixChunkHeaderSize(header, binary){

        /**
         * Fix Chunk sizes
         */
        {
            // a chunk block could be smaller as the given size...
            if (header.size > binary.remain())
                header.size = binary.remain();

            //some chunks sizes are too long... we need to validate it
            if (header.size > 0){
                let currentStart = binary.current();
                binary.setCurrent(binary.current() + header.size);

                //we have space left - at least enough for bytes for the next header
                let lookupDeep = 4;
                if (binary.remain() >= lookupDeep * 4){

                    while(lookupDeep--){

                        if (header.version !== binary.consume(4, 'uint32'))
                            continue;

                        if (binary.current() - 12 - currentStart !== header.size){
                            header.oriSize = header.size;
                            console.log("adjust ",header.id, header.size, "to", binary.current() - 12 - currentStart );
                            // debugger;
                        }

                        header.size = binary.current() - 12 - currentStart;
                        break;
                    }
                }

                binary.setCurrent(currentStart);
            }
        }


    }
    
    static parseHeader(binary){
        let header = {
            id: binary.consume(4, 'int32'),
            size: binary.consume(4, 'uint32'),
            version: binary.consume(4, 'uint32')
        };

        Renderware.fixChunkHeaderSize(header, binary);

        return header;
    }

    /**
     *
     * @param binary {NBinary}
     * @param rootData {Object}
     * @returns {Chunk}
     */
    static processChunk( binary, rootData) {
        rootData = rootData || {
            materials: [],
            textures: [],
            skins: [],
            frames: [],
            frameNames: [],
            geometries: [],
            atomics: []
        };

        let header = Renderware.parseHeader(binary);

        assert(typeof Renderware.handler[header.id], "function", "Chunk function not found for ID " + header.id);

        header.typeName = Renderware.handler[header.id].name;

        /**
         * Happens in Tony Hawks 3 PC Demo
         * Renderware Version (int32): 784
         */
        if (header.size > binary.remain())
            header.size = binary.remain();

        let data = binary.consume(header.size, 'nbinary');

        return new Renderware.handler[header.id](data, header, rootData);
    }


    static getVersion( version ) {
        return ((version & 4294901760) !== 0 ? ((((version >>> 14) & 261888) + 196608) | ((version >>> 16) & 63)) : (version << 8));
    }

    /**
     * 
     * @param binary {NBinary}
     * @returns {[]}
     */
    static readClumpList(binary) {

        function readUserDataPLG(binary, index) {
            let numSet = binary.consume(4, 'int32');
            let boneName = "bone" + index;

            for (let i = 0; i < numSet; i++) {
                let typeNameLen = binary.consume(4, 'int32');
                binary.seek(typeNameLen);
                binary.seek(8); //u2 + u3

                let nameLen = binary.consume(4, 'int32');
                if (nameLen > 0)
                    boneName = binary.consume(nameLen, 'nbinary').getString(0);
            }

            return boneName;
        }

        function readExtension(header){
            let name = null;
            let endOfs = binary.current() + header.size;
            while (binary.current() < endOfs) {
                let sHeader = Renderware.parseHeader(binary);
                if (sHeader.id === Renderware.CHUNK_FRAME) {
                    name = binary.consume(sHeader.size, 'nbinary').getString(0);
                } else if (sHeader.id === Renderware.CHUNK_USERDATAPLUGIN) {
                    name = readUserDataPLG(binary, 1);
                } else {

                    binary.seek(sHeader.size);
                }
            }

            return name;
        }

        function findName(){
            let header = Renderware.parseHeader(binary);
            switch (header.id) {
                case Renderware.CHUNK_FRAME:
                    return binary.consume(header.size, 'nbinary').getString(0);
                case Renderware.CHUNK_USERDATAPLUGIN:
                    return readUserDataPLG(binary,1);
                case Renderware.CHUNK_HANIM:
                    binary.seek(12);
                    return findName();
                case Renderware.CHUNK_EXTENSION:
                    return readExtension(header);
            }

            return "";
        }

        let entries = [];



        let count = 1;
        while (binary.current() < binary.length()) {
            let offset = binary.current();

            //CHUNK_CLUMP
            let clumpChunk = Renderware.parseHeader(binary);
            let next = binary.current() + clumpChunk.size;
            let name = "Unk_" + offset;

            if (clumpChunk.version === 469893130) {



            }else if (clumpChunk.version === 784){
                next += 3*4; //BAAD HACK TODO

                //CHUNK_STRUCT
                // let clumpStruct = Renderware.parseHeader(binary);
                // binary.seek(clumpStruct.size);
                //
                // let frameListStruct = Renderware.parseHeader(binary);
                // binary.seek(frameListStruct.size);
                //
                // let frameListDataStruct = Renderware.parseHeader(binary);
                // binary.seek(frameListStruct.size);
                //
                // binary.seek(12); // extheader
                //
                // let name = findName();

            }else{
                //CHUNK_STRUCT
                let clumpStruct = Renderware.parseHeader(binary);
                binary.seek(clumpStruct.size);

                binary.seek(12);

                let frameListStructHeader = Renderware.parseHeader(binary);
                binary.seek(frameListStructHeader.size);
                binary.seek(12); // extheader

                name = findName();
            }

            if (name !== ""){
                (function (offset, name) {
                    entries.push({
                        name: name,
                        offset: offset,
                        data: function(){
                            let mesh = Renderware.getModel(binary, offset);
                            mesh.name = name;
                            return mesh;
                        }
                    });
                })(offset, name);
            }else{
                console.warn("this model has no name! offset: ", offset);
            }

            binary.setCurrent(next);
            count++;
        }

        return entries;
    }


    static getMap(nBinary, level){
        nBinary.setCurrent(0);

        while(nBinary.remain() > 0){
            let tree = Renderware.parse(nBinary);
            if (tree.type !== Renderware.CHUNK_WORLD)
                continue;


            let normalizedMesh = (new NormalizeMap(tree)).normalize();
            normalizedMesh.name = "TODO";

            let mesh = generateMesh(level._storage.tex, normalizedMesh);
            mesh.children.forEach(function (subMesh) {
                subMesh.visible = true;
            });


            return mesh;
        }

        return false;
    }

    static getAnimation(nBinary, level){
        nBinary.setCurrent(0);

        let tree = RW.parser(nBinary).parse();
        return RW.convert.animation(tree, level);
    }

    static getModel(nBinary, offset) {
        console.log("moodel at", offset);
        nBinary.setCurrent(offset);
        let tree = Renderware.parse(nBinary);
        // console.log("Model Tree", tree);
        return (new NormalizeModel(tree)).normalize();
    }

    static getTextures(nBinary) {

        let tree = Renderware.parse(nBinary);
        return (new NormalizeTexture(tree)).normalize();
    }

}
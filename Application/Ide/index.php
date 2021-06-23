<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Manhunt Studio</title>

    <link href="coreUi/style.css" rel="stylesheet">

    <meta name="robots" content="noindex">


    <script>
        window.MANHUNT = {

            ObjectAnimation: {},
            resources: {
                mh1: {},
                mh2: {},
            },
            studio: {},
            config: {},
            init: [],
            scene: {},
            relation: {},
            control: {},
            loader: {},
            parser: {},
            states: {},
            fileLoader: {},
            converter: {},
            entity: {},
            levelScript: {},
            storage: {},
            frontend: {
                modal: {}
            },
            sidebar: {
                view: {},
                menu: {},
                elements: {}
            }
        };

        MANHUNT.fov = 57.29578; //Default MH2 FOV

    </script>

    <script src="src/Library/jquery-3.5.1.min.js"></script>
    <script src="src/Library/deflate.min.js"></script>
    <script src="src/Library/inflate.min.js"></script>

    <link href="src/Library/select2.min.css" rel="stylesheet"/>
    <script src="src/Library/select2.min.js"></script>

    <script>

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

        let CHUNK_ID_NAME = {

            0x0: "CHUNK_NAOBJECT",
            0x1: "CHUNK_STRUCT",
            0x2: "CHUNK_STRING",
            0x3: "CHUNK_EXTENSION",
            0x5: "CHUNK_CAMERA",
            0x6: "CHUNK_TEXTURE",
            0x7: "CHUNK_MATERIAL",
            0x8: "CHUNK_MATLIST",
            0x9: "CHUNK_ATOMICSECT",
            0xA: "CHUNK_PLANESECT",
            0xB: "CHUNK_WORLD",
            0xC: "CHUNK_SPLINE",
            0xD: "CHUNK_MATRIX",
            0xE: "CHUNK_FRAMELIST",
            0xF: "CHUNK_GEOMETRY",
            0x10: "CHUNK_CLUMP",
            0x12: "CHUNK_LIGHT",
            0x13: "CHUNK_UNICODESTRING",
            0x14: "CHUNK_ATOMIC",
            0x15: "CHUNK_TEXTURENATIVE",
            0x16: "CHUNK_TEXDICTIONARY",
            0x17: "CHUNK_ANIMDATABASE",
            0x18: "CHUNK_IMAGE",
            0x19: "CHUNK_SKINANIMATION",
            0x1A: "CHUNK_GEOMETRYLIST",
            0x1B: "CHUNK_HANIMANIMATION",
            0x1C: "CHUNK_TEAM",
            0x1D: "CHUNK_CROWD",
            0x1F: "CHUNK_RIGHTTORENDER",
            0x20: "CHUNK_MTEFFECTNATIVE",
            0x21: "CHUNK_MTEFFECTDICT",
            0x22: "CHUNK_TEAMDICTIONARY",
            0x23: "CHUNK_PITEXDICTIONARY",
            0x24: "CHUNK_TOC",
            0x25: "CHUNK_PRTSTDGLOBALDATA",
            0x26: "CHUNK_ALTPIPE",
            0x27: "CHUNK_PIPEDS",
            0x28: "CHUNK_PATCHMESH",
            0x29: "CHUNK_CHUNKGROUPSTART",
            0x2A: "CHUNK_CHUNKGROUPEND",
            0x2B: "CHUNK_UVANIMDICT",
            0x2C: "CHUNK_COLLTREE",
            0x2D: "CHUNK_ENVIRONMENT",
            0x2E: "CHUNK_COREPLUGINIDMAX",

            0x105: "CHUNK_MORPH",
            0x110: "CHUNK_SKYMIPMAP",
            0x116: "CHUNK_SKIN",
            0x118: "CHUNK_PARTICLES",
            0x11E: "CHUNK_HANIM",
            0x120: "CHUNK_MATERIALEFFECTS",
            0x131: "CHUNK_PDSPLG",
            0x134: "CHUNK_ADCPLG",
            0x135: "CHUNK_UVANIMPLG",
            0x50E: "CHUNK_BINMESH",
            0x510: "CHUNK_VERTEXFORMAT",

            0x253F2F3: "CHUNK_PIPELINESET",
            0x253F2F6: "CHUNK_SPECULARMAT",
            0x253F2F8: "CHUNK_2DFX",
            0x253F2F9: "CHUNK_NIGHTVERTEXCOLOR",
            0x253F2FA: "CHUNK_COLLISIONMODEL",
            0x253F2FC: "CHUNK_REFLECTIONMAT",
            0x253F2FD: "CHUNK_MESHEXTENSION",
            0x253F2FE: "CHUNK_FRAME",

        };

        const PLATFORM_OGL = 2;
        const PLATFORM_PS2    = 4;
        const PLATFORM_XBOX   = 5;
        const PLATFORM_D3D8   = 8;
        const PLATFORM_D3D9   = 9;
        const PLATFORM_PS2FOURCC = 0x00325350; /* "PS2\0" */

        const CHUNK_AUDIOCONTAINER  = 0x0000080d;
        const CHUNK_AUDIOHEADER     = 0x0000080e;
        const CHUNK_AUDIODATA       = 0x0000080f;
        const CHUNK_NAOBJECT        = 0x0;
        const CHUNK_STRUCT          = 0x1;
        const CHUNK_STRING          = 0x2;
        const CHUNK_EXTENSION       = 0x3;
        const CHUNK_CAMERA          = 0x5;
        const CHUNK_TEXTURE         = 0x6;
        const CHUNK_MATERIAL        = 0x7;
        const CHUNK_MATLIST         = 0x8;
        const CHUNK_ATOMICSECT      = 0x9;
        const CHUNK_PLANESECT       = 0xA;
        const CHUNK_WORLD           = 0xB;
        const CHUNK_SPLINE          = 0xC;
        const CHUNK_MATRIX          = 0xD;
        const CHUNK_FRAMELIST       = 0xE;
        const CHUNK_GEOMETRY        = 0xF;
        const CHUNK_CLUMP           = 0x10;
        const CHUNK_LIGHT           = 0x12;
        const CHUNK_UNICODESTRING   = 0x13;
        const CHUNK_ATOMIC          = 0x14;
        const CHUNK_TEXTURENATIVE   = 0x15;
        const CHUNK_TEXDICTIONARY   = 0x16;
        const CHUNK_ANIMDATABASE    = 0x17;
        const CHUNK_IMAGE           = 0x18;
        const CHUNK_SKINANIMATION   = 0x19;
        const CHUNK_GEOMETRYLIST    = 0x1A;
        const CHUNK_ANIMANIMATION   = 0x1B;
        const CHUNK_HANIMANIMATION  = 0x1B;
        const CHUNK_TEAM            = 0x1C;
        const CHUNK_CROWD           = 0x1D;
        const CHUNK_RIGHTTORENDER   = 0x1F;
        const CHUNK_MTEFFECTNATIVE  = 0x20;
        const CHUNK_MTEFFECTDICT    = 0x21;
        const CHUNK_TEAMDICTIONARY  = 0x22;
        const CHUNK_PITEXDICTIONARY = 0x23;
        const CHUNK_TOC             = 0x24;
        const CHUNK_PRTSTDGLOBALDATA = 0x25;
        const CHUNK_ALTPIPE         = 0x26;
        const CHUNK_PIPEDS          = 0x27;
        const CHUNK_PATCHMESH       = 0x28;
        const CHUNK_CHUNKGROUPSTART = 0x29;
        const CHUNK_CHUNKGROUPEND   = 0x2A;
        const CHUNK_UVANIMDICT      = 0x2B;
        const CHUNK_COLLTREE        = 0x2C;
        const CHUNK_ENVIRONMENT     = 0x2D;
        const CHUNK_COREPLUGINIDMAX = 0x2E;

        const CHUNK_MORPH           = 0x105;
        const CHUNK_SKYMIPMAP       = 0x110;
        const CHUNK_SKIN            = 0x116;
        const CHUNK_PARTICLES       = 0x118;
        const CHUNK_HANIM           = 0x11E;
        const CHUNK_MATERIALEFFECTS = 0x120;
        const CHUNK_PDSPLG          = 0x131;
        const CHUNK_ADCPLG          = 0x134;
        const CHUNK_UVANIMPLG       = 0x135;
        const CHUNK_BINMESH         = 0x50E;
        const CHUNK_NATIVEDATA      = 0x510;
        const CHUNK_VERTEXFORMAT    = 0x510;

        const CHUNK_PIPELINESET      = 0x253F2F3;
        const CHUNK_SPECULARMAT      = 0x253F2F6;
        const CHUNK_2DFX             = 0x253F2F8;
        const CHUNK_NIGHTVERTEXCOLOR = 0x253F2F9;
        const CHUNK_COLLISIONMODEL   = 0x253F2FA;
        const CHUNK_REFLECTIONMAT    = 0x253F2FC;
        const CHUNK_MESHEXTENSION    = 0x253F2FD;
        const CHUNK_FRAME            = 0x253F2FE;

        const FLAGS_TRISTRIP   = 0x01;
        const FLAGS_POSITIONS  = 0x02;
        const FLAGS_TEXTURED   = 0x04;
        const FLAGS_PRELIT     = 0x08;
        const FLAGS_NORMALS    = 0x10;
        const FLAGS_LIGHT      = 0x20;
        const FLAGS_MODULATEMATERIALCOLOR  = 0x40;
        const FLAGS_TEXTURED2  = 0x80;


        function assert(a, b, msg){
            if (a !== b){
                console.error((msg || ('Expect ' + CHUNK_ID_NAME[b] + ' got ' + CHUNK_ID_NAME[a])) );
                die;
            }
        }
    </script>

    <script type="module">
        import {TransformControls} from './src/Library/TransformControls.js';
        import {OrbitControls} from './src/Library/OrbitControls.js';
        import {DDSLoader} from './src/Library/Loader/three.dds.loader.js';
        import {FlyControls} from './src/Library/FlyControls.js';

        import  Renderware from './src/module/Renderware/Renderware.js'
        import  NormalizeMap from './src/module/Renderware/Three/map.js'
        import  NormalizeModel from './src/module/Renderware/Three/model.js'
        import  NormalizeTexture from './src/module/Renderware/Three/texture.js'
        import  Scan from './src/module/Renderware/Utils/Scan.js'
        import  generateMesh from './src/module/Three/generateMesh.js'
        import  Relation from './src/Relation.js'
        import  ObjectAnimation from './src/ObjectAnimation.js'
        import  Tab from './src/Frontend/Tab.js'
        import  Studio from './src/Studio.js'
        import  Player from './src/Entity/Player.js'
        import  Light from './src/Entity/Light.js'
        import  Hunter from './src/Entity/Hunter.js'
        import  Regular from './src/Entity/Regular.js'
        import  Trigger from './src/Entity/Trigger.js'
        import  Api from './src/Api.js'
        import  Config from './src/Config.js'
        import  Playstation from './src/Library/Texture/Playstation.js'

        window.DDSLoader = DDSLoader;
        window.FlyControls = FlyControls;
        window.OrbitControls = OrbitControls;
        window.TransformControls = TransformControls;
        window.ObjectAnimation = ObjectAnimation;
        window.Tab = Tab;
        window.Studio = Studio;
        window.Player = Player;
        window.Light = Light;
        window.Hunter = Hunter;
        window.Regular = Regular;
        window.Trigger = Trigger;
        window.Scan = Scan;




        window.generateMesh = generateMesh;
        window.Renderware = Renderware;
        window.NormalizeMap = NormalizeMap;
        window.NormalizeModel = NormalizeModel;
        window.NormalizeTexture = NormalizeTexture;
        window.Relation = Relation;
        window.Api = Api;
        window.Config = Config;
        window.Playstation = Playstation;



    </script>


<!--    <script src="src/Library/Renderware/Renderware.js"></script>-->

    <script src="src/Sidebar/Elements/AttributeValue.js"></script>
    <script src="src/Sidebar/Elements/InputGroup.js"></script>
    <script src="src/Sidebar/Elements/Button.js"></script>
    <script src="src/Sidebar/Elements/Dropdown.js"></script>
    <script src="src/Sidebar/View/Construct.js"></script>
    <script src="src/Sidebar/View/Xyz.js"></script>
    <script src="src/Sidebar/View/InfoBlock.js"></script>
    <script src="src/Sidebar/View/EntitySelection.js"></script>
    <script src="src/Sidebar/View/SceneSelection.js"></script>
    <script src="src/Sidebar/Section.js"></script>
    <script src="src/Sidebar/Menu.js"></script>


    <!-- Library  -->
    <script src="src/Library/three.min.js"></script>
    <script src="src/Library/NBinary.js"></script>

<!--    <script src="src/Api.js"></script>-->

    <!-- File loader  -->
    <script src="src/Library/Loader/manhunt.ifp.loader.js"></script>
    <script src="src/Library/Loader/manhunt.inst.loader.js"></script>
    <script src="src/Library/Loader/manhunt.tvp.loader.js"></script>
    <script src="src/Library/Loader/manhunt.mls.loader.js"></script>
    <script src="src/Library/Loader/manhunt.mdl.loader.js"></script>
    <script src="src/Library/Loader/manhunt.tvp.loader.js"></script>
    <script src="src/Library/Loader/manhunt.glg.loader.js"></script>
    <script src="src/Library/Loader/manhunt.bsp.loader.js"></script>
    <script src="src/Library/Loader/manhunt.tex.loader.js"></script>
    <script src="src/Loader.js"></script>

    <!-- Content Parser  -->
    <script src="src/Library/Parser/manhunt.parser.srce.trigger.js"></script>
    <script src="src/Library/Parser/mdl.parser.js"></script>
    <script src="src/Library/Parser/txd.parser.js"></script>
    <script src="src/Library/Parser/tex.parser.js"></script>
    <script src="src/Library/Parser/manhunt2.psp.bsp.parser.js"></script>
    <script src="src/Library/Parser/manhunt.ps2.txd.parser.js"></script>

    <!-- Content Converter  -->
    <script src="src/Library/Converter/dxt.rgb.converter.js"></script>
    <script src="src/Library/Converter/generic.mesh.converter.js"></script>
    <script src="src/Library/Converter/psp.bsp.mesh.converter.js"></script>
    <script src="src/Library/Converter/dds.texture.converter.js"></script>
    <script src="src/Library/Converter/ps2.texture.converter.js"></script>

    <!-- Camera -->
    <script src="src/Camera/TVP.js"></script>


    <script src="src/Resources/abstract.js"></script>
    <script src="src/Resources/manhunt.pc.js"></script>
    <script src="src/Resources/manhunt2.pc.js"></script>
    <script src="src/Resources/manhunt.ps2.v064.js"></script>
    <script src="src/Resources/manhunt2.psp.v001.js"></script>
    <script src="src/Resources/resources.js"></script>
    <script src="src/Scene/model.view.js"></script>
    <script src="src/Scene/views.js"></script>

    <!-- Entity (inst) handler -->
<!--    <script src="src/Entity/Construct.js"></script>-->
<!--    <script src="src/Entity/Entity.js"></script>-->
<!--    <script src="src/Entity/Default.js"></script>-->
<!--    <script src="src/Entity/Player.js"></script>-->
<!--    <script src="src/Entity/Hunter.js"></script>-->
<!--    <script src="src/Entity/Trigger.js"></script>-->
<!--    <script src="src/Entity/Light.js"></script>-->




    <script src="src/LevelScript/Functions.js"></script>

    <script src="src/Camera.js"></script>
    <script src="src/Control/ThirdPerson.js"></script>
    <script src="src/Control/OrbitAndTransform.js"></script>
    <script src="src/Control/Fly.js"></script>

    <script src="src/Storage/Storage.js"></script>
    <script src="src/Storage/Default.js"></script>
    <script src="src/Storage/Animation.js"></script>
    <script src="src/Storage/Model.js"></script>

<!--    <script src="src/Config.js"></script>-->

    <!-- Engine -->
    <script src="src/Engine.js"></script>

    <script src="src/Editor/EntityInteractive.js"></script>
</head>
<body class="c-app" id="dropZone">


    <div id="webgl"></div>


    <?php include('php/templates.html'); ?>
    <?php include('php/modal.html'); ?>

    <div class="c-sidebar c-sidebar-dark c-sidebar-fixed c-sidebar-lg-show" id="sidebar">

        <ul class="c-sidebar-nav">


            <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link" href="index.html">
                    <svg class="c-sidebar-nav-icon">
                        <use xlink:href="coreUi/free.svg#cil-speedometer"></use>
                    </svg>
                    Manhunt Toolkit
                </a>
            </li>


            <li class="c-sidebar-nav-title">Level Viewer</li>
            <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="charts.html">
                    <svg class="c-sidebar-nav-icon">
                        <use xlink:href="coreUi/free.svg#cil-chart-pie"></use>
                    </svg>
                    Load Level</a></li>


            <li class="c-sidebar-nav-title">File Editor</li>
            <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link" href="colors.html">
                    <svg class="c-sidebar-nav-icon">
                        <use xlink:href="coreUi/free.svg#cil-drop"></use>
                    </svg>
                    Models
                    <span class="badge badge-info">mdl/dff</span>

                </a>
            </li>

            <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link" href="colors.html">
                    <svg class="c-sidebar-nav-icon">
                        <use xlink:href="coreUi/free.svg#cil-drop"></use>
                    </svg>
                    Textures
                    <span class="badge badge-info">txd/tex</span>

                </a>
            </li>
        </ul>
    </div>


    <div class="c-wrapper c-fixed-components">

        <header class="c-header c-header-light ">
            <ul class="nav nav-tabs" id="studio-tab-list">
                <li style="position: absolute;right: 15px;top: 5px;">
                    <button type="button" class="btn btn-sm btn-primary">Save changes</button>
                </li>
            </ul>
        </header>

        <div class="c-body">
            <main class="c-main">
                <div  id="studio-tab-content">

                </div>
            </main>
        </div>
    </div>


    <script src="src/Scene/level.js"></script>
    <script src="src/Scene/texture.view.js"></script>
    <script src="src/Scene/animationPort.view.js"></script>
    <script src="src/Scene/animation.view.js"></script>
    <script src="src/Scene/world.view.js"></script>
    <script src="src/Frontend/Modal/Setup.js"></script>
    <script src="src/Frontend/Modal/LevelSelection.js"></script>

    <script src="src/Frontend/Modal/Handler.js"></script>
<!--    <script src="src/Studio.js"></script>-->


<script>
    let dropZone = document.getElementById('dropZone');
    dropZone.addEventListener('dragover', function(e) {
        e.stopPropagation();
        e.preventDefault();
        e.dataTransfer.dropEffect = 'copy';
    });
    dropZone.addEventListener('drop', function (e) {
        console.log('File(s) in drop zone');

        var files = e.dataTransfer.files; // Array of all files
        var reader = new FileReader();

        reader.onload = function(event) {
            let binary = new NBinary(event.target.result);

            let rwScanner = new Scan(binary);
            let result = rwScanner.scan();
            console.log(result);
        };

        reader.readAsArrayBuffer(files[0]); // start reading the file data.


        // Prevent default behavior (Prevent file from being opened)
        e.preventDefault();
    });



    window.setTimeout(function () {


        //
        Api.load(0, 'test/waitress00_clean.dff', function (data) {
        // Api.load(0, 'test/boss00_clean.dff', function (data) {
            let binary = new NBinary(data);
            let rwScanner = new Scan(binary/*,{
                scanForNewChunks: true,      //search byte per byte for chunk headers (slow)
                forcedFirstVersion: true,    //the first "valid" version will be used for future validation
                forcedVersion: null,
                searchChunks: [Renderware.CHUNK_STRING],
                onChunkCallback: function (id, chunkBinary, absoluteStartOffset) {
                    console.log("STRING", chunkBinary.getString(0));
                }

            }*/);
            let result = rwScanner.scan();
            console.log("scan", result);


            binary.setCurrent(0);
            while(binary.remain() > 0){

                console.log(Renderware.parse(binary));
            }
        });



        Studio.boot();

    }, 1000);
</script>

</body>
</html>
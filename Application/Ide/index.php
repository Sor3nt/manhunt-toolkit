<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Manhunt Toolkit v1.0</title>


    <link href="coreUi/style.css" rel="stylesheet">

    <meta name="robots" content="noindex">


    <script>
        var MANHUNT = {
            init: [],
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
            frontend: {},
            sidebar: {
                view: {},
                menu: {},
                elements: {}
            }
        };

        MANHUNT.scale = 48; //World scale for models and position
        MANHUNT.fov = 57.29578; //Default MH2 FOV

    </script>

    <script src="src/Library/jquery-3.5.1.min.js"></script>
    <script src="src/Library/deflate.min.js"></script>
    <script src="src/Library/inflate.min.js"></script>

    <link href="src/Library/select2.min.css" rel="stylesheet"/>
    <script src="src/Library/select2.min.js"></script>


    <script type="module">
        import {TransformControls} from './src/Library/TransformControls.js';
        import {OrbitControls} from './src/Library/OrbitControls.js';
        import {DDSLoader} from './src/Library/Loader/three.dds.loader.js';
        import {FlyControls} from './src/Library/FlyControls.js';

        window.DDSLoader = DDSLoader;
        window.FlyControls = FlyControls;
        window.OrbitControls = OrbitControls;
        window.TransformControls = TransformControls;
    </script>

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

    <!-- Content Converter  -->
    <script src="src/Library/Converter/dxt.rgb.converter.js"></script>

    <!-- Camera -->
    <script src="src/Camera/TVP.js"></script>

    <!-- Entity (inst) handler -->
    <script src="src/Entity/Construct.js"></script>
    <script src="src/Entity/Entity.js"></script>
    <script src="src/Entity/Default.js"></script>
    <script src="src/Entity/Player.js"></script>
    <script src="src/Entity/Hunter.js"></script>
    <script src="src/Entity/Trigger.js"></script>
    <script src="src/Entity/Light.js"></script>


    <script src="src/Frontend/Model.js"></script>
    <script src="src/Frontend/Tab.js"></script>


    <script src="src/LevelScript/Functions.js"></script>

    <script src="src/Relation.js"></script>
    <script src="src/Camera.js"></script>
    <script src="src/Control/ThirdPerson.js"></script>
    <script src="src/Control/OrbitAndTransform.js"></script>
    <script src="src/Control/Fly.js"></script>
    <script src="src/Storage/Storage.js"></script>
    <script src="src/Storage/Animation.js"></script>
    <script src="src/Storage/Model.js"></script>

    <script src="src/Config.js"></script>
    <script src="src/Animator.js"></script>

    <!-- Engine -->
    <script src="src/Engine.js"></script>
    <script src="src/Level.js"></script>

    <!--    <script type="module" src="src/Library/TransformControls.js"></script>-->

    <script src="src/Editor/EntityInteractive.js"></script>
</head>
<body class="c-app">


<div id="webgl"></div>


<?php include('php/templates.html'); ?>

<div id="loading" class="modal">
    <div class="modal-content">
        <div class="row">
            <div class="col-3"></div>
            <div class="col-6 text-center">
                <div class="progress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" id="progressbar"
                         style="width: 75%"></div>
                </div>
                <div style="color: #fff;">Loading <span id="loading-text">Engine</span> ...</div>
            </div>
            <div class="col-3"></div>
        </div>
    </div>
</div>

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
    <button class="c-sidebar-minimizer c-class-toggler" type="button" data-target="_parent"
            data-class="c-sidebar-minimized"></button>
</div>


<div class="c-wrapper c-fixed-components">


    <header class="c-header c-header-light ">


        <ul class="nav nav-tabs" id="tab-list">

            <li style="position: absolute;right: 15px;top: 5px;">
                <button type="button" class="btn btn-sm btn-primary">Save changes</button>

            </li>
        </ul>
    </header>

    <div class="c-body">
        <main class="c-main">
            <div class="container-fluid" id="content">
                <div class="row" id="tab-world" style="display: none">
                    <div class="col-9" id="webgl-world" style=" height: calc(100vh - 70px)"></div>


                    <div class="col-3">
                        <div class="c-sidebar c-sidebar-right c-sidebar-dark  c-sidebar-lg-show" id="sidebar-right"
                             style="height: calc(100vh - 70px);">

                            <ul class="c-sidebar-nav">

                                <li class="c-sidebar-nav-item c-sidebar-nav-dropdown"><a
                                            class="c-sidebar-nav-link c-sidebar-nav-dropdown-toggle" href="#">
                                        <svg class="c-sidebar-nav-icon">
                                            <use xlink:href="coreUi/free.svg#cil-puzzle"></use>
                                        </svg>
                                        Viewer Control</a>
                                    <ul class="c-sidebar-nav-dropdown-items">
                                        <li class="c-sidebar-nav-item c-sidebar-nav-dropdown">
                                            <a class="c-sidebar-nav-link c-sidebar-nav-dropdown-toggle" href="#">
                                                <svg class="c-sidebar-nav-icon">
                                                    <use xlink:href="coreUi/free.svg#cil-puzzle"></use>
                                                </svg>
                                                Layer selection
                                            </a>
                                            <ul class="c-sidebar-nav-dropdown-items">
                                                <li class="c-sidebar-nav-item"><span
                                                            class="c-sidebar-nav-icon"></span>

                                                    <div class="form-group ">

                                                        <div class="col-md-12 col-form-label">
                                                            <div class="form-check checkbox">
                                                                <input class="form-check-input" id="check1"
                                                                       type="checkbox" value="">
                                                                <label class="form-check-label"
                                                                       for="check1">World</label>
                                                                <span style="float:right" class="badge badge-info">Scene1</span>
                                                            </div>
                                                            <div class="form-check checkbox">
                                                                <input class="form-check-input" id="check2"
                                                                       type="checkbox" value="">
                                                                <label class="form-check-label"
                                                                       for="check2">Transparent</label>
                                                                <span style="float:right" class="badge badge-info">Scene2</span>
                                                            </div>
                                                            <div class="form-check checkbox">
                                                                <input class="form-check-input" id="check3"
                                                                       type="checkbox" value="">
                                                                <label class="form-check-label"
                                                                       for="check3">Shadow</label>
                                                                <span style="float:right" class="badge badge-info">Scene3</span>
                                                            </div>
                                                            <div class="form-check checkbox">
                                                                <input class="form-check-input" id="check4"
                                                                       type="checkbox" value="">
                                                                <label class="form-check-label" for="check4">Bounding
                                                                    Boxes</label>
                                                                <span style="float:right" class="badge badge-info">Scene3</span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </li>


                                            </ul>
                                        </li>
                                    </ul>
                                </li>

                            </ul>
                        </div>
                    </div>
                </div>
                <div class="row" id="tab-model" style="display: none">

                    <div class="card  col-12">
                        <div class="card-header"> Level Models
                            <div class="card-header-actions">

                                <a class="card-header-action" href="#" target="_blank">+ Add new Model</a>
                            </div>
                        </div>
                        <div class="card-body">


                            <div class="row" style="border-bottom: 1px solid black; margin-bottom:15px">

                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">

                                                <span class="input-group-text">
                                                    <svg width="16" height="16">-->
                                                        <use xlink:href="coreUi/free.svg#cil-search"></use>
                                                    </svg>
                                                </span>

                                        <!--                                            <span class="input-group-text" id="basic-addon1">@</span>-->

                                    </div>
                                    <input id="model-filter" type="text" class="form-control"
                                           placeholder="Search for a Model">
                                </div>


                                <ul class="list-group light  col-6" id="model-list"
                                    style="height: calc(100vh - 240px); overflow: scroll;display: inline-block; padding-right: 0"></ul>

                                <div id="webgl-model" class="col-6" style="padding: 0 !important;"></div>

                            </div>


                        </div>

                    </div>

                </div>

            </div>
        </main>
    </div>
</div>

<script src="coreUi/coreui.bundle.min.js"></script>
<!--[if IE]><!-->
<script src="coreUi/svgxuse.min.js"></script>
<!--<![endif]-->

<script src="coreUi/coreui-chartjs.bundle.js"></script>
<script src="coreUi/coreui-utils.js"></script>
<script src="coreUi/main.js"></script>


<script type="module">


    MANHUNT.engine.init();
    MANHUNT.frontend.tab.init();

    function waitForWorldCalllback() {

        MANHUNT.engine.createSceneInfo(
            document.getElementById('webgl-model'),
            'model',
            new THREE.PerspectiveCamera(MANHUNT.fov, 1.33, 0.1, 10000),
            MANHUNT.control.OrbitAndTransform,
            function (sceneInfo) { //onCreate
                MANHUNT.frontend.model.init();
                MANHUNT.frontend.model.loadResources();

                MANHUNT.frontend.tab.add(
                    'model',
                    jQuery('#tab-model'),
                    function () {
                        //close
                    },
                    function () {
                        MANHUNT.engine.changeScene('model');
                        //focus
                    },
                    function () {
                        //blur
                    },
                );

                MANHUNT.frontend.tab.show('model');

                sceneInfo.camera.position.set(-140.83501492578623, 119.29015658522931, -73.34957947924103);

                var spotLight = new THREE.SpotLight(0xffffff);
                spotLight.position.set(1, 1, 1);
                sceneInfo.scene.add(spotLight);

                sceneInfo.scene.add(new THREE.HemisphereLight(0xffffff, 0x444444));
                sceneInfo.scene.add(new THREE.GridHelper(1000, 10, 0x888888, 0x444444));

            },

            function (sceneInfo, delta) { //onUpdate
            }
        );
    }

    var worldSceneInfo = MANHUNT.engine.createSceneInfo(
        document.getElementById('webgl-world'),
        'world',
        new THREE.PerspectiveCamera(MANHUNT.fov, 1.33, 0.1, 10000),
        MANHUNT.control.ThirdPerson,
        function (sceneInfo) { //onCreate

            var game = "mh2";
            var name = "A01_Escape_Asylum";

            var loadChain = [
                {
                    order: [
                        {
                            ifp: ['./data/levels/' + name + '/allanims_pc.ifp']
                        }
                    ],

                    callback: function () {

                    }
                },
                {
                    order: [
                        {
                            tex: [
                                './data/global/danny_asylum_bloody_pc.tex',
                                './data/levels/' + name + '/modelspc.tex'
                            ],
                            glg: ['./data/levels/' + name + '/resource3.glg'],

                        },
                        {
                            mdl: [
                                './data/global/danny_asylum_bloody_pc.mdl',
                                './data/levels/' + name + '/modelspc.mdl'
                            ]
                        },
                        {
                            inst: ['./data/levels/' + name + '/entity_pc.inst']
                        }

                    ],

                    callback: function () {

                        MANHUNT.level.getStorage('inst').getData().forEach(function (instEntry) {

                            var entity;
                            MANHUNT.relation.addInst(instEntry.name, instEntry);

                            var glg = MANHUNT.level.getStorage('glg').find(instEntry.glgRecord);
                            if (glg !== false){
                                MANHUNT.relation.addGlg(instEntry.glgRecord, glg);
                                MANHUNT.relation.inst2Glg(instEntry.name, instEntry.glgRecord);

                                var modelName = glg.getValue("MODEL");
                                if (modelName === false) {
                                    entity = MANHUNT.entity.construct.byInstEntry(instEntry);
                                    if (entity === false) return;

                                    sceneInfo.scene.add(entity.object);
                                }else{

                                    //TODO, hardcoded level 1 stuff
                                    if (modelName === "fist_poly_hunter"){
                                        modelName = 'danny_asylum_bloody';
                                    }

                                    var model = MANHUNT.level.getStorage('mdl').find(modelName);
                                    if (model !== false){
                                        MANHUNT.relation.addModel(modelName, model);
                                        MANHUNT.relation.model2Glg(modelName, instEntry.glgRecord);
                                        MANHUNT.relation.model2Inst(modelName, instEntry.name);

                                        entity = MANHUNT.entity.construct.byInstEntry(instEntry, model);
                                        if (entity === false) return;

                                        sceneInfo.scene.add(entity.object);

                                    }

                                    MANHUNT.level.getStorage('entity').add(entity);
                                    MANHUNT.relation.addEntity(entity.name, entity);
                                    MANHUNT.relation.inst2Entity(entity.name, instEntry.name);
                                }
                            }


                        });
                    }
                },

                {
                    order: [
                        {
                            tex: ['./data/levels/' + name + '/scene1_pc.tex'],
                        },
                        {
                            bsp: [
                                './data/levels/' + name + '/scene1_pc.bsp',
                                './data/levels/' + name + '/scene2_pc.bsp',
                                './data/levels/' + name + '/scene3_pc.bsp'
                            ]
                        }

                    ],

                    callback: function () {
                        var storage = MANHUNT.level.getStorage('bsp');

                        var scenes = [
                            storage.find('scene1'),
                            storage.find('scene2'),
                            storage.find('scene3'),
                        ];

                        scenes.forEach(function (scene, index) {
                            // if (index === 0) scene.renderOrder = 0;
                            if (index === 2){
                                //hide bbox and shadow light
                                MANHUNT.level.getStorage('bsp').find('scene3').children.forEach(function (child) {
                                    child.visible = false;
                                });
                            }

                            scene.scale.set(48,48,48);
                            sceneInfo.scene.add(scene);
                        });
                    }
                }
            ];

            MANHUNT.level.processChain(loadChain, function () {

                waitForWorldCalllback();

                MANHUNT.frontend.tab.add(
                    'world',
                    jQuery('#tab-world'),
                    function () {
                        //close
                    },
                    function () {
                        MANHUNT.engine.changeScene('world');
                        //focus
                    },
                    function () {
                        //blur
                    },
                );
                //
                // MANHUNT.frontend.tab.show('world');

                var spotLight = new THREE.SpotLight(0xffffff);
                spotLight.position.set(1, 1, 1);
                sceneInfo.scene.add(spotLight);

                sceneInfo.scene.add(new THREE.HemisphereLight(0xffffff, 0x444444));

                var player = MANHUNT.level.getStorage('entity').find('player(player)');
                sceneInfo.control.enable(player.object);


                jQuery('#loading').hide();


            });

        },

        function (sceneInfo, delta) { //onUpdate

            /**
             * Camera follow the player
             */
            var lookAt = sceneInfo.control.enable(); //enable return the enabled object if no arguments applied

            var relativeCameraOffset = new THREE.Vector3(0, 2, -3);
            lookAt.updateMatrixWorld();
            var cameraOffset = relativeCameraOffset.applyMatrix4(lookAt.matrixWorld);

            sceneInfo.camera.position.lerp(cameraOffset, 0.1);
            sceneInfo.camera.lookAt(lookAt.position);

            sceneInfo.control.update(delta);
        }
    );

    MANHUNT.engine.render();


</script>


</body>
</html>
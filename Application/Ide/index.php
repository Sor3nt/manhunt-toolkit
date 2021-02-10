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
            resources: {},
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

    <script src="src/Api.js"></script>

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
    <script src="src/Library/Parser/dff.parser.js"></script>
    <script src="src/Library/Parser/txd.parser.js"></script>
    <script src="src/Library/Parser/tex.parser.js"></script>

    <!-- Content Converter  -->
    <script src="src/Library/Converter/dxt.rgb.converter.js"></script>
    <script src="src/Library/Converter/generic.mesh.converter.js"></script>
    <script src="src/Library/Converter/dds.texture.converter.js"></script>

    <!-- Camera -->
    <script src="src/Camera/TVP.js"></script>


    <script src="src/Resources/abstract.js"></script>
    <script src="src/Resources/manhunt.js"></script>
    <script src="src/Resources/manhunt2.js"></script>
    <script src="src/Resources/resources.js"></script>
    <script src="src/Scene/model.view.js"></script>
    <script src="src/Scene/views.js"></script>

    <!-- Entity (inst) handler -->
    <script src="src/Entity/Construct.js"></script>
    <script src="src/Entity/Entity.js"></script>
    <script src="src/Entity/Default.js"></script>
    <script src="src/Entity/Player.js"></script>
    <script src="src/Entity/Hunter.js"></script>
    <script src="src/Entity/Trigger.js"></script>
    <script src="src/Entity/Light.js"></script>


    <script src="src/Frontend/Tab.js"></script>


    <script src="src/LevelScript/Functions.js"></script>

    <script src="src/Relation.js"></script>
    <script src="src/Camera.js"></script>
    <script src="src/Control/ThirdPerson.js"></script>
    <script src="src/Control/OrbitAndTransform.js"></script>
    <script src="src/Control/Fly.js"></script>

    <script src="src/Storage/Storage.js"></script>
    <script src="src/Storage/Default.js"></script>
    <script src="src/Storage/Animation.js"></script>
    <script src="src/Storage/Model.js"></script>

    <script src="src/Config.js"></script>

    <!-- Engine -->
    <script src="src/Engine.js"></script>

    <script src="src/Editor/EntityInteractive.js"></script>
</head>
<body class="c-app">


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


    <script src="src/ObjectAnimation.js"></script>
    <script src="src/Scene/level.js"></script>
    <script src="src/Scene/texture.view.js"></script>
    <script src="src/Scene/animationPort.view.js"></script>
    <script src="src/Scene/animation.view.js"></script>
    <script src="src/Scene/world.view.js"></script>
    <script src="src/Frontend/Modal/Setup.js"></script>
    <script src="src/Frontend/Modal/LevelSelection.js"></script>

    <script src="src/Frontend/Modal/Handler.js"></script>
    <script src="src/Studio.js"></script>

</body>
</html>
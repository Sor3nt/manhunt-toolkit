MANHUNT.scene.modelView = function (level) {


    var self = {

        _name : 'model '+ level._name,

        _camera: new THREE.PerspectiveCamera(MANHUNT.fov, 1.33, 0.1, 10000),
        _control: MANHUNT.control.OrbitAndTransform,
        _content : {},

        _sceneInfo: {},

        _init: function(){
            var template = document.querySelector('#view-model');

            var row = jQuery(template.content).clone();
            jQuery('#tab-content').append(row);
            self._content = jQuery('#tab-content').find('>div:last-child');


            self._sceneInfo = MANHUNT.engine.createSceneInfo(
                self._content.find('[data-field="webgl"]'),
                self._name,
                self._camera,
                self._control,
                self._onCreate,
                self._onUpdate
            );
        },

        _onCreate: function (sceneInfo) {
console.log("HHHHH", sceneInfo);
            var modelsView = new MANHUNT.frontend.Model(level);
            modelsView.loadResources();

            MANHUNT.frontend.tab.add(
                self._name,
                self._content,
                function () {
                    //close
                },
                function () {
                    MANHUNT.engine.changeScene(self._name);
                    //focus
                },
                function () {
                    //blur
                },
            );

            MANHUNT.frontend.tab.show(self._name);

            sceneInfo.camera.position.set(-140.83501492578623, 119.29015658522931, -73.34957947924103);

            var spotLight = new THREE.SpotLight(0xffffff);
            spotLight.position.set(1, 1, 1);
            sceneInfo.scene.add(spotLight);

            sceneInfo.scene.add(new THREE.HemisphereLight(0xffffff, 0x444444));
            sceneInfo.scene.add(new THREE.GridHelper(1000, 10, 0x888888, 0x444444));


        },

        _onUpdate: function (sceneInfo, delta) {

        }
        
    };

    self._init();

    return {

    }
};
MANHUNT.scene.WorldView = function (level) {

    let self = {

        _name : 'level_'+ level._name,
        _camera: new THREE.PerspectiveCamera(MANHUNT.fov, 1.33, 0.1, 10000),
        _control: MANHUNT.control.ThirdPerson,

        _sceneInfo: {},
        _content : {},

        _init: function(){
            self._content = jQuery(jQuery('#view-world').html());
            MANHUNT.studio.getTabHandler().addContent(self._content);

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

            MANHUNT.studio.getTabHandler().add(
                self._name,
                self._content,
                function () { }, //close
                function () { MANHUNT.engine.changeScene(self._name);}, //focus
                function () { } //blur
            );

            let spotLight = new THREE.SpotLight(0xffffff);
            spotLight.position.set(1, 1, 1);
            sceneInfo.scene.add(spotLight);

            sceneInfo.scene.add(new THREE.HemisphereLight(0xffffff, 0x444444));
        },

        _onUpdate: function (sceneInfo, delta) {

            /**
             * Camera follow the player
             */
            let lookAt = sceneInfo.control.enable(); //enable return the enabled object if no arguments applied

            let relativeCameraOffset = new THREE.Vector3(0, 2, -3);
            lookAt.updateMatrixWorld();
            let cameraOffset = relativeCameraOffset.applyMatrix4(lookAt.matrixWorld);

            sceneInfo.camera.position.lerp(cameraOffset, 0.1);
            sceneInfo.camera.lookAt(lookAt.position);

            sceneInfo.control.update(delta);
        },
    };

    self._init();

    return {
        getSceneInfo: function () {
            return self._sceneInfo;
        }
    }
};
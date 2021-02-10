MANHUNT.scene.AnimationPortView = function () {


    let self = {

        _name : 'Animation Porting',

        _camera: new THREE.PerspectiveCamera(MANHUNT.fov, 1.33, 0.1, 1000),
        _control: MANHUNT.control.OrbitAndTransform,
        _container : {},

        _sceneInfo: {},

        _tabHandler: {},
        _animationMh1 : false,
        _animationMh2 : false,

        _init: function(){

            // self._container = jQuery("<h1>joo</h1>");
            self._container = jQuery(jQuery('#view-animation-port').html());


            MANHUNT.studio.getTabHandler().add(
                self._name,
                self._container,
                function () { }, //close
                function () {
                    MANHUNT.engine.changeScene(self._name);
                }, //focus
                function () { } //blur
            );

            self._sceneInfo = MANHUNT.engine.createSceneInfo(
                self._container.find('[data-field="webgl"]'),
                self._name,
                self._camera,
                self._control,
                self._onCreate,
                self._onUpdate
            );



        },

        _onCreate: function (sceneInfo) {

            //Create scene
            sceneInfo.camera.position.set(-140.83501492578623, 119.29015658522931, -73.34957947924103);

            let spotLight = new THREE.SpotLight(0xffffff);
            spotLight.position.set(1, 1, 1);
            sceneInfo.scene.add(spotLight);

            sceneInfo.scene.add(new THREE.HemisphereLight(0xffffff, 0x444444));
            sceneInfo.scene.add(new THREE.GridHelper(1000, 10, 0x888888, 0x444444));


            (new MANHUNT.resources.Manhunt('jury_turf', function ( storageMh1 ) {

                let model = storageMh1.mdl.find('Player_Bod').get();
                self._animationMh1 = new MANHUNT.ObjectAnimation({ _storage: storageMh1}, model);

                sceneInfo.scene.add(model);
                // model.visible = false;

                const helper = new THREE.SkeletonHelper( model );
                sceneInfo.scene.add( helper );

                console.log("mh model", model);


                (new MANHUNT.resources.Manhunt2('A01_Escape_Asylum', function ( storageMh2 ) {

                    let model = storageMh2.mdl.find('danny_asylum_bloody').get();
                    self._animationMh2 = new MANHUNT.ObjectAnimation({ _storage: storageMh2}, model);

                    // model.position.x = 60;
                    sceneInfo.scene.add(model);
                    // model.visible = false;

                    const helper = new THREE.SkeletonHelper( model );
                    sceneInfo.scene.add( helper );


                    let clip = storageMh1.ifp.find("PlayerAnims", "Bag_Sneak_Attack4");
                    self._animationMh1.playClip(clip);
                    //
                    storageMh1.ifp.setConvertNames(true);
                    let clip2 = storageMh1.ifp.find("PlayerAnims", "Bag_Sneak_Attack4");
                    // let clip2 = storageMh1.ifp.find("PlayerAnims", "Bag_Sneak_Attack4");
                    self._animationMh2.playClip(clip2);


                    window.setTimeout(function () {
                        // self._animationMh1.pause();
                        // self._animationMh2.pause();
                    }, 100);

                    console.log("mh2 model", model);

                    //apply the model to the control
                    sceneInfo.control.enable(model);

                }));

            }));

            MANHUNT.studio.getTabHandler().show(self._name);
        },

        _onUpdate: function (sceneInfo, delta) {
            self._animationMh1 !== false && self._animationMh1.update(delta);
            self._animationMh2 !== false && self._animationMh2.update(delta);
        }

        
    };

    self._init();

    return {
        getSceneInfo: function () {
            return self._sceneInfo;
        }

    }
};
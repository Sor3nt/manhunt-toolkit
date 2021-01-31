MANHUNT.scene.AbstractLevel = function (levelName, doneCallback) {

    let self = {

        _name : 'level_' + levelName,

        _camera: new THREE.PerspectiveCamera(MANHUNT.fov, 1.33, 0.1, 10000),
        _control: MANHUNT.control.ThirdPerson,

        _sceneInfo: {},

        _storage: {},
        _animator: {},
        _content : {},
        
        relation: new MANHUNT.Relation(),
        
        _init: function(){

            self._content = jQuery(jQuery('#view-world').html());
            MANHUNT.studio.getTabHandler().addContent(self._content);



            var storage = new MANHUNT.storage.Storage(self);

            self._storage.ifp = storage.create('Animation');
            self._storage.mdl = storage.create('Model');
            self._storage.tex = storage.create('tex');
            self._storage.bsp = storage.create('bsp');
            self._storage.glg = storage.create('glg');
            self._storage.inst = storage.create('inst');
            self._storage.entity = storage.create();
            self._animator = new MANHUNT.animator(self);


            self._sceneInfo = MANHUNT.engine.createSceneInfo(
                self._content.find('[data-field="webgl"]'),
                self._name,
                self._camera,
                self._control,
                self._onCreate,
                self._onUpdate
            );
        },


        loadChainFiles: function(entries, callback){

            var wait = 0;

            jQuery.each(entries, function (loader, files) {

                jQuery.each(files, function (fileId, file) {
                    wait++;

                    self._storage[loader].load(file, function () {
                        wait--;

                        if (wait === 0){
                            callback();
                        }

                    })

                });

            });

        },

        _processChain: function(chain){


            var promise = new Promise(function(okCallback){
                okCallback();
            });

            jQuery.each(chain, function (chainId, part) {

                promise = promise.then(function () {


                    var innerPromise = new Promise(function (okCallback) {
                        okCallback();
                    });


                    jQuery.each(part.order, function (orderIndex, order) {
                        innerPromise = innerPromise.then(function () {
                            return new Promise(function (okCallback) {
                                self.loadChainFiles(order, function () {
                                    okCallback();
                                })

                            });

                        });
                    });

                    innerPromise = innerPromise.then(function () {
                        return new Promise(function (okCallback) {
                            part.callback();
                            okCallback();
                        });
                    });

                    return innerPromise;
                });
            });


            promise.then(function () {
                return new Promise(function (okCallback) {
                    self._onChainProcessed();
                    okCallback();
                });
            });

        },

        _onChainProcessed: function(){

            MANHUNT.studio.getTabHandler().add(
                self._name,
                self._content,
                function () { }, //close
                function () { MANHUNT.engine.changeScene(self._name);}, //focus
                function () { } //blur
            );

            let sceneInfo = self._sceneInfo;

            let spotLight = new THREE.SpotLight(0xffffff);
            spotLight.position.set(1, 1, 1);
            sceneInfo.scene.add(spotLight);

            sceneInfo.scene.add(new THREE.HemisphereLight(0xffffff, 0x444444));

            let player = self._storage.entity.find('player(player)');
            sceneInfo.control.enable(player.object);

            jQuery('#loading').hide();

            doneCallback(self);
        },

        addScene: function(view){
            new view(self);
        },


        _onUpdate: function (sceneInfo, delta) {

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

    };

    return self;
};
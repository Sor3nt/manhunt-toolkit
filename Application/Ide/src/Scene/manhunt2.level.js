MANHUNT.scene.Manhunt2Level = function (levelName, doneCallback) {


    var self = {

        _name : 'level_' + levelName,
        _game: 'manhunt2',

        _camera: new THREE.PerspectiveCamera(MANHUNT.fov, 1.33, 0.1, 10000),
        _control: MANHUNT.control.ThirdPerson,

        _sceneInfo: {},

        _storage: {},
        _animator: {},
        _content : {},
        
        relation: new MANHUNT.Relation(),
        
        _init: function(){

            self._content = jQuery(jQuery('#world').html());
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

        _processChain: function(chain, callback){


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
                    callback();
                    okCallback();
                });
            });

        },

        addScene: function(view){
            new view(self);
        },

        _onCreate: function (sceneInfo) {

            var loadChain = [
                {
                    order: [
                        {
                            ifp: ['levels/' + levelName + '/allanims_pc.ifp']
                        }
                    ],

                    callback: function () {

                    }
                },
                {
                    order: [
                        {
                            tex: [
                                'global/danny_asylum_bloody_pc.tex',
                                'levels/' + levelName + '/modelspc.tex'
                            ],
                            glg: ['levels/' + levelName + '/resource3.glg'],

                        },
                        {
                            mdl: [
                                'global/danny_asylum_bloody_pc.mdl',
                                'levels/' + levelName + '/modelspc.mdl'
                            ]
                        },
                        {
                            inst: ['levels/' + levelName + '/entity_pc.inst']
                        }

                    ],

                    callback: function () {

                        self._storage.inst.getData().forEach(function (instEntry) {

                            var entity;
                            self.relation.addInst(instEntry.name, instEntry);

                            var glg = self._storage.glg.find(instEntry.glgRecord);
                            instEntry.glg = glg;
                            if (glg !== false){
                                self.relation.addGlg(instEntry.glgRecord, glg);
                                self.relation.inst2Glg(instEntry.name, instEntry.glgRecord);

                                var modelName = glg.getValue("MODEL");
                                instEntry.model = false;
                                if (modelName === false) {
                                    entity = MANHUNT.entity.construct.byInstEntry(instEntry);
                                    if (entity === false) return;

                                    sceneInfo.scene.add(entity.object);
                                }else{

                                    //TODO, hardcoded level 1 stuff
                                    if (modelName === "fist_poly_hunter"){
                                        modelName = 'danny_asylum_bloody';
                                    }

                                    var model = self._storage.mdl.find(modelName);
                                    if (model !== false){
                                        self.relation.addModel(modelName, model);
                                        self.relation.model2Glg(modelName, instEntry.glgRecord);
                                        self.relation.model2Inst(modelName, instEntry.name);

                                        entity = MANHUNT.entity.construct.byInstEntry(instEntry, model);
                                        if (entity === false) return;


                                        //Hunter have a additional model
                                        var headRecordName = entity.record.getValue("HEAD");
                                        if (headRecordName !== false && headRecordName !== "no_hed"){

                                            var headRecordGlg = self._storage.glg.find(headRecordName);
                                            var headModelName = headRecordGlg.getValue("MODEL");

                                            var headModel = self._storage.mdl.find(headModelName);
                                            var headObj = headModel.get();

                                            entity.object.skeleton.bones.forEach(function (bone) {
                                                if (bone.name === "Bip01_Head") bone.add(headObj);
                                            });

                                            self.relation.addModel(headModelName, headObj);
                                            self.relation.addGlg(headRecordName, headRecordGlg);

                                            self.relation.inst2Glg(instEntry.name, headModelName);
                                            self.relation.model2Inst(headModelName, instEntry.name);
                                            self.relation.model2Glg(headModelName, headRecordName);

                                        }
                                        
                                        

                                        sceneInfo.scene.add(entity.object);

                                        self._storage.entity.add(entity);
                                        self.relation.addEntity(entity.name, entity);
                                        self.relation.inst2Entity(entity.name, instEntry.name);

                                    }

                                }
                            }


                        });
                    }
                },

                {
                    order: [
                        {
                            tex: ['levels/' + levelName + '/scene1_pc.tex'],
                        },
                        {
                            bsp: [
                                'levels/' + levelName + '/scene1_pc.bsp',
                                'levels/' + levelName + '/scene2_pc.bsp',
                                'levels/' + levelName + '/scene3_pc.bsp'
                            ]
                        }

                    ],

                    callback: function () {
                        var storage = self._storage.bsp;

                        var scenes = [
                            storage.find('scene1'),
                            storage.find('scene2'),
                            storage.find('scene3'),
                        ];

                        scenes.forEach(function (scene, index) {
                            // if (index === 0) scene.renderOrder = 0;
                            if (index === 2){
                                //hide bbox and shadow light
                                scene.children.forEach(function (child) {
                                    child.visible = false;
                                });
                            }

                            scene.scale.set(48,48,48);
                            sceneInfo.scene.add(scene);
                        });
                    }
                }
            ];

            self._processChain(loadChain, function () {

                // waitForWorldCalllback();

                MANHUNT.studio.getTabHandler().add(
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
                //
                // MANHUNT.frontend.tab.show('world');

                var spotLight = new THREE.SpotLight(0xffffff);
                spotLight.position.set(1, 1, 1);
                sceneInfo.scene.add(spotLight);

                sceneInfo.scene.add(new THREE.HemisphereLight(0xffffff, 0x444444));

                var player = self._storage.entity.find('player(player)');
                sceneInfo.control.enable(player.object);


                jQuery('#loading').hide();

                doneCallback(self);

            });

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

    self._init();

    return {
        addScene: self.addScene
    }
};
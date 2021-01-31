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

            let storage = new MANHUNT.storage.Storage(self);

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

            let wait = 0;

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

            let promise = new Promise(function(okCallback){
                okCallback();
            });

            jQuery.each(chain, function (chainId, part) {

                promise = promise.then(function () {


                    let innerPromise = new Promise(function (okCallback) {
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

            let player;
            if (self._game === "manhunt2"){
                player = self._storage.entity.find('player(player)');
            }else{
                player = self._storage.entity.find('player');
            }

            sceneInfo.control.enable(player.object);

            jQuery('#loading').hide();

            doneCallback(self);
        },

        _createMap: function(){
            self._storage.bsp.getData().forEach(function (scene, index) {

                if (self._game === "manhunt2" && index === 2){
                    //hide bbox and shadow light
                    scene.children.forEach(function (child) {
                        child.visible = false;
                    });
                }

                scene.scale.set(48,48,48);
                self._sceneInfo.scene.add(scene);
            });
        },

        addScene: function(view){
            new view(self);
        },

        _createModels: function(){
            self._storage.inst.getData().forEach(function (instEntry) {

                let entity;
                self.relation.addInst(instEntry.name, instEntry);

                let glg = self._storage.glg.find(instEntry.glgRecord);
                instEntry.glg = glg;
                if (glg !== false){
                    self.relation.addGlg(instEntry.glgRecord, glg);
                    self.relation.inst2Glg(instEntry.name, instEntry.glgRecord);

                    let modelName = glg.getValue("MODEL");
                    //searchable and trigger has no model
                    if (modelName === false || modelName === "") return;

                    instEntry.model = false;
                    if (modelName === false) {
                        entity = MANHUNT.entity.construct.byInstEntry(instEntry);
                        if (entity === false) return;

                        sceneInfo.scene.add(entity.object);
                    }else{

                        //TODO, hardcoded level 1 stuff
                        if (modelName === "fist_poly_hunter"){
                            if (self._game === "manhunt2"){
                                modelName = 'danny_asylum_bloody';
                            }else{
                                modelName = 'Player_Bod';
                            }

                        }

                        self._createModel(modelName, instEntry);


                    }
                }


            });
        },

        _createModel: function(modelName, instEntry){
            let model = self._storage.mdl.find(modelName);
            if (model === false) return;

            self.relation.addModel(modelName, model);
            self.relation.model2Glg(modelName, instEntry.glgRecord);
            self.relation.model2Inst(modelName, instEntry.name);

            entity = MANHUNT.entity.construct.byInstEntry(instEntry, model);
            if (entity === false) return;


            //Hunter have a additional model
            let headRecordName = entity.record.getValue("HEAD");
            if (headRecordName !== false && headRecordName !== "no_hed"){

                let headRecordGlg = self._storage.glg.find(headRecordName);
                let headModelName = headRecordGlg.getValue("MODEL");

                let headModel = self._storage.mdl.find(headModelName);
                let headObj = headModel.get();

                entity.object.skeleton.bones.forEach(function (bone) {
                    if (bone.name === "Bip01_Head") bone.add(headObj);
                });

                self.relation.addModel(headModelName, headObj);
                self.relation.addGlg(headRecordName, headRecordGlg);

                self.relation.inst2Glg(instEntry.name, headRecordName);
                self.relation.model2Inst(headModelName, instEntry.name);
                self.relation.model2Glg(headModelName, headRecordName);

            }



            self._sceneInfo.scene.add(entity.object);

            self._storage.entity.add(entity);
            self.relation.addEntity(entity.name, entity);
            self.relation.inst2Entity(entity.name, instEntry.name);

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
        }

    };

    return self;
};
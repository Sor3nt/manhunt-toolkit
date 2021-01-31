MANHUNT.scene.ManhuntLevel = function (levelName, doneCallback) {


    let base = new MANHUNT.scene.AbstractLevel(levelName, doneCallback);

    let self = Object.assign(base, {

        _game: 'manhunt',

        _onCreate: function (sceneInfo) {

            let loadChain = [
                {
                    order: [
                        {
                            ifp: ['levels/' + levelName + '/allanims.ifp']
                        }
                    ],

                    callback: function () {

                    }
                },
                {
                    order: [
                        {
                            tex: [
                                'levels/GLOBAL/CHARPAK/cash_pc.txd',
                                'levels/' + levelName + '/pak/modelspc.txd',
                                'levels/' + levelName + '/picmap.txd',
                                // 'levels/' + levelName + '/picmmap.txd'
                            ],

                            glg: ['levels/GLOBAL/DATA/ManHunt.pak#./levels/' + levelName + '/entityTypeData.ini'],

                        },
                        {
                            mdl: [
                                'levels/GLOBAL/CHARPAK/cash_pc.dff',
                                'levels/' + levelName + '/pak/modelspc.dff'
                            ]
                        },
                        {
                            inst: [
                                'levels/' + levelName + '/entity.inst',
                                'levels/' + levelName + '/entity2.inst'
                            ]
                        }

                    ],

                    callback: function () {

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
                                        modelName = 'Player_Bod'; //todo
                                    }

                                    let model = self._storage.mdl.find(modelName.substr(0,23));
                                    if (model !== false){
                                        self.relation.addModel(modelName, model);
                                        self.relation.model2Glg(modelName, instEntry.glgRecord);
                                        self.relation.model2Inst(modelName, instEntry.name);

                                        entity = MANHUNT.entity.construct.byInstEntry(instEntry, model);
                                        if (entity === false) return;


                                        //Hunter have a additional model
                                        let headRecordName = entity.record.getValue("HEAD");
                                        // if (headRecordName !== false && headRecordName !== "no_hed"){
                                        //
                                        //     let headRecordGlg = self._storage.glg.find(headRecordName);
                                        //     let headModelName = headRecordGlg.getValue("MODEL");
                                        //
                                        //     let headModel = self._storage.mdl.find(headModelName);
                                        //     let headObj = headModel.get();
                                        //
                                        //     entity.object.skeleton.bones.forEach(function (bone) {
                                        //         if (bone.name === "Bip01_Head") bone.add(headObj);
                                        //     });
                                        //
                                        //     self.relation.addModel(headModelName, headObj);
                                        //     self.relation.addGlg(headRecordName, headRecordGlg);
                                        //
                                        //     self.relation.inst2Glg(instEntry.name, headModelName);
                                        //     self.relation.model2Inst(headModelName, instEntry.name);
                                        //     self.relation.model2Glg(headModelName, headRecordName);
                                        //
                                        // }



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
                            tex: ['levels/' + levelName + '/pak/scene1pc.txd'],
                        },
                        {
                            bsp: [
                                'levels/' + levelName + '/scene1.bsp',
                                // 'levels/' + levelName + '/scene2.bsp',
                            ]
                        }

                    ],

                    callback: function () {
                        let storage = self._storage.bsp;

                        let scenes = [
                            storage.find('scene1'),
                            // storage.find('scene2')
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

            self._processChain(loadChain);

        }

    });

    self._init();

    return {
        addScene: self.addScene
    }
};
MANHUNT.scene.Manhunt2Level = function (levelName, doneCallback) {

    let base = new MANHUNT.scene.AbstractLevel(levelName, doneCallback);

    let self = Object.assign(base, {

        _game: 'manhunt2',


        _onCreate: function (sceneInfo) {

            let loadChain = [
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

                            let entity;
                            self.relation.addInst(instEntry.name, instEntry);

                            let glg = self._storage.glg.find(instEntry.glgRecord);
                            instEntry.glg = glg;
                            if (glg !== false){
                                self.relation.addGlg(instEntry.glgRecord, glg);
                                self.relation.inst2Glg(instEntry.name, instEntry.glgRecord);

                                let modelName = glg.getValue("MODEL");
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

                                    let model = self._storage.mdl.find(modelName);
                                    if (model !== false){
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
                        let storage = self._storage.bsp;

                        let scenes = [
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

            self._processChain(loadChain);

        }

    });

    self._init();

    return {
        addScene: self.addScene
    }
};
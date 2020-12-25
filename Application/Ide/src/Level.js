
MANHUNT.level = (function () {

    var self = {

        _name : "",
        _callback: {},

        _storage: {},
        _status : {
            chain1: false,
            chain2: false,
            chain3: false,
            chain4: false,
            chain5: false
        },
        _processed : {
            chain1: false,
            chain2: false,
            chain3: false,
            chain4: false,
            chain5: false
        },

        _init: function(){
            self._storage.ifp = new MANHUNT.storage.Animation();
            self._storage.tex = new MANHUNT.storage.Storage('tex');
            self._storage.mdl = new MANHUNT.storage.Model();
            self._storage.bsp = new MANHUNT.storage.Storage('bsp');
            self._storage.glg = new MANHUNT.storage.Storage('glg');
            self._storage.inst = new MANHUNT.storage.Storage('inst');
            self._storage.entity = new MANHUNT.storage.Storage();
        },

        load: function (name, callback) {
            console.log("[MANHUNT.level] Load level ", name);

            self._name = name;
            self._callback = callback;

            /**
             * Chain 1: Player Texture => Player Model
             */
            self._storage.tex.load('./data/global/danny_asylum_bloody_pc.tex', function () {
                self._storage.mdl.load('./data/global/danny_asylum_bloody_pc.mdl', function () {
                    self._status.chain1 = true;
                    self._checkChainStates();
                });

            });

            /**
             * Chain 2: Model Textures => Model
             */
            self._storage.tex.load('./data/levels/' + name + '/modelspc.tex', function () {
                self._storage.mdl.load('./data/levels/' + name + '/modelspc.mdl', function () {
                    self._status.chain2 = true;
                    self._checkChainStates();
                });
            });

            /**
             * Chain 3: Map Texture => Scene 1 => Scene 2
             */
            self._storage.tex.load('./data/levels/' + name + '/scene1_pc.tex', function () {
                self._storage.bsp.load('./data/levels/' + name + '/scene1_pc.bsp', function () {
                    self._storage.bsp.load('./data/levels/' + name + '/scene2_pc.bsp', function () {
                        self._storage.bsp.load('./data/levels/' + name + '/scene3_pc.bsp', function () {
                            self._status.chain3 = true;
                            self._checkChainStates();
                        });
                    });
                });
            });

            /**
             * Chain 4: GLG => INST
             */
            self._storage.glg.load('./data/levels/' + name + '/resource3.glg', function () {
                self._storage.inst.load('./data/levels/' + name + '/entity_pc.inst', function () {
                    self._status.chain4 = true;
                    self._checkChainStates();
                });
            });

            /**
             * Chain 5: IFP
             */
            self._storage.ifp.load('./data/levels/' + name + '/allanims_pc.ifp', function () {
                self._status.chain5 = true;
                self._checkChainStates();
            });
        },

        _checkChainStates: function(){

            //Map Texture, scene1 and scene2 loaded
            if (self._status.chain3 && self._processed.chain3 === false){
                console.log("[MANHUNT.level] Chain 3 loaded");
                self._processed.chain3 = true;

                var scenes = [
                    self._storage.bsp.find('scene1'),
                    self._storage.bsp.find('scene2'),
                    self._storage.bsp.find('scene3'),
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
                    MANHUNT.engine.getScene('world').add(scene);
                });

            }

            //Player and Entity Models, GLG and INST loaded
            if (
                self._status.chain1 &&
                self._status.chain2 && self._status.chain4 &&
                self._processed.chain2 === false && self._processed.chain4 === false
            ){
                console.log("[MANHUNT.level] Chain 2 and 4 loaded");
                self._processed.chain1 = true;
                self._processed.chain2 = true;
                self._processed.chain4 = true;


                /**
                 * Generate Relations
                 */
                self._storage.inst.getData().forEach(function (instEntry) {
                    MANHUNT.relation.addInst(instEntry.name, instEntry);

                    var glg = self._storage.glg.find(instEntry.glgRecord);
                    if (glg !== false){
                        MANHUNT.relation.addGlg(instEntry.glgRecord, glg);
                        MANHUNT.relation.inst2Glg(instEntry.name, instEntry.glgRecord);

                        var modelName = glg.getValue("MODEL");
                        if (modelName !== false){

                            //TODO, hardcoded level 1 stuff
                            if (modelName === "fist_poly_hunter"){
                                modelName = 'danny_asylum_bloody';
                            }

                            var model = self._storage.mdl.find(modelName);
                            if (model !== false){
                                MANHUNT.relation.addModel(modelName, model);
                                MANHUNT.relation.model2Glg(modelName, instEntry.glgRecord);
                                MANHUNT.relation.model2Inst(modelName, instEntry.name);
                            }
                        }
                    }


                });


                self._storage.inst.getData().forEach(function (instEntry) {

                    var glg = MANHUNT.relation.getGlgByInst(instEntry.name);
                    // var glg = self._storage.glg.find(instEntry.glgRecord);
                    if (glg === false) return;

                    var model,entity;
                    var modelName = glg.getValue("MODEL");
                    if (modelName === false){
                        entity = MANHUNT.entity.construct.byInstEntry(instEntry);
                        if (entity === false) return;

                        MANHUNT.engine.getScene('world').add(entity.object);

                    }else {
                        if (modelName === "skybox_asylum") {
                            return;

                        }else if (modelName === "fist_poly_hunter"){
                            modelName = 'danny_asylum_bloody';
                        }

                        model = MANHUNT.relation.getModelByInst(instEntry.name);
                        // model = self._storage.mdl.find(modelName);
                        if (model === false) return;

                        entity = MANHUNT.entity.construct.byInstEntry(instEntry, model);
                        if (entity === false) return;

                        MANHUNT.engine.getScene('world').add(entity.object);
                    }


// console.log("ADD ENT", entity.name);
                    self._storage.entity.add(entity);
                    MANHUNT.relation.addEntity(entity.name, entity);
                    MANHUNT.relation.inst2Entity(entity.name, instEntry.name);
// console.log(entity.object, model);




                });
            }

            //IFP is ready
            if (self._status.chain5 && self._processed.chain5 === false ){
                console.log("[MANHUNT.level] Chain 5 loaded");
                self._processed.chain5 = true;


            }

            //All chains are done
            if (
                self._processed.chain1 && self._processed.chain2 &&
                self._processed.chain3 && self._processed.chain4 &&
                self._processed.chain5
            ){
                console.log("[MANHUNT.level] Boot Helpers");

                //allow to click on a entity model
                // MANHUNT.entityInteractive.init();

                typeof MANHUNT.sidebar.menu !== "undefined" && MANHUNT.sidebar.menu.init();

                var section = MANHUNT.sidebar.menu.getSection('entity');
                section.getView('entity-selection').setEntities(self._storage.entity.getData());

                console.log("[MANHUNT.level] Anything is loaded.");
                self._callback();
            }
        },


        getStorage: function (name) {
            return self._storage[name];
        }

    };

    self._init();

    return {
        getConfig: function(){
            return self._config;
        },
        getStorage: self.getStorage,
        load: self.load
    }
})();
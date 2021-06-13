MANHUNT.scene.Level = function (gameId, levelInfo, storage) {

    let gameInfo = MANHUNT.config.getGame(gameId);

    let self = {

        _name: levelInfo.name,

        _storage: storage,

        _views: {},

        relation: new Relation(),

        _container: {},
        _tabHandler: {},

        _init: function(){
            self._container = jQuery(jQuery('#level').html());


            MANHUNT.studio.getTabHandler().add(
                self._name,
                self._container,
                function () { }, //close
                function () {

                    //TODO: reactivate current scene, the webgl is otherwise not there

                }, //focus
                function () { } //blur
            );
            MANHUNT.studio.getTabHandler().show(self._name);

            self._tabHandler = new Tab(self._container.find('[data-id="level-tab-list"]'), self._container.find('[data-id="level-tab-content"]'));

            self._views.world = new MANHUNT.scene.WorldView(self);
            self._views.model = new MANHUNT.scene.ModelView(self);

            self._createMap();
            self._createModels();

            self._views.animation = new MANHUNT.scene.AnimationView(self);

            let player;
            if (gameInfo.game === "mh2"){
                player = self._storage.entity.find('player(player)');
            }else{
                player = self._storage.entity.find('player');
            }

            self._views.world.getSceneInfo().control.enable(player.object);
        },


        _createMap: function(){

            //sort the maps first, we need alway this order scene1->scene2->scene3
            self._storage.bsp.getData().sort(function (a, b) {
                if(a.name < b.name) { return -1; }
                if(a.name > b.name) { return 1; }
                return 0;
            }).forEach(function (scene, index) {

                if (gameInfo.game === "mh2" && index === 2){
                    //hide bbox and shadow light
                    scene.children.forEach(function (child) {
                        child.visible = false;
                    });
                }

                self._views.world.getSceneInfo().scene.add(scene);
            });
        },

        _createModels: function(){
            let sceneInfo = self._views.world.getSceneInfo();

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
                    if (modelName === false || modelName === "" || modelName === "collisionbox") return;

                    instEntry.model = false;
                    if (modelName === false) {
                        entity = MANHUNT.entity.construct.byInstEntry(instEntry);
                        if (entity === false) return;

                        sceneInfo.scene.add(entity.object);
                    }else{

                        //TODO, hardcoded level 1 stuff
                        if (modelName === "fist_poly_hunter"){
                            if (gameInfo.game === "mh2"){
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
            let sceneInfo = self._views.world.getSceneInfo();

            let model = self._storage.mdl.find(modelName);
            if (model === false) return;

            self.relation.addModel(modelName, model);
            self.relation.model2Glg(modelName, instEntry.glgRecord);
            self.relation.model2Inst(modelName, instEntry.name);

            let entity = MANHUNT.entity.construct.byInstEntry(instEntry, model);
            if (entity === false) return;


            //Hunter have a additional model
            let headRecordName = entity.record.getValue("HEAD");
            if (headRecordName !== false && headRecordName !== "no_hed"){

                let headRecordGlg = self._storage.glg.find(headRecordName);
                let headModelName = headRecordGlg.getValue("MODEL");

                let headModel = self._storage.mdl.find(headModelName);
                let headObj = headModel.get();

                entity.object.skeleton.bones.forEach(function (bone) {
                    if (bone.name === "Bip01_Head") bone.add(headObj); //mh2
                    if (bone.name === "Bip01 Head") bone.add(headObj); //mh1
                });

                self.relation.addModel(headModelName, headObj);
                self.relation.addGlg(headRecordName, headRecordGlg);

                self.relation.inst2Glg(instEntry.name, headRecordName);
                self.relation.model2Inst(headModelName, instEntry.name);
                self.relation.model2Glg(headModelName, headRecordName);
            }

            sceneInfo.scene.add(entity.object);

            self._storage.entity.add(entity);
            self.relation.addEntity(entity.name, entity);
            self.relation.inst2Entity(entity.name, instEntry.name);

        }
        
    };

    self._init();

    return {}
};
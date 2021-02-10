MANHUNT.scene.AnimationView = function (level) {


    let self = {

        _name : 'animation '+ level._name,

        _camera: new THREE.PerspectiveCamera(MANHUNT.fov, 1.33, 0.1, 1000),
        _control: MANHUNT.control.OrbitAndTransform,
        _container : {},

        _sceneInfo: {},

        _lastModels : [],
        _lastModelRow : false,

        _animation : false,
        _lastAnimationRow : false,

        _row: {},

        _filter: {},
        _template: {},
        _tabHandler: {},

        _init: function(){
            self._template.model = document.querySelector('#model-list-entry');
            self._template.animation = document.querySelector('#animation-list-entry');

            self._container = jQuery(jQuery('#view-animation').html());
            level._tabHandler.addContent(self._container);

            self._filter = self._container.find('[data-field="model-filter"]');
            self._tabHandler = new MANHUNT.frontend.Tab(self._container.find('[data-id="animation-tab-list"]'), self._container.find('[data-id="animation-tab-content"]'));

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

            //Create Main Tab
            level._tabHandler.add(
                self._name,
                self._container,
                function () { }, //close
                function () { MANHUNT.engine.changeScene(self._name); }, //focus
                function () { },  //blur
                'Animation'
            );

            level._tabHandler.show(self._name);



            //Create scene
            sceneInfo.camera.position.set(-140.83501492578623, 119.29015658522931, -73.34957947924103);

            let spotLight = new THREE.SpotLight(0xffffff);
            spotLight.position.set(1, 1, 1);
            sceneInfo.scene.add(spotLight);

            sceneInfo.scene.add(new THREE.HemisphereLight(0xffffff, 0x444444));
            sceneInfo.scene.add(new THREE.GridHelper(1000, 10, 0x888888, 0x444444));



            //Create Sub-Tab
            self._tabHandler.add(
                'Models',
                self._container.find('[data-id="model"]'),
                function () { }, //close
                function () { }, //focus
                function () { }, //blur
            );
            self._tabHandler.show('Models');


            let names = level._storage.mdl.getModelNames();
            names.forEach(function (name) {
                self._createModelEntry(name);

            });
        },

        _onUpdate: function (sceneInfo, delta) {
            self._animation !== false && self._animation.update(delta);
        },

        _createModelRow: function(animBlocks, modelName){
            self._container.find('[data-field="model-list-container"]').append(
                jQuery(self._template.model.content).clone()
            );

            let row = self._container.find('[data-field="model-list-container"]').find('li:last-child');

            self._row[modelName] = row;

            //Set model view trigger
            row.find('[data-field="name"]')
                .html(modelName)
                .click(function () {
                    self._onModelClick(row, modelName, animBlocks);
                })
            ;
        },

        _cleanup: function(){

            if (self._lastModelRow !== false) self._lastModelRow.removeClass('active');

            if (self._animation !== false) self._animation.stop();

            self._container.find('[data-field="animation-list-container"]').html('');

            //remove old objects
            if (self._lastModels.length > 0) {
                let sceneInfo = MANHUNT.engine.getSceneInfo();

                self._lastModels.forEach(function (model) {
                    sceneInfo.scene.remove(model);
                });
            }
        },

        _onModelClick: function(row, modelName, animBlocks){

            self._cleanup();

            let sceneInfo = MANHUNT.engine.getSceneInfo();

            //Generate Model Object
            let model = level._storage.mdl.find(modelName).get();

            console.log("CLIK", model);
            self._animation = new MANHUNT.ObjectAnimation(level, model);
            self._createRelatedAnim(modelName, animBlocks);
            sceneInfo.scene.add(model);

            const helper = new THREE.SkeletonHelper( model );
            sceneInfo.scene.add( helper );

            self._lastModels = [helper, model];

            //apply the model to the control
            sceneInfo.control.enable(model);

            //Active / Highlighting row
            row.addClass("active");

            self._lastModelRow = row;

        },

        _onAnimationClick: function(row, animBlock, name){

            //Active / Highlighting row
            if (self._lastAnimationRow !== false) self._lastAnimationRow.removeClass('active');
            row.addClass("active");

            self._animation.play(name, animBlock);

            self._lastAnimationRow = row;

        },

        _createAnimationRow: function(animBlock, name){
            let row = jQuery(jQuery(self._template.animation).html());

            row.find('[data-field="name"]').html(name).click(function () {
                self._onAnimationClick(row, animBlock, name);
            });

            self._container.find('[data-field="animation-list-container"]').append(row);
        },

        _createRelatedAnim: function(modelName, animBlocks ){



            let container = self._container.find('[data-id="relatedAnim"]');

            //Create Sub-Tab
            self._tabHandler.remove('Related Anim');
            self._tabHandler.add(
                'Related Anim',
                container,
                function () { }, //close
                function () { }, //focus
                function () { }, //blur
            );
            console.log("container", container);

            animBlocks.forEach(function (animBlock) {

                let names = level._storage.ifp.getNamesByGroup(animBlock);

                names = [...names].sort(function (a, b) {
                    if(a < b) { return -1; }
                    if(a > b) { return 1; }
                    return 0;
                });

                names.forEach(function (name) {
                    self._createAnimationRow(animBlock, name);
                });
            });

        },


        _createModelEntry: function( modelName ){

            let glgs = level.relation.getGlgByModel(modelName);

            if (glgs !== false){

                //Detect animation blocks
                let animBlocks = [];
                glgs.forEach(function (rel) {
                    let animBlock = rel.glg.getValue('ANIMATION_BLOCK');
                    if (animBlock === false) return;

                    if (animBlocks.indexOf(animBlock) === -1) animBlocks.push(animBlock);
                });

                //The model has animations (from GLG)
                if (animBlocks.length > 0){
                    self._createModelRow(animBlocks, modelName);
                }
            }
        }
        
    };

    self._init();

    return {
        getSceneInfo: function () {
            return self._sceneInfo;
        }

    }
};
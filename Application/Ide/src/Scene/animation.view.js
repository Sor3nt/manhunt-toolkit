MANHUNT.scene.animationView = function (level) {


    let self = {

        _name : 'animation '+ level._name,

        _camera: new THREE.PerspectiveCamera(MANHUNT.fov, 1.33, 0.1, 10000),
        _control: MANHUNT.control.OrbitAndTransform,
        _container : {},

        _sceneInfo: {},

        _lastModels : [],
        _lastRow : false,

        _row: {},

        _filter: {},
        _template: {},
        _tabHandler: {},

        _init: function(){
            self._template.model = document.querySelector('#model-list-entry');
            self._template.animation = document.querySelector('#animation-list-entry');

            self._container = jQuery(jQuery('#view-animation').html());
            MANHUNT.studio.getTabHandler().addContent(self._container);

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
            MANHUNT.studio.getTabHandler().add(
                self._name,
                self._container,
                function () { }, //close
                function () { MANHUNT.engine.changeScene(self._name); }, //focus
                function () { }  //blur
            );

            MANHUNT.studio.getTabHandler().show(self._name);



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
            level._animator.update(delta);
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

        _onModelClick: function(row, modelName, animBlocks){

            let sceneInfo = MANHUNT.engine.getSceneInfo();

            //remove old objects
            if (self._lastModels.length > 0) {
                self._lastModels.forEach(function (model) {
                    sceneInfo.scene.remove(model);
                });
            }

            //Generate Model Object
            let model = level._storage.mdl.find(modelName).get();
            self._createRelatedAnim(modelName, animBlocks);

            model.scale.set(MANHUNT.scale,MANHUNT.scale,MANHUNT.scale);
            sceneInfo.scene.add(model);

            level._animator.play(model, 'BAT_STAND_SNEAK_ANIM', 'PlayerAnims');

            const helper = new THREE.SkeletonHelper( model );
            helper.scale.set(MANHUNT.scale,MANHUNT.scale,MANHUNT.scale);
            sceneInfo.scene.add( helper );

            self._lastModels = [helper, model];

            //apply the model to the control
            sceneInfo.control.enable(model);

            //Active / Highlighting row
            if (self._lastRow !== false) self._lastRow.removeClass('active');
            row.addClass("active");

            self._lastRow = row;

        },

        _createAnimationRow: function(container){
            let row = jQuery(self._template.animation.content).clone();
            container.find('[data-field="animation-list-container"]').append(row);
            return container.find('[data-field="model-list-container"]').find('li:last-child');
        },

        _createRelatedAnim: function( modelName, animBlocks ){

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

            animBlocks.forEach(function (animBlock) {

                let names = level._storage.ifp.getNamesByGroup(animBlock);
                names.forEach(function (name) {

                });

                // console.log("animBlock", animBlock, level._storage.ifp.getNamesByGroup(animBlock));

            });

        },


        _createModelEntry: function( modelName ){

            let glgs = level.relation.getGlgByModel(modelName);
            if (glgs !== false){

                //Detect animation blocks
                let animBlocks = [];
                glgs.forEach(function (rel) {
                    let animBlock = rel.glg.getValue('ANIMATION_BLOCK');
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

    }
};
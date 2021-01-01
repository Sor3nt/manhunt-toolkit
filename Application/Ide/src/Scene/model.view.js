MANHUNT.scene.modelView = function (level) {


    var self = {

        _name : 'model '+ level._name,

        _camera: new THREE.PerspectiveCamera(MANHUNT.fov, 1.33, 0.1, 10000),
        _control: MANHUNT.control.OrbitAndTransform,
        _container : {},

        _sceneInfo: {},

        _lastModel : false,
        _lastRow : false,

        _row: {},

        _filter: {},
        _template: {},
        _templatePos: {},

        _init: function(){
            var template = document.querySelector('#view-model');
            self._template = document.querySelector('#model-list-entry');
            self._templatePos = document.querySelector('#model-list-info-position');

            var row = jQuery(template.content).clone();
            jQuery('#tab-content').append(row);
            self._container = jQuery('#tab-content').find('>div:last-child');
            self._filter = self._container.find('[data-field="model-filter"]');

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

            MANHUNT.frontend.tab.add(
                self._name,
                self._container,
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

            MANHUNT.frontend.tab.show(self._name);

            sceneInfo.camera.position.set(-140.83501492578623, 119.29015658522931, -73.34957947924103);

            var spotLight = new THREE.SpotLight(0xffffff);
            spotLight.position.set(1, 1, 1);
            sceneInfo.scene.add(spotLight);

            sceneInfo.scene.add(new THREE.HemisphereLight(0xffffff, 0x444444));
            sceneInfo.scene.add(new THREE.GridHelper(1000, 10, 0x888888, 0x444444));

            var entries = level._storage.mdl.getDataRaw();
            entries.forEach(function (entry) {
                self._createEntry(entry);

            });
        },

        _onUpdate: function (sceneInfo, delta) {

        },


        _createEntry: function( entry ){

            var row = jQuery(self._template.content).clone();
            self._container.find('[data-field="model-list-container"]').append(row);
            row = self._container.find('[data-field="model-list-container"]').find('li:last-child');

            row.find('[data-action="delete"]')
                .click(function () {
                    // self._tasks.push({
                    //     action: 'delete',
                    //     name: entry.bone.boneName
                    // });

                    row.remove();
                });

            var relsInst2Model = level.relation.getInstByModel(entry.bone.boneName);
            if (relsInst2Model === false){
                //as example heads are sub-glg records from the actual hunter
                //there is no direct inst relation
                var relsGLG = level.relation.getGlgByModel(entry.bone.boneName);

                row.addClass("unused");
            }else{

                //Detect animation and material
                var animBlocks = [];
                var matBlocks = [];

                relsInst2Model.forEach(function (rel) {
                    var glgs = level.relation.getGlgByModel(entry.bone.boneName);
                    glgs.forEach(function (rel) {

                        var mat = rel.glg.getValue('MATERIAL');
                        var animBlock = rel.glg.getValue('ANIMATION_BLOCK');

                        if(
                            animBlock !== false &&
                            animBlocks.indexOf(animBlock) === -1
                        ) {
                            row.find('[data-field="animationBlock"]').append(
                                '<span class="badge badge-info" >' + animBlock + '</span>'
                            );

                            animBlocks.push(animBlock);
                        }

                        if(
                            mat !== false &&
                            matBlocks.indexOf(mat) === -1
                        ) {
                            row.find('[data-field="material"]').append(
                                '<span class="badge badge-info" >' + mat + '</span>'
                            );
                            matBlocks.push(mat);
                        }

                    });
                });

                if (animBlocks.length > 0){
                    row.find('[data-icon="animation"]').show();
                }else{
                    row.find('[data-field="animationBlock"]').parent().remove();
                }


                //Generate Position
                relsInst2Model.forEach(function (rel) {
                    var posRow = jQuery(self._templatePos.content).clone();
                    var fieldPosition = row.find('[data-section="position"]');
                    fieldPosition.append(posRow);
                    posRow = fieldPosition.find('div:last-child');

                    posRow.find('[data-field="goto"]').click(function () {

                        var realModel = level.relation.getEntityByInst(rel.instName).object;

                        MANHUNT.frontend.tab.show('world');
                        var sceneInfo = MANHUNT.engine.getSceneInfo();
                        sceneInfo.control.enable(realModel);

                    });

                    posRow.find('[data-field="position"]').html(
                        rel.inst.position.x.toFixed(2) + ', ' +
                        rel.inst.position.y.toFixed(2) + ', ' +
                        rel.inst.position.z.toFixed(2)
                    );

                });
            }


            //Generate Textures names
            var fieldTextures = row.find('[data-field="textures"]');
            var appliedTextures = [];
            entry.objects.forEach(function (object) {
                if (object.materials.length > 0){
                    object.materials.forEach(function (material) {
                        if (appliedTextures.indexOf(material.TexName) !== -1) return;
                        appliedTextures.push(material.TexName);

                        fieldTextures.append(
                            '<span class="badge badge-info">' + material.TexName + '</span>'
                        )
                    })
                }
            });




            //Set model view trigger
            row.find('[data-field="name"]')
                .click(function () {
                    var sceneInfo = MANHUNT.engine.getSceneInfo();

                    //remove old objects
                    if (self._lastModel !== false) {
                        self._lastRow.find('[data-section="info"]').hide();
                        sceneInfo.scene.remove(self._lastModel);
                    }

                    row.find('[data-section="info"]').show();

                    // console.log("HHHHH", entities);

                    //Generate Model Object
                    var model = level._storage.mdl.find(entry.bone.boneName).get();
                    model.scale.set(MANHUNT.scale,MANHUNT.scale,MANHUNT.scale);
                    sceneInfo.scene.add(model);
                    self._lastModel = model;

                    //apply the model to the control
                    sceneInfo.control.enable(model);

                    //Active / Highlighting row
                    if (self._lastRow !== false) self._lastRow.removeClass('active');
                    row.addClass("active");

                    self._lastRow = row;

                })
                .html(entry.bone.boneName);



            // console.log(entry, entry.bone.boneName);
            if (entry.skinDataFlag === true){
                // console.log(row);
                row.find('[data-icon="skin"]').show();
            }

            self._row[entry.bone.boneName] = row;
        }
        
    };

    self._init();

    return {

    }
};
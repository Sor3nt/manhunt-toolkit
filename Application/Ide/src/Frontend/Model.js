MANHUNT.frontend.Model = function (level) {

    var self = {

        _storage: {},
        _template: {},
        _container: {},
        
        _row: {},

        _lastModel : false,
        _lastRow : false,

        _tasks : [],

        _init: function () {
            self._container = jQuery('#model-list');
            self._filter = jQuery('#model-filter');
            self._template = document.querySelector('#model-list-entry');
            self._templatePos = document.querySelector('#model-list-info-position');

            self._filter.keyup(function () {
                jQuery.each(self._row, function (name, row) {
                    if (name.indexOf(self._filter.val()) === -1)
                        row.hide();
                    else
                        row.show();
                });
            });

        },

        _createEntry: function( entry ){
            var row = jQuery(self._template.content).clone();
            self._container.append(row);
            row = self._container.find('li:last-child');

            row.find('[data-action="delete"]')
                .click(function () {
                    self._tasks.push({
                        action: 'delete',
                        name: entry.bone.boneName
                    });

                    row.remove();
                });

            var relsInst2Model = MANHUNT.relation.getInstByModel(entry.bone.boneName);
            if (relsInst2Model === false){
                //as example heads are sub-glg records from the actual hunter
                //there is no direct inst relation
                var relsGLG = MANHUNT.relation.getGlgByModel(entry.bone.boneName);

                row.addClass("unused");
            }else{

                //Detect animation and material
                var animBlocks = [];
                var matBlocks = [];

                relsInst2Model.forEach(function (rel) {
                    var glgs = MANHUNT.relation.getGlgByModel(entry.bone.boneName);
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

                        var realModel = MANHUNT.relation.getEntityByInst(rel.instName).object;

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
        },

        loadResources: function () {

            var entries = level._storage.mdl.getDataRaw();
            entries.forEach(function (entry) {
                self._createEntry(entry);

            });

            //auto select the first entry
            // self._row[Object.keys(self._row)[0]].find('[data-field="name"]').trigger('click');

        }
    };

    self._init();

    return {
        loadResources: self.loadResources,
    }
};
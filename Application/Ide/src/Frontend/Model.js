MANHUNT.frontend.model = (function () {

    var self = {

        _storage: {},
        _template: {},
        _container: {},
        
        _row: {},

        _lastModel : false,
        _lastRow : false,

        _tasks : [],

        init: function () {
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

            row.find('[data-field="name"]')
                .click(function () {
                    var sceneInfo = MANHUNT.engine.getSceneInfo();

console.log("click", entry);
                    //
                    //remove old objects
                    if (self._lastModel !== false) {
                        self._lastRow.find('[data-section="info"]').hide();
                        sceneInfo.scene.remove(self._lastModel);
                    }

                    row.find('[data-section="info"]').show();

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

                    //Generate Position
                    // var worldModel = MANHUNT.relation.model2inst[entry.bone.boneName];
                    var rels = MANHUNT.relation.getInstByModel(entry.bone.boneName);
                    rels.forEach(function (rel) {
                        var posRow = jQuery(self._templatePos.content).clone();
                        var fieldPosition = row.find('[data-section="position"]');
                        fieldPosition.append(posRow);
                        posRow = fieldPosition.find('div:last-child');

                        posRow.find('[data-field="goto"]').click(function () {

                            var realModel = MANHUNT.relation.getEntityByInst(rel.instName).object;

                            MANHUNT.frontend.tab.show('world');
                            var sceneInfo = MANHUNT.engine.getSceneInfo();
                            console.log("goto", realModel);
                            sceneInfo.control.enable(realModel);

                        });

                        posRow.find('[data-field="position"]').html(
                            rel.inst.position.x.toFixed(2) + ', ' + rel.inst.position.y.toFixed(2) + ', ' + rel.inst.position.z.toFixed(2)
                        );

console.log(posRow);
                    });
                    // console.log("HHHHH", entities);

                    //Generate Model Object
                    var model = MANHUNT.level.getStorage('mdl').find(entry.bone.boneName).get();
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

             if (entry.bone.animationDataIndex !== true){
                // console.log(row);
                row.find('[data-icon="animation"]').show();
            }

            self._row[entry.bone.boneName] = row;
        },

        loadResources: function () {
            var texStorage = MANHUNT.level.getStorage('tex');
            var mdlStorage = MANHUNT.level.getStorage('mdl');

            var entries = mdlStorage.getDataRaw();
            entries.forEach(function (entry) {
                self._createEntry(entry);

            });

            //auto select the first entry
            // self._row[Object.keys(self._row)[0]].find('[data-field="name"]').trigger('click');

        }
    };


    return {
        init: self.init,
        loadResources: self.loadResources,
    }
})();
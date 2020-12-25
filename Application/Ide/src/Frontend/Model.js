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

                    //remove old objects
                    if (self._lastModel !== false) sceneInfo.scene.remove(self._lastModel);

                    var model = MANHUNT.level.getStorage('mdl').find(entry.bone.boneName).get();
                    model.scale.set(MANHUNT.scale,MANHUNT.scale,MANHUNT.scale);
                    sceneInfo.scene.add(model);
                    self._lastModel = model;

                    sceneInfo.control.enable(model);

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
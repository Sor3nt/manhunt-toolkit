MANHUNT.sidebar.menu = (function () {

    var self = {

        _elements: {
            sections: {},
            container: {},
            selections: {},
        },

        _callback: {
            onObjectChanged: []
        },

        _section : {},
        _activeSection : false,
        _activeObject : false,

        init: function () {
            self._elements.container = jQuery('#menu');
            self._elements.sections = jQuery('#sections');
            self._elements.selections = jQuery('#selection');

            self._createSections();

            self.showSection('world');
        },

        _createSections: function(){
            self._section.world = new MANHUNT.sidebar.Section({
                name: 'World',
                icon: '🌎'
            });
            self._section.world.addView('scene-selection', new MANHUNT.sidebar.view.SceneSelection());

            //todo: hide section für entity bauen basierend auf den inst class names
            //sprich alle hunter, alle basic....

            self._section.entity = new MANHUNT.sidebar.Section({
                name: 'Entity',
                icon: '🔎'
            });

            self._section.entity.addView('entity-selection', new MANHUNT.sidebar.view.EntitySelection());
            self._section.entity.addView('info-block', new MANHUNT.sidebar.view.InfoBlock());
            self._section.entity.addView('xyz', new MANHUNT.sidebar.view.Xyz());


            self._section.trigger = new MANHUNT.sidebar.Section({
                name: 'Trigger',
                icon: '🎌'
            });


            self._section.tvp = new MANHUNT.sidebar.Section({
                name: 'Timed Vector Pair',
                icon: '📽️'
            });

            //Append all sections into the sidebar
            for(var i in self._section){
                if (!self._section.hasOwnProperty(i)) continue;
                self._elements.selections.append(self._section[i].sectionButton);
                self._elements.sections.append(self._section[i].container);

                (function(element, sectionIndex){
                    element.click(function () {
                        MANHUNT.sidebar.menu.showSection(sectionIndex);
                    });
                })(self._section[i].sectionButton, i);
            }
        },

        showSection: function(section){
            if(typeof self._section[section] === "undefined") return false;

            if (self._activeSection !== false) self._activeSection.hide();
            self._activeSection = self._section[section];

            self._activeSection.show();

        },

        getSection: function (section) {
            if(typeof self._section[section] === "undefined") return false;
            return self._section[section];
        },

        object: function (object) {
            if (typeof object === "undefined") return self._activeObject;
            self._activeObject = object;

            self._callback.onObjectChanged.forEach(function (callback) {
                callback(object);
            })
        }


    };


    return {
        onObjectChanged: function(callback){
            self._callback.onObjectChanged.push(callback);
        },
        object: self.object,
        init: self.init,
        showSection: self.showSection,
        getSection: self.getSection
    }
})();
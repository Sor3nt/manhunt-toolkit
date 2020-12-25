MANHUNT.sidebar.view.EntitySelection = function () {
    var base = new MANHUNT.sidebar.view.construct();

    var self = Object.assign(base, {

        _elements: {
            container: {}
        },

        _entities : {},
        _dropdown : false,

        _init: function () {

            var container = jQuery('<div>');
            container.addClass("view entity-selection");
            self._elements.container = container;

            self._dropdown = MANHUNT.sidebar.elements.Dropdown();
            self._dropdown.onChangeCallback(self.onEntitySelected);

            self._dropdown.container.hide();
            container.append(self._dropdown.container);
        },

        onEntitySelected: function(event){
            var entity = self._entities[event.target.value];
            console.log('[MANHUNT.sidebar.view.EntitySelection] Look at ', entity.name);

            MANHUNT.camera.lookAt(entity.object);
            MANHUNT.control.active('transform');
            MANHUNT.sidebar.menu.object(entity.object);

            // var entitySection = MANHUNT.sidebar.menu.getSection('entity');
            // entitySection.getView('xyz').update();
        },

        setEntities: function( entities ){

            var names = [];
            entities.forEach(function (entity) {
                self._entities[entity.name] = entity;
                names.push(entity.name);
            });

            names.sort();

            self._dropdown.setValues(names);

            self._dropdown.container.show();
        },


        update: function () {
            if (self._object === false) return false;

        },

    });


    self._init();

    return {
        setEntities: self.setEntities,
        hide: self.hide,
        show: self.show,
        container: self._elements.container,
        update: self.update
    }
};
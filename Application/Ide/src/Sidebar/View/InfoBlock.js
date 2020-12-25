MANHUNT.sidebar.view.InfoBlock = function () {
    var base = new MANHUNT.sidebar.view.construct();

    var self = Object.assign(base, {

        _values: {},

        _init: function () {

            var container = jQuery('<div>');
            container.addClass("view info-block");
            self._elements.container = container;

            self._values = new MANHUNT.sidebar.elements.AttributeValue({
                'Entity': '',
                'Record': '',
                'Model': '',
            });
            //
            // self._elements.scene = MANHUNT.sidebar.elements.InputGroup({
            //     world: true,
            //     transparent: true,
            //     shadow: false,
            //     bbox: false
            // }, 'checkbox', self.onSelectionChanged);

            container.append(self._values.container);
        },

        onObjectChanged: function (object) {
            console.log("ehh", object.entity);
            self._values.updateValue('Entity', object.entity.name);
            self._values.updateValue('Record', object.entity.settings.glgRecord);
            self._values.updateValue('Model', object.entity.record.getValue('MODEL'));
        }


    });


    self._init();


    MANHUNT.sidebar.menu.onObjectChanged(self.onObjectChanged);


    return {
        hide: self.hide,
        show: self.show,
        container: self._elements.container
    }
};
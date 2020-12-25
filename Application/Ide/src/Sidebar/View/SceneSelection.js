MANHUNT.sidebar.view.SceneSelection = function () {
    var base = new MANHUNT.sidebar.view.construct();

    var self = Object.assign(base, {

        _elements: {
            container: {},
            scene: {}
        },

        _init: function () {

            var container = jQuery('<div>');
            container.addClass("view scene-selection");
            self._elements.container = container;

            self._elements.scene = MANHUNT.sidebar.elements.InputGroup({
                world: true,
                transparent: true,
                shadow: false,
                bbox: false
            }, 'checkbox', self.onSelectionChanged);

            container.append(self._elements.scene.container);
        },

        onSelectionChanged: function(event){
            var fieldName = event.target.name;
            var enable = event.target.checked;

            switch(fieldName){
                case 'world':
                    MANHUNT.level.getStorage('bsp').find('scene1').visible = event.target.checked;
                    break;
                case 'transparent':
                    MANHUNT.level.getStorage('bsp').find('scene2').visible = event.target.checked;
                    break;
                case 'shadow':
                    MANHUNT.level.getStorage('bsp').find('scene3').children.forEach(function (child) {
                        if (child.name === "preligh") child.visible = enable;
                    });
                    break;
                case 'bbox':
                    MANHUNT.level.getStorage('bsp').find('scene3').children.forEach(function (child) {
                        if (child.name === "bbox") child.visible = enable;
                    });
                    break;
            }
        }

    });


    self._init();

    return {
        hide: self.hide,
        show: self.show,
        container: self._elements.container
    }
};
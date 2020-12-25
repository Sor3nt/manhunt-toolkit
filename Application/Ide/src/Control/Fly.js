MANHUNT.control.Fly = function (sceneInfo) {

    var self = {

        _active: false,
        _control: {},

        _init: function () {

            var renderer = MANHUNT.engine.getRenderer();

            self._control.fly = new FlyControls( sceneInfo.camera, renderer.domElement );
            self._control.fly.movementSpeed = 800;
            self._control.fly.domElement = renderer.domElement;
            self._control.fly.rollSpeed = Math.PI / 8;
            self._control.fly.autoForward = false;
            self._control.fly.dragToLook = false;
            self._control.fly.mousedown = function(){};
            self._control.fly.mouseup = function(){};
        },

        update: function( delta ){
            if (self._active === false) return;
            self._control.fly.update(delta);
        },

        enable: function (state) {
            if (typeof state === "undefined") return self._active;

            self._active = state;
        }
    };

    self._init();

    return {
        update: self.update,
        enable: self.enable
    }
};
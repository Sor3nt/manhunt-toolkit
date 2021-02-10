MANHUNT.control.OrbitAndTransform = function (sceneInfo) {

    var self = {


        _active: false,
        _control: {},

        _init: function () {

            var renderer = MANHUNT.engine.getRenderer();
            self._control.transform = new TransformControls( sceneInfo.camera, renderer.domElement );

            self._control.orbit = new OrbitControls( sceneInfo.camera, renderer.domElement );
            self._control.orbit.enableDamping = true;
            self._control.orbit.dampingFactor = 0.05;
            self._control.orbit.screenSpacePanning = false;
            self._control.orbit.minDistance = 0.5 ;
            self._control.orbit.maxDistance = 4.0 ;
            self._control.orbit.maxPolarAngle = Math.PI / 2;
            self._control.orbit.enabled = false;

            self._createEvents();

            sceneInfo.scene.add( self._control.transform );
        },

        _createEvents: function(){

            self._control.transform.addEventListener( 'change', function ( event ) {
                if (self._active === false) return;

                // var section = MANHUNT.sidebar.menu.getSection('entity');
                // section && section.getView('xyz').update();
            } );

            self._control.transform.addEventListener( 'dragging-changed', function ( event ) {
                self._control.orbit.enabled = ! event.value;
            } );

        },

        update: function( delta ){
            if (self._active === false) return;

            self._control.orbit.update();
        },

        enable: function (object) {
            if (typeof object === "undefined") return self._active;

            if (self._active !== false){
                self._control.orbit.enabled = false;
                self._control.transform.detach( self._active );
            }

            if(object !== false){
                self._control.orbit.enabled = true;
                self._control.orbit.target.copy(object.position);
                self._control.transform.attach( object );
                self._active = object;

                self._control.orbit.update();
            }
        }
    };

    self._init();

    return {
        update: self.update,
        enable: self.enable
    }
};
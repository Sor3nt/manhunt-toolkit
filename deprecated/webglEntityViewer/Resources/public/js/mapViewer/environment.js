function Environment(){

    var self = {

        camera: {},
        scene: {},
        renderer: {},

        _init : function () {

            //PerspectiveCamera( fov : Number, aspect : Number, near : Number, far : Number )
            self.camera = new THREE.PerspectiveCamera( 75, window.innerWidth / window.innerHeight, 1, 100000 );

            self.scene = new THREE.Scene();
            self.scene.background = new THREE.Color( 0xffffff );

            self._createLight();
            self._createRenderer();
        },

        _createLight : function () {
            var light = new THREE.HemisphereLight( 0xeeeeff, 0x777788, 0.75 );
            light.position.set( 0.5, 1, 0.75 );
            self.scene.add( light );
        },

        _createRenderer: function () {

            self.renderer = new THREE.WebGLRenderer( { antialias: true } );
            self.renderer.setPixelRatio( window.devicePixelRatio );
            self.renderer.setSize( window.innerWidth, window.innerHeight );
            document.body.appendChild( self.renderer.domElement );
        }

    };

    self._init();

    return {
        camera: self.camera,
        renderer: self.renderer,
        scene: self.scene,
        worldScale: 48
    };

}
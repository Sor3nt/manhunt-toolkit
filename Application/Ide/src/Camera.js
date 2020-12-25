MANHUNT.camera = (function () {

    var self = {
        _camera: {},
        _active : 'perpective',
        _lookAt: null,

        _init: function () {
            var width = jQuery(MANHUNT.engine.container()).width();
            var aspect = width / width;

            self._camera.perpective = new THREE.PerspectiveCamera(MANHUNT.fov, aspect, 0.1, 10000);

        },

        update: function(){
            if (self._lookAt === null) return;
            var camera = self.getCamera();

            if (MANHUNT.control.active() === "transform"){
                camera.lookAt( self._lookAt.position );
                return;
            }

            if (MANHUNT.control.active() !== "default") return;

            var relativeCameraOffset = new THREE.Vector3(0,2,-3);

            self._lookAt.updateMatrixWorld();

            var cameraOffset = relativeCameraOffset.applyMatrix4( self._lookAt.matrixWorld );

            camera.position.lerp(cameraOffset, 0.1);
            camera.lookAt( self._lookAt.position );

        },

        setCameraType: function(type){
            self._active = type;
        },

        setPosition: function(vec){
            var camera = self.getCamera();
            camera.position.x = vec.x;
            camera.position.y = vec.y;
            camera.position.z = vec.z;
        },


        lookAt: function(object){
            if (typeof object === "undefined") return self._lookAt;
            self._lookAt = object;
        },

        getCamera: function () {
            return self._camera[self._active];
        }
    };


    return {
        init : self._init,
        setPosition: self.setPosition,
        lookAt: self.lookAt,
        setCameraType: self.setCameraType,
        getCamera: self.getCamera,
        update: self.update
    }
})();
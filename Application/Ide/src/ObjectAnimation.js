MANHUNT.ObjectAnimation = function (level, model) {

    let self = {

        _mixer: new THREE.AnimationMixer(model),

        play: function( animationName, animationBlock){

            let clip = level._storage.ifp.find(animationBlock, animationName);

            self.stop();
            self._mixer.clipAction( clip ).play();
        },

        stop: function(){
            self._mixer.stopAllAction();
        },

        update: function (delta) {
            self._mixer.update( delta );
        }

    };

    return {
        play: self.play,
        stop: self.stop,
        update: self.update
    }
};
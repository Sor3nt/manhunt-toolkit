MANHUNT.ObjectAnimation = function (level, model) {

    let self = {

        _mixer: new THREE.AnimationMixer(model),

        play: function( animationName, animationBlock){

            let clip = level._storage.ifp.find(animationBlock, animationName);

            self._mixer.stopAllAction();
            self._mixer.clipAction( clip ).play();
        },

        update: function (delta) {
            self._mixer.update( delta );
        }

    };

    return {
        play: self.play,
        update: self.update
    }
};
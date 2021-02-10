MANHUNT.ObjectAnimation = function (level, model) {

    let self = {

        _mixer: new THREE.AnimationMixer(model),

        playClip: function( clip ){
            self.stop();
            self._mixer.timeScale = 1;
            self._mixer.clipAction( clip ).play();

        },

        play: function( animationName, animationBlock){

            let clip = level._storage.ifp.find(animationBlock, animationName);
            self.playClip(clip);
        },

        stop: function(){
            self._mixer.stopAllAction();
        },

        pause: function(){
            self._mixer.timeScale = 0;
        },

        update: function (delta) {
            self._mixer.update( delta );
        }

    };

    return {
        play: self.play,
        pause: self.pause,
        playClip: self.playClip,
        stop: self.stop,
        update: self.update
    }
};
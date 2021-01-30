MANHUNT.animator = function (level) {

    //TODO: https://github.com/mrdoob/three.js/blob/master/examples/webgl_animation_skinning_additive_blending.html

    var self = {

        _mixer: {},
        _action: {},
        _clip: {},

        play: function( model, animationName, animatioBlock){

            var animIndex = model.uuid + '_' + animationName;

            //load animation clip
            if (typeof self._clip[animationName] === "undefined")
                self._clip[animationName] = level._storage.ifp.find(animatioBlock, animationName);

            if (typeof self._mixer[model.uuid] === "undefined")
                self._mixer[model.uuid] = new THREE.AnimationMixer(model);

            if (typeof self._action[animIndex] === "undefined")
                self._action[animIndex] = self._mixer[model.uuid].clipAction( self._clip[animationName] );

            self._action[animIndex].play();
        },

        update: function (delta) {

            for(var i in self._mixer){
                if (!self._mixer.hasOwnProperty(i)) continue;
                self._mixer[i].update( delta );
            }
        }

    };

    return {
        play: self.play,
        update: self.update
    }
};
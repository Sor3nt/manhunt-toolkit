MANHUNT.animator = (function () {

    //TODO: https://github.com/mrdoob/three.js/blob/master/examples/webgl_animation_skinning_additive_blending.html

    var self = {

        _mixer: {},
        _action: {},
        _clip: {},

        play: function( entity, animationName){

            if (entity.hasAnimation === false){
                console.error('[MANHUNT.animator] try to play animation', animationName, 'on an entity which has no animation setting', entity);
                return;
            }

            if (typeof entity.animatioBlock !== "string"){
                console.error('[MANHUNT.animator] given animationBlock is not valid for entity', entity);
                return;
            }
            var obj = entity.lod.getLOD(0);
            var animIndex = obj.uuid + '_' + animationName;

            // if (typeof self._clip[animIndex] === "undefined"){
                //load animation clip
                self._clip[animIndex] = MANHUNT.level.getStorage('ifp').find(entity.animatioBlock, animationName);
            // }

            if (typeof self._mixer[obj.uuid] === "undefined")
                self._mixer[obj.uuid] = new THREE.AnimationMixer(obj);
            else console.error("das kann nicht sein 1");


            if (typeof self._action[animIndex] === "undefined"){
                self._action[animIndex] = self._mixer[obj.uuid].clipAction( self._clip[animIndex] );
            }

            self._action[animIndex].play();
        },

        addMixer: function(uuid, mixer){
            self._mixer[uuid] = mixer;
        },

        update: function (delta) {

            for(var i in self._mixer){
                if (!self._mixer.hasOwnProperty(i)) continue;
               // console.log("update mixer", self._mixer[i]);
                self._mixer[i].update( delta );
            }

            //consle.log(asd);

        }

    };

    return {
        addMixer: self.addMixer,
        play: self.play,
        update: self.update
    }
})();

MANHUNT.levelScript.functions = (function () {

    var self = {

        /**
         * Helper/Proxy Manhunt 2 function
         */

        _visible: function (entity, state) {
            entity.object.visible = state;
        },

        _sleep: function (ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        },


        /**
         * Original Manhunt 2 function
         */

        switchLightOn: function (entity) {
            self._visible(entity, true);
        },

        switchLightOff: function (entity) {
            self._visible(entity, false);
        },

        sleep:  function (ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        },

        randNum: function (max) {
            return  Math.floor(Math.random() * (max + 1));
        },

        getEntity: function (name) {
            return MANHUNT.level.getStorage('entity').find(name);
        },

        setCurrentLOD: function (entity, index) {

            // var vec4 = entity.record.getValues("LOD_DATA", index);
            //
            // if (index === 0){
            //     entity.object.children.forEach(function (child) {
            //         child.visible = false;
            //     })
            // }else{
            //
            // }
            // entity.object.userData.LODIndex = index;
            entity.lod.enableLOD(index);
        }
    };

    return {
        switchLightOn: self.switchLightOn,
        switchLightOff: self.switchLightOff,
        sleep: self.sleep,
        randNum: self.randNum,
        getEntity: self.getEntity,
        setCurrentLOD: self.setCurrentLOD,
    }
})();
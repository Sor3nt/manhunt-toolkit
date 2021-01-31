MANHUNT.scene.views = (function () {


    let self = {

        load: function(level, name){
            return new MANHUNT.scene[name](level);
        },

    };

    return {
        load: self.load
    }
})();
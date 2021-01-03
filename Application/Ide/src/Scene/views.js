MANHUNT.scene.views = (function () {


    var self = {

        _level: {

        },

        load: function(level, name){
            var view = new MANHUNT.scene[name](level)
        },

        loadLevel: function(game, levelName, callback){

            switch (game) {

                case 'manhunt':
                    self._level[game + '_' + levelName] = new MANHUNT.scene.ManhuntLevel(levelName, callback);
                    break;

                case 'manhunt2':
                    self._level[game + '_' + levelName] = new MANHUNT.scene.Manhunt2Level(levelName, callback);
                    break;

            }

        },

        getLevel: function (game, levelName) {
            return self._level[game + '_' + levelName];
        }

    };

    return {
        load: self.load,
        loadLevel: self.loadLevel,
        getLevel: self.getLevel
    }
})();
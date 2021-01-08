
MANHUNT.storage.Model = function (level) {
    var self = {
        _data: {},

        _proxy : [],

        _loadedFiles : [],

        load: function(file, callback){
            if (self._loadedFiles.indexOf(file) !== -1){
                callback();
                return
            }

            jQuery('#loading-text').html(file);
            self._loadedFiles.push(file);

            MANHUNT.loader.load(level, 'mdl', file, function (proxy) {
                self._proxy.push(proxy);
                callback();
            });
        },

        getModelNames: function(){

            var names = [];

            self._proxy.forEach(function (proxy) {
                proxy.getModelNames().forEach(function (name) {
                    names.push(name);
                });
            });

            return names;

        },

        find: function (name) {

            var found = false;

            self._proxy.forEach(function (proxy) {
                if (found !== false) return;

                found = proxy.find(name);
            });

            if (found === false){
                console.log('[MANHUNT.Storage.Model','] Unable to find model', name);
                return false;
            }

            return  {
                    get: function () {
                        return found;
                    },

                    LODLength: found.children.length,

                    enableLOD: function(lodIndex){
                        found.children[found.userData.LODIndex].visible = false;
                        found.userData.LODIndex = lodIndex;
                        found.children[found.userData.LODIndex].visible = true;
                    },

                    getLOD: function (lodIndex) {
                        return found.children[lodIndex];

                    }
                };

        }

    };

    return {
        load: self.load,
        find: self.find,
        getModelNames: self.getModelNames
    }
};
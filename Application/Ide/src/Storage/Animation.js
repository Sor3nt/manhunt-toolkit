
MANHUNT.storage.Animation = function () {
    var self = {
        _data: {},

        _proxy : {},

        _loadedFiles: [],

        load: function(file, callback){
            if (self._loadedFiles.indexOf(file) !== -1){
                callback();
                return
            }

            MANHUNT.loader.load('ifp', file, function (proxy) {
                self._proxy = proxy;
                callback();
            });
        },


        find: function (group, name) {

            var index = group + '_' + name;

            if (typeof self._data[index] === "undefined"){
                self._data[index] = self._proxy.find(group, name);
            }

            if (self._data[index] === false){
                console.log('[MANHUNT.Storage.IFP','] Unable to find animation', name);
                return false;
            }

            return self._data[index];
        }

    };

    return {
        load: self.load,
        find: self.find
    }
};
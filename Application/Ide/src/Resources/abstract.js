MANHUNT.resources.Abstract = function (levelName, doneCallback) {

    let self = {

        _storage: {},
        _content : {},
        
        _init: function(){

            let storage = new MANHUNT.storage.Storage(self);

            self._storage.ifp = storage.create('Animation');
            self._storage.mdl = storage.create('Model');
            self._storage.tex = storage.create('tex');
            self._storage.bsp = storage.create('bsp');
            self._storage.glg = storage.create('glg');
            self._storage.inst = storage.create('inst');
            self._storage.entity = storage.create();

            self._buildChain();
        },


        loadChainFiles: function(entries, callback){

            let wait = 0;

            jQuery.each(entries, function (loader, files) {

                jQuery.each(files, function (fileId, file) {
                    wait++;

                    self._storage[loader].load(file, function () {
                        wait--;

                        if (wait === 0){
                            callback();
                        }

                    })

                });

            });

        },

        _processChain: function(chain){

            let promise = new Promise(function(okCallback){
                okCallback();
            });

            jQuery.each(chain, function (chainId, part) {

                promise = promise.then(function () {


                    let innerPromise = new Promise(function (okCallback) {
                        okCallback();
                    });

                    jQuery.each(part.order, function (orderIndex, order) {
                        innerPromise = innerPromise.then(function () {
                            return new Promise(function (okCallback) {
                                self.loadChainFiles(order, function () {
                                    okCallback();
                                })

                            });

                        });
                    });

                    innerPromise = innerPromise.then(function () {
                        return new Promise(function (okCallback) {
                            part.callback();
                            okCallback();
                        });
                    });

                    return innerPromise;
                });
            });


            promise.then(function () {
                return new Promise(function (okCallback) {
                    doneCallback(self._storage);
                    okCallback();
                });
            });

        }

    };

    return self;
};
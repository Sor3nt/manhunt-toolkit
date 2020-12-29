
MANHUNT.storage.Storage = function (level) {

    var self = {

        create: function (name) {

            switch (name) {

                case 'Animation':
                case 'Model':
                    return new MANHUNT.storage[name](level);

                default:
                    return new MANHUNT.storage.Default(level, name);

            }

        }



    };

    return {
        create: self.create

    }
};
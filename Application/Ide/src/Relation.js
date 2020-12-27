MANHUNT.relation = (function () {

    var self = {

        _model: {},
        _texture: {},
        _inst: {},
        _glg: {},
        _entity: {},

        model2glg: {},
        model2inst: {},

        inst2glg: {},
        inst2model: {},
        inst2entity: {},

        glg2model: {},
        glg2inst: {},


        //N:N => return X results
        getGlgByModel: function(modelName){
            if (typeof self.model2glg[modelName] === "undefined") return false;
            //
            // var result = [];
            // self.model2inst[modelName].forEach(function (rel) {
            //     result.push(rel.inst);
            // });

            return self.model2glg[modelName] || false;

        },
        //N:N => return X results
        getInstByModel: function(modelName){
            if (typeof self.model2inst[modelName] === "undefined") return false;
            //
            // var result = [];
            // self.model2inst[modelName].forEach(function (rel) {
            //     result.push(rel.inst);
            // });

            return self.model2inst[modelName] || false;

        },


        //1:1 => return 1 result
        getEntityByInst: function(instName){
            if (typeof self.inst2entity[instName] === "undefined") return false;
            return self.inst2entity[instName].entity;

        },

        //1:N => return 1 result
        getGlgByInst: function(instName){
            if (typeof self.inst2glg[instName] === "undefined") return false;
            return self.inst2glg[instName].glg;

        },

        //N:N => return 1 result because its always the same model
        getModelByInst: function(instName){
            if (typeof self.inst2model[instName] === "undefined") return false;
            return self.inst2model[instName][0].model;

        },

        model2Inst: function(modelName, instName){
            if (typeof self.model2inst[modelName] === "undefined")
                self.model2inst[modelName] = [];

            if (typeof self.inst2model[instName] === "undefined")
                self.inst2model[instName] = [];

            var rel = {
                instName: instName,
                inst: self._inst[instName],

                modelName: modelName,
                model: self._model[modelName]
            };

            // console.log("add rel", rel);

            self.model2inst[modelName].push(rel);
            self.inst2model[instName].push(rel);
        },

        inst2Glg: function(instName, glgName){
            // if (typeof self.inst2glg[instName] === "undefined")
            //     self.inst2glg[instName] = [];

            if (typeof self.glg2inst[glgName] === "undefined")
                self.glg2inst[glgName] = [];

            var rel = {
                instName: instName,
                inst: self._inst[instName],

                glgName: glgName,
                glg: self._glg[glgName]
            };

            self.inst2glg[instName] = rel;
            self.glg2inst[glgName].push(rel);
        },

        inst2Entity: function(instName, entityName){

            var rel = {
                instName: instName,
                inst: self._inst[instName],

                entityName: entityName,
                entity: self._entity[entityName]
            };

            self.inst2entity[instName] = rel;
        },

        model2Glg: function(modelName, glgName){
            if (typeof self.model2glg[modelName] === "undefined")
                self.model2glg[modelName] = [];

            if (typeof self.glg2model[glgName] === "undefined")
                self.glg2model[glgName] = [];

            var rel = {
                modelName: modelName,
                model: self._model[modelName],

                glgName: glgName,
                glg: self._glg[glgName]
            };

            self.model2glg[modelName].push(rel);
            self.glg2model[glgName].push(rel);
        },

        addTexture: function ( name, object) {
           self._texture[name] = object;
        },

        addModel: function ( name, object) {
           self._model[name] = object;
        },

        addInst: function ( name, object) {
           self._inst[name] = object;
        },

        addGlg: function ( name, object) {
           self._glg[name] = object;
        },

        addEntity: function ( name, object) {
           self._entity[name] = object;
        },

    };


    return self;
})();
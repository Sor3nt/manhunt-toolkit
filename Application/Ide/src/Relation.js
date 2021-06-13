
export default class Relation{
    constructor() {
        this.data = {
            model: {},
            texture: {},
            inst: {},
            glg: {},
            entity: {},
        };

        this.relation = {
            model2glg: {},
            model2inst: {},

            inst2glg: {},
            inst2model: {},
            inst2entity: {},

            glg2model: {},
            glg2inst: {},
        };
    }

    //N:N => return X results
    getGlgByModel(modelName){
        if (typeof this.relation.model2glg[modelName] === "undefined") return false;

        return this.relation.model2glg[modelName] || false;
    }

    //N:N => return X results
    getInstByModel(modelName){
        if (typeof this.relation.model2inst[modelName] === "undefined") return false;

        return this.relation.model2inst[modelName] || false;
    }

    //1:1 => return 1 result
    getEntityByInst(instName){
        if (typeof this.relation.inst2entity[instName] === "undefined") return false;
        return this.relation.inst2entity[instName].entity;

    }

    //1:N => return 1 result
    getGlgByInst(instName){
        if (typeof this.relation.inst2glg[instName] === "undefined") return false;
        return this.relation.inst2glg[instName].glg;
    }

    //N:N => return 1 result because its always the same model
    getModelByInst(instName){
        if (typeof this.relation.inst2model[instName] === "undefined") return false;
        return this.relation.inst2model[instName][0].model;
    }

    model2Inst(modelName, instName){
        if (typeof this.relation.model2inst[modelName] === "undefined")
            this.relation.model2inst[modelName] = [];

        if (typeof this.relation.inst2model[instName] === "undefined")
            this.relation.inst2model[instName] = [];

        let rel = {
            instName: instName,
            inst: this.data.inst[instName],

            modelName: modelName,
            model: this.data.model[modelName]
        };

        this.relation.model2inst[modelName].push(rel);
        this.relation.inst2model[instName].push(rel);
    }

    inst2Glg(instName, glgName){
        if (typeof this.relation.glg2inst[glgName] === "undefined")
            this.relation.glg2inst[glgName] = [];

        let rel = {
            instName: instName,
            inst: this.data.inst[instName],

            glgName: glgName,
            glg: this.data.glg[glgName]
        };

        this.relation.inst2glg[instName] = rel;
        this.relation.glg2inst[glgName].push(rel);
    }

    inst2Entity(instName, entityName){
        this.relation.inst2entity[instName] = {
            instName: instName,
            inst: this.data.inst[instName],

            entityName: entityName,
            entity: this.data.entity[entityName]
        };
    }

    model2Glg(modelName, glgName){
        if (typeof this.relation.model2glg[modelName] === "undefined")
            this.relation.model2glg[modelName] = [];

        if (typeof this.relation.glg2model[glgName] === "undefined")
            this.relation.glg2model[glgName] = [];

        let rel = {
            modelName: modelName,
            model: this.data.model[modelName],

            glgName: glgName,
            glg: this.data.glg[glgName]
        };

        this.relation.model2glg[modelName].push(rel);
        this.relation.glg2model[glgName].push(rel);
    }

    addTexture( name, object) {
        this.data.texture[name] = object;
    }

    addModel( name, object) {
        this.data.model[name] = object;
    }

    addInst( name, object) {
        this.data.inst[name] = object;
    }

    addGlg( name, object) {
        this.data.glg[name] = object;
    }

    addEntity( name, object) {
        this.data.entity[name] = object;
    }

}

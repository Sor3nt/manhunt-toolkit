MANHUNT.entity.Hunter = function ( instEntity, model ) {

    var base = new MANHUNT.entity.abstract(instEntity, model.getLOD(0), model);


    var headRecordName = base.record.getValue("HEAD");
    if (headRecordName !== false && headRecordName !== "no_hed"){

        var headRecordGlg = MANHUNT.level.getStorage('glg').find(headRecordName);
        var headModelName = headRecordGlg.getValue("MODEL");

        var headModel = MANHUNT.level.getStorage('mdl').find(headModelName);
        var headObj = headModel.get();

        //TODO: WHY do i need this ?!
        headObj.traverse( function ( object ) {
            if ( object.isMesh ) object.scale.set(1,1,1);
        } );

        base.object.skeleton.bones.forEach(function (bone) {
            if (bone.name === "Bip01_Head") bone.add(headObj);
        });

        MANHUNT.relation.addModel(headModelName, headObj);
        MANHUNT.relation.addGlg(headRecordName, headRecordGlg);

        MANHUNT.relation.inst2Glg(instEntity.name, headModelName);
        MANHUNT.relation.model2Inst(headModelName, instEntity.name);
        MANHUNT.relation.model2Glg(headModelName, headRecordName);

    }

    return Object.assign(base, {


    });
};
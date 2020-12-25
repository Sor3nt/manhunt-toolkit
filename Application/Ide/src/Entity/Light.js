MANHUNT.entity.Light = function ( entity, model ) {

    var glg = MANHUNT.level.getStorage('glg').find(entity.glgRecord);
    var lod = glg.getValue('LOD_DATA');

    var targetPos = new THREE.Vector3(
        lod.x,
        lod.y,
        lod.z
    );

    var container = new THREE.Object3D();

    // var spotLight = new THREE.SpotLight( new THREE.Color(0xff55ff), 1 );
    // // spotLight.position.set( 15, 40, 35 );
    // spotLight.angle = Math.PI / 4;
    // spotLight.penumbra = 0.1;
    // spotLight.decay = 2;
    // spotLight.distance = 200;
    //
    // // scene.add( spotLight );
    //

    //
    // var light = new THREE.SpotLight(
    //     new THREE.Color(entity.settings.colourRed, entity.settings.colourGreen, entity.settings.colourBlue),
    //     1
    // );

    /*

affectsMap: 0
affectsObjects: 1
attenuationRadius: 1
colourBlue: 114
colourGreen: 124
colourRed: 124
coneAngle: 20
createsCharacterShadows: 1
effectDuration: 4294967295
fadeContinously: 0
fadeInTimeInMs: 0
fadeOutTime: 0
flickerStrobeOffTimeInMs: 300
flickerStrobeOnTimeInMs: 300
hasLensflare: 1
hasSearchlightCone: 0
isRealLight: 1
lensflareIntensity: 115
lensflareSize: 0.5
lightEffectType: 0
lightFog: 0
lightType: 3
notClimbable: 0
switchOffAfterDuration: 0
switchOnByDefault: 1
unk_bf4d0100: 25.000001907348633

     */

    var light = new THREE.PointLight(
        new THREE.Color(entity.settings.colourRed, entity.settings.colourGreen, entity.settings.colourBlue),
        // new THREE.Color(0xff55ff),
        1, 1000, 25
    );
    light.position.set(
        entity.position.x * MANHUNT.scale,
        entity.position.y * MANHUNT.scale,
        entity.position.z * MANHUNT.scale
    );
    // light.target.position.copy(light.position);
    // MANHUNT.engine.getScene().add(light.target);
    // light.target.position.sub(targetPos);

//     light.angle = 1.3; //Math.PI/entity.coneAngle;
//     light.penumbra = entity.settings.attenuationRadius;
// //     light.decay = 1;
//     light.distance = -entity.settings.unk_bf4d0100;
console.log("LIGHT en", light, entity);
    // window.spotLightHelper = new THREE.SpotLightHelper( light );
    // MANHUNT.engine.getScene().add( spotLightHelper );

    var base = new MANHUNT.entity.abstract(entity, light, light);


    // console.log(target.position, light.position);

    //
    //
    //
    //
    //
    //
    // const geometry = new THREE.BoxGeometry( 10 / 48, 10 / 48, 10 / 48 );
    // const material = new THREE.MeshBasicMaterial( {color: 0x00ff00} );
    // model = new THREE.Mesh( geometry, material );
    // target.add(model);
    //
    // target.position.x = targetPos.x;
    // target.position.y = targetPos.y;
    // target.position.z = targetPos.z;
    //
    // container.add(light);
    // // container.add(target);
    //
    //
    // // light.target = targetPos;
    // model.add(container);

    return Object.assign(base, {



    });
};
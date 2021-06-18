import EntityAbstract from "./Entity.js";

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

export default class Light extends EntityAbstract{

    constructor(instEntity){

        let light = new THREE.PointLight(
            new THREE.Color(instEntity.settings.colourRed, instEntity.settings.colourGreen, instEntity.settings.colourBlue),
            1, 1000, 25
        );

        light.position.set(
            instEntity.position.x,
            instEntity.position.y,
            instEntity.position.z
        );

        super(instEntity, light, light);

    }

}
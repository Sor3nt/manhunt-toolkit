import EntityAbstract from "./Entity.js";

export default class Trigger extends EntityAbstract{

    constructor(instEntity){

        let radius = 0.5;

        instEntity.settings.forEach(function (setting) {
            if (setting.hash === 3065307){
                radius = setting.value;
            }
        });

        let object = new THREE.Mesh(
            new THREE.SphereGeometry(radius, 32, 32),
            new THREE.MeshBasicMaterial({
                color: 0xffff00,
                opacity: 0.12,
                transparent: true
            })
        );

        object.name = instEntity.internalName;

        super(instEntity, object, object);
    }

}


export default class EntityAbstract {

    constructor(instEntity, object, model) {
        this.name = "dummy";
        this.settings = instEntity || false;
        this.object = object;
        this.lod = model;
        this.hasAnimation = false;

        if (this.settings !== false) {
            this.name = this.settings.name;

            this.setPosition(this.settings.position);
            this.setRotation(this.settings.rotation);

            this.record = this.settings.glg;
            let animBlock = this.record.getValue("ANIMATION_BLOCK");
            this.hasAnimation = animBlock !== false;
            this.animatioBlock = animBlock;

            this.object.entity = this;

            // if (this.inst.glg.getValue("TRANSPARENT") === true){
            // object.material.forEach(function (mat) {
            //     mat.transparent = true;
            //     mat.opacity = 0.1;
            //     mat.needsUpdate = true;
            // });
            // }

        }
    }

    hasAnimation(){
        return this.hasAnimation;
    }

    getAnimationBlock(){
        return this.animatioBlock;
    }

    getName(){
        if (this.settings !== false)
            return this.settings.name;

        return "NoName";
    }

    getPosition() {
        return new THREE.Vector3(
            this.object.position.x,
            this.object.position.y,
            this.object.position.z
        )
    }

    setPosition(vec3) {
        this.object.position.set(
            vec3.x,
            vec3.y,
            vec3.z
        )
    }

    setRotation(vec4) {
        let quaternion = new THREE.Quaternion(vec4.x, vec4.z, -vec4.y, vec4.w * -1);

        let v = new THREE.Euler();
        v.setFromQuaternion(quaternion);

        this.object.rotation.copy(v);
    }
}
//
// MANHUNT.entity.abstract = function ( instEntity, object, model ) {
//
//     var glgRecord = instEntity.glg;
//
//     var self = {
//         name: instEntity.name,
//         lod: model,
//         record: glgRecord,
//         settings: instEntity,
//         object: object,
//
//
//         getPosition: function(){
//             return new THREE.Vector3(
//                 object.position.x,
//                 object.position.y,
//                 object.position.z
//             )
//         },
//
//         setPosition: function (vec3) {
//             object.position.set(
//                 vec3.x,
//                 vec3.y,
//                 vec3.z
//             )
//         },
//
//         setRotation: function (vec4) {
//
//             // var quaternion = new THREE.Quaternion(vec4.x, vec4.y, vec4.z, vec4.w );
//             var quaternion = new THREE.Quaternion(vec4.x, vec4.z, -vec4.y, vec4.w * -1);
//
//             var v = new THREE.Euler();
//             v.setFromQuaternion(quaternion);
//
//             object.rotation.copy(v );
//
//         }
//
//     };
//
//     object.name = instEntity.name;
//     self.setPosition(instEntity.position);
//     self.setRotation(instEntity.rotation);
//
//     var animBlock = glgRecord.getValue("ANIMATION_BLOCK");
//     self.hasAnimation = animBlock !== false;
//     self.animatioBlock = animBlock;
//
//     object.entity = self;
//
//
//
//     if (glgRecord.getValue("TRANSPARENT") === true){
//         // object.material.forEach(function (mat) {
//         //     mat.transparent = true;
//         //     mat.opacity = 0.1;
//         //     mat.needsUpdate = true;
//         // });
//     }
//
//
//     return self;
//
// };
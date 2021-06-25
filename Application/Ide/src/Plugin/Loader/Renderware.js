import AbstractLoader from "./Abstract.js";
import Renderware from "./Renderware/Renderware.js";

export default class RenderwareLoader extends AbstractLoader{
    static name = "Renderware";

    static canHandle(binary){
        if (binary.length() <= 12) return false;

        let current = binary.current();
        let header = Renderware.parseHeader(binary);
        binary.setCurrent(current);

        switch (header.id) {
            case Renderware.CHUNK_WORLD:
            case Renderware.CHUNK_CLUMP:
                return true;
        }

        return false;
    }

    static list(binary){

        let results = [];

        while(binary.remain() > 0){
            let current = binary.current();
            let header = Renderware.parseHeader(binary);

            switch (header.id) {

                case Renderware.CHUNK_WORLD:
                    (function (offset) {
                        results.push({
                            type: Studio.MAP,
                            name: "scene",
                            offset: offset,
                            data: function(){
                                binary.setCurrent(offset);
                                let tree = Renderware.parse(binary);
                                return (new NormalizeMap(tree)).normalize();

                                //
                                // let mesh = generateMesh(level._storage.tex, normalizedMesh);
                                // mesh.children.forEach(function (subMesh) {
                                //     subMesh.visible = true;
                                // });
                                // return mesh;
                            }
                        });
                    })(current);


                    break;
                case Renderware.CHUNK_CLUMP:
                    binary.setCurrent(current);
                    let list = Renderware.readClumpList(binary);
                    list.forEach(function (info) {
                        results.push({
                            type: Studio.MODEL,
                            name: info.name,
                            offset: info.offset,
                            data: function(){
                                binary.setCurrent(info.offset);
                                let tree = Renderware.parse(binary);
                                return (new NormalizeModel(tree)).normalize();
                                //
                                // let mesh = Renderware.getModel(binary, info.offset);
                                // mesh.name = name;
                                // return mesh;
                            }
                        });
                    });

                    break;

                default:
                    console.error('Unknown Renderware Chunk given');
                    debugger;
                    break;

            }

            binary.setCurrent(current + 12 + header.size);

        }


        return results;
    }

}
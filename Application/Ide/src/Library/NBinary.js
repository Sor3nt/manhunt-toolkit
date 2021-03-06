function NBinary( data, options){

    options = options || { parent: null, parentOffset : 0 };

    var current = 0;


    var self = {

        _init: function(){
            if (self.remain() <= 4) return;

            if (self.consume(4, 'string') === "Z2HM"){
                current = 8;
                var deflated = self.consume(self.remain(), 'arraybuffer');

                var inflated = new Zlib.Inflate( new Uint8Array(deflated) );
                data = inflated.decompress().buffer;
            }

            current = 0;
        },

        getAbsoluteOffset: function(){
            if (options.parentOffset !== null){
                return options.parentOffset + current;
            }

            return current;
        },

        remain: function(){
            return data.byteLength - current;
        },

        length: function(){
            return data.byteLength;
        },

        toString: function(){
            var enc = new TextDecoder();
            return enc.decode(data);
        },

        consumeMulti: function(amount, bytes, type, little){
            let result = [];
            for(let i = 0; i < amount; i++){
                result.push(self.consume(bytes, type, little));
            }
            return result;
        },

        consume: function (bytes, type, little) {
            little = little || true;
            var view = new DataView(data,current);

            current += bytes;


            if (type === 'int16') return view.getInt16(0, little);
            if (type === 'int32') return view.getInt32(0, little);
            if (type === 'uint32') return view.getUint32(0, little);
            if (type === 'float32') return view.getFloat32(0, little);
            if (type === 'uint16') return view.getUint16(0, little);
            if (type === 'int8') return view.getInt8(0, little);
            if (type === 'uint8') return view.getUint8(0, little);
            if (type === 'arraybuffer'){

                var buffer = new ArrayBuffer(bytes);
                var storeView = new DataView(buffer);

                var index = 0;
                while(bytes--){
                    storeView.setUint8(index, view.getUint8(index, true));
                    index++;
                }
                return buffer;
            }
            if (type === 'dataview'){

                var subview = new DataView(data,current - bytes, bytes);

                return subview;
            }
            if (type === 'nbinary'){

                let ssize = bytes;

                var buffer = new ArrayBuffer(bytes);
                var storeView = new DataView(buffer);

                var index = 0;
                while(bytes--){
                    storeView.setUint8(index, view.getUint8(index, true));
                    index++;
                }

                return new NBinary(buffer, { parent: self, parentOffset: current - ssize });
            }
            if (type === 'string'){

                var str = "";
                var index = 0;
                while(bytes--){
                    str += String.fromCharCode(view.getUint8(index, true));
                    index++
                }

                return str;
            }
            console.error(type, "not known, error");

            return view;
        },


        getString: function (delimiter, doPadding) {
            var view = new DataView(data,current);

            var name = '';
            var nameIndex = 0;
            while(self.remain() > 0){
                var val = self.consume(1, 'uint8');
                if (val === delimiter) break;
                name += String.fromCharCode(val);
                nameIndex++;
            }

            if (doPadding === true){
                nameIndex++;

                if (4 - (nameIndex % 4) !== 4){
                    current += 4 - (nameIndex % 4);
                }

            }

            return name;
        },

        readXYZ: function () {
            return {
                x: self.consume(4, 'float32'),
                y: self.consume(4, 'float32'),
                z: self.consume(4, 'float32')
            };
        },

        readVector2: function (byte, type) {
            byte = byte || 4;
            type = type || 'float32';

            return new THREE.Vector2(
                self.consume(byte, type),
                self.consume(byte, type)
            );
        },

        readMatrix4: function(byte, type){
            byte = byte || 4;
            type = type || 'float32';

            return [
                [ self.consume(byte, type), self.consume(byte, type), self.consume(byte, type) ],
                [ self.consume(byte, type), self.consume(byte, type), self.consume(byte, type) ],
                [ self.consume(byte, type), self.consume(byte, type), self.consume(byte, type) ],
                [ self.consume(byte, type), self.consume(byte, type), self.consume(byte, type) ]
            ];

        },

        readVector3: function (byte, type, pad, pByte, pType) {
            byte = byte || 4;
            type = type || 'float32';
            pad = pad || false;

            var vec3 = new THREE.Vector3(
                self.consume(byte, type),
                self.consume(byte, type),
                self.consume(byte, type)
            );

            if (pad === true){
                pByte = pByte || byte;
                pType = pType || type;
                self.consume(pByte, pType);
            }

            return vec3;
        },

        readFace3: function (byte, type) {
            byte = byte || 2;
            type = type || 'int16';

            return new THREE.Face3(
                self.consume(byte, type),
                self.consume(byte, type),
                self.consume(byte, type)
            );
        },

        readFaces3: function (count, materialForFace, byte, type) {
            byte = byte || 2;
            type = type || 'int16';

            var faces = [];
            for (i = 0; i < count; i++) {

                var face3 = self.readFace3(byte, type);
                face3.materialIndex = materialForFace[i];
                faces.push(face3);
            }

            return faces;
        },

        readFloats: function (count) {

            let ret = [];
            while(count--){
                ret.push(self.consume(4, 'float32'));
            }

            return ret;

        },


        readVector4: function (byte, type) {
            byte = byte || 4;
            type = type || 'float32';

            return new THREE.Vector4(
                self.consume(byte, type),
                self.consume(byte, type),
                self.consume(byte, type),
                self.consume(byte, type)
            );
        },


        readXYZW: function () {
            return {
                x: self.consume(4, 'float32'),
                y: self.consume(4, 'float32'),
                z: self.consume(4, 'float32'),
                w: self.consume(4, 'float32')
            };
        },

        readColorRGBA: function (byte, type) {
            byte = byte || 1;
            type = type || 'uint8';

            var rgba = [
                self.consume(byte, type),
                self.consume(byte, type),
                self.consume(byte, type),
                self.consume(byte, type)
            ];

            return new THREE.Color(
                rgba[0], rgba[1], rgba[2]
            );
        },

        readColorRGB: function (byte, type) {
            byte = byte || 1;
            type = type || 'uint8';

            var rgba = [
                self.consume(byte, type),
                self.consume(byte, type),
                self.consume(byte, type)
            ];

            return new THREE.Color(
                rgba[0], rgba[1], rgba[2]
            );
        },

        readColorBGRADiv255: function (byte, type) {
            byte = byte || 1;
            type = type || 'uint8';

            var bgra = [
                self.consume(byte, type) / 255.0,
                self.consume(byte, type) / 255.0,
                self.consume(byte, type) / 255.0,
                self.consume(byte, type)
            ];

            return new THREE.Color(
                bgra[2], bgra[1], bgra[0]
            );
        },

        getCurrent: function(){
            return current;
        },

        seek: function (bytes) {
            current = current + bytes;
        }

    };

    self._init();

    return {
        data: data,
        setCurrent: function(cur){
            current = cur;
        },
        current : function(){
            return current;
        },

        getAbsoluteOffset: self.getAbsoluteOffset,
        toString: self.toString,
        length: self.length,
        readColorRGB: self.readColorRGB,
        readColorRGBA: self.readColorRGBA,
        readColorBGRADiv255: self.readColorBGRADiv255,
        readFace3: self.readFace3,
        readFaces3: self.readFaces3,
        readVector2: self.readVector2,
        readVector3: self.readVector3,
        readVector4: self.readVector4,
        readFloats: self.readFloats,
        remain: self.remain,
        readXYZ: self.readXYZ,
        seek: self.seek,
        readXYZW: self.readXYZW,
        readMatrix4: self.readMatrix4,
        consume: self.consume,
        consumeMulti: self.consumeMulti,
        getString: self.getString
    }
}
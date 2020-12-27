/**
 * Credits: https://github.com/kchapelier/decode-dxt/tree/master/lib
 */
MANHUNT.converter.dxt = (function () {

    var utils = {};

    var lerp = function lerp (v1, v2, r) {
        return v1 * (1 - r) + v2 * r;
    };

    var convert565ByteToRgb = function convert565ByteToRgb (byte) {
        return [
            Math.round(((byte >>> 11) & 31) * (255 / 31)),
            Math.round(((byte >>> 5) & 63) * (255 / 63)),
            Math.round((byte & 31) * (255 / 31))
        ];
    };

    utils.extractBitsFromUin16Array = function extractBitsFromUin16Array (array, shift, length) {
        // sadly while javascript operates with doubles, it does all its binary operations on 32 bytes integers
        // so we have to get a bit dirty to do the bitshifting on the 48 bytes integer for the alpha values of DXT5

        var height = array.length,
            heightm1 = height - 1,
            width = 16,
            rowS = ((shift / width) | 0),
            rowE = (((shift + length - 1) / width) | 0),
            shiftS,
            shiftE,
            result;

        if (rowS === rowE) {
            // all the requested bits are contained in a single uint16
            shiftS = (shift % width);
            result = (array[heightm1 - rowS] >> shiftS) & (Math.pow(2, length) - 1);
        } else {
            // the requested bits are contained in two continuous uint16
            shiftS = (shift % width);
            shiftE = (width - shiftS);
            result = (array[heightm1 - rowS] >> shiftS) & (Math.pow(2, length) - 1);
            result += (array[heightm1 - rowE] & (Math.pow(2, length - shiftE) - 1)) << shiftE;
        }

        return result;
    };

    utils.interpolateColorValues = function interpolateColorValues (firstVal, secondVal, isDxt1) {
        var firstColor = convert565ByteToRgb(firstVal),
            secondColor = convert565ByteToRgb(secondVal),
            colorValues = [].concat(firstColor, 255, secondColor, 255);

        if (isDxt1 && firstVal <= secondVal) {
            colorValues.push(
                Math.round((firstColor[0] + secondColor[0]) / 2),
                Math.round((firstColor[1] + secondColor[1]) / 2),
                Math.round((firstColor[2] + secondColor[2]) / 2),
                255,

                0,
                0,
                0,
                0
            );
        } else {
            colorValues.push(
                Math.round(lerp(firstColor[0], secondColor[0], 1 / 3)),
                Math.round(lerp(firstColor[1], secondColor[1], 1 / 3)),
                Math.round(lerp(firstColor[2], secondColor[2], 1 / 3)),
                255,

                Math.round(lerp(firstColor[0], secondColor[0], 2 / 3)),
                Math.round(lerp(firstColor[1], secondColor[1], 2 / 3)),
                Math.round(lerp(firstColor[2], secondColor[2], 2 / 3)),
                255
            );
        }

        return colorValues;
    };

    utils.interpolateAlphaValues = function interpolateAlphaValues (firstVal, secondVal) {
        var alphaValues = [firstVal, secondVal];

        if (firstVal > secondVal) {
            alphaValues.push(
                Math.floor(lerp(firstVal, secondVal, 1 / 7)),
                Math.floor(lerp(firstVal, secondVal, 2 / 7)),
                Math.floor(lerp(firstVal, secondVal, 3 / 7)),
                Math.floor(lerp(firstVal, secondVal, 4 / 7)),
                Math.floor(lerp(firstVal, secondVal, 5 / 7)),
                Math.floor(lerp(firstVal, secondVal, 6 / 7))
            );
        } else {
            alphaValues.push(
                Math.floor(lerp(firstVal, secondVal, 1 / 5)),
                Math.floor(lerp(firstVal, secondVal, 2 / 5)),
                Math.floor(lerp(firstVal, secondVal, 3 / 5)),
                Math.floor(lerp(firstVal, secondVal, 4 / 5)),
                0,
                255
            );
        }

        return alphaValues;
    };

    utils.multiply = function (component, multiplier) {
        if (!isFinite(multiplier) || multiplier === 0) {
            return 0;
        }

        return Math.round(component * multiplier);
    };

    var getAlphaValueBc2 = function getAlphaValueBc2 (alphaValue, pixelIndex) {
        return utils.extractBitsFromUin16Array(alphaValue, (4 * (15 - pixelIndex)), 4) * 17;
    };

    var getAlphaIndexBc3 = function getAlphaIndexBc3 (alphaIndices, pixelIndex) {
        return utils.extractBitsFromUin16Array(alphaIndices, (3 * (15 - pixelIndex)), 3);
    };

    function decodeBC1 (imageData, width, height, rgb) {
        rgb = rgb || false;
        var rgba = new Uint8Array(width * height * 4),
            height_4 = (height / 4) | 0,
            width_4 = (width / 4) | 0,
            offset = 0,
            colorValues,
            colorIndices,
            colorIndex,
            pixelIndex,
            rgbaIndex,
            h,
            w,
            x,
            y;

        for (h = 0; h < height_4; h++) {
            for (w = 0; w < width_4; w++) {
                colorValues = utils.interpolateColorValues(imageData.getUint16(offset, true), imageData.getUint16(offset + 2, true), true);
                colorIndices = imageData.getUint32(offset + 4, true);

                for (y = 0; y < 4; y++) {
                    for (x = 0; x < 4; x++) {
                        pixelIndex = (3 - x) + (y * 4);
                        rgbaIndex = (h * 4 + 3 - y) * width * 4 + (w * 4 + x) * 4;
                        colorIndex = (colorIndices >> (2 * (15 - pixelIndex))) & 0x03;
                        rgba[rgbaIndex] = colorValues[colorIndex * 4];
                        rgba[rgbaIndex + 1] = colorValues[colorIndex * 4 + 1];
                        rgba[rgbaIndex + 2] = colorValues[colorIndex * 4 + 2];
                        if (rgb === false)
                            rgba[rgbaIndex + 3] = colorValues[colorIndex * 4 + 3];
                    }
                }

                offset += 8;
            }
        }

        return rgba;
    }

    function decodeBC2 (imageData, width, height, premultiplied) {
        var rgba = new Uint8Array(width * height * 4),
            height_4 = (height / 4) | 0,
            width_4 = (width / 4) | 0,
            offset = 0,
            alphaValues,
            alphaValue,
            multiplier,
            colorValues,
            colorIndices,
            colorIndex,
            pixelIndex,
            rgbaIndex,
            h,
            w,
            x,
            y;

        for (h = 0; h < height_4; h++) {
            for (w = 0; w < width_4; w++) {
                alphaValues = [
                    imageData.getUint16(offset + 6, true),
                    imageData.getUint16(offset + 4, true),
                    imageData.getUint16(offset + 2, true),
                    imageData.getUint16(offset, true)
                ]; // reordered as big endian

                colorValues = utils.interpolateColorValues(imageData.getUint16(offset + 8, true), imageData.getUint16(offset + 10, true));
                colorIndices = imageData.getUint32(offset + 12, true);

                for (y = 0; y < 4; y++) {
                    for (x = 0; x < 4; x++) {
                        pixelIndex = (3 - x) + (y * 4);
                        rgbaIndex = (h * 4 + 3 - y) * width * 4 + (w * 4 + x) * 4;
                        colorIndex = (colorIndices >> (2 * (15 - pixelIndex))) & 0x03;
                        alphaValue = getAlphaValueBc2(alphaValues, pixelIndex);

                        multiplier = premultiplied ? 255 / alphaValue : 1;

                        rgba[rgbaIndex] = utils.multiply(colorValues[colorIndex * 4], multiplier);
                        rgba[rgbaIndex + 1] = utils.multiply(colorValues[colorIndex * 4 + 1], multiplier);
                        rgba[rgbaIndex + 2] = utils.multiply(colorValues[colorIndex * 4 + 2], multiplier);
                        rgba[rgbaIndex + 3] = getAlphaValueBc2(alphaValues, pixelIndex);
                    }
                }

                offset += 16;
            }
        }

        return rgba;
    }

    function decodeBC3 (imageData, width, height, premultiplied) {
        var rgba = new Uint8Array(width * height * 4),
            height_4 = (height / 4) | 0,
            width_4 = (width / 4) | 0,
            offset = 0,
            alphaValues,
            alphaIndices,
            alphaIndex,
            alphaValue,
            multiplier,
            colorValues,
            colorIndices,
            colorIndex,
            pixelIndex,
            rgbaIndex,
            h,
            w,
            x,
            y;

        for (h = 0; h < height_4; h++) {
            for (w = 0; w < width_4; w++) {
                alphaValues = utils.interpolateAlphaValues(imageData.getUint8(offset, true), imageData.getUint8(offset + 1, true), false);
                alphaIndices = [
                    imageData.getUint16(offset + 6, true),
                    imageData.getUint16(offset + 4, true),
                    imageData.getUint16(offset + 2, true)
                ]; // reordered as big endian

                colorValues = utils.interpolateColorValues(imageData.getUint16(offset + 8, true), imageData.getUint16(offset + 10, true));
                colorIndices = imageData.getUint32(offset + 12, true);

                for (y = 0; y < 4; y++) {
                    for (x = 0; x < 4; x++) {
                        pixelIndex = (3 - x) + (y * 4);
                        rgbaIndex = (h * 4 + 3 - y) * width * 4 + (w * 4 + x) * 4;
                        colorIndex = (colorIndices >> (2 * (15 - pixelIndex))) & 0x03;
                        alphaIndex = getAlphaIndexBc3(alphaIndices, pixelIndex);
                        alphaValue = alphaValues[alphaIndex];

                        multiplier = premultiplied ? 255 / alphaValue : 1;

                        rgba[rgbaIndex] = utils.multiply(colorValues[colorIndex * 4], multiplier);
                        rgba[rgbaIndex + 1] = utils.multiply(colorValues[colorIndex * 4 + 1], multiplier);
                        rgba[rgbaIndex + 2] = utils.multiply(colorValues[colorIndex * 4 + 2], multiplier);
                        rgba[rgbaIndex + 3] = alphaValue
                    }
                }

                offset += 16;
            }
        }

        return rgba;
    }

    return {
        decodeBC1: decodeBC1,
        decodeBC2: decodeBC2,
        decodeBC3: decodeBC3,
    }
})();

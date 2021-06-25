/**
 * Utility to lookup the Renderware engine structure.
 * By Sor3nt 2021
 */
export default class Guessing{



    static scan(binary){

        binary.setCurrent(0);

        while (binary.remain() > 0){

            Guessing.guessValue(binary);


        }
    }

    static guessValue(binary){
        /**
         *
         * @type {DataView}
         */
        let data = binary.consume(4, 'dataview');



        console.log(data);
        console.log(data.getInt32(0));
        die;
    }

}
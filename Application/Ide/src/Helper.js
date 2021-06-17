export default class Helper{

    static assert(a, b, msg){
        if (a !== b){
            console.error((msg || ('Expect ' + b + ' got ' + a)) );
            debugger;
        }
    }

}
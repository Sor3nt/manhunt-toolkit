import Loader from "../Loader.js";

export default class AbstractLoader{
    static name = "Unnamed Loader";

    static canHandle(binary){
        return false;
    }

    static list(binary, options){
        return [];
    }

}
export default class Loader{

    static plugins = [];

    static registerPlugin( plugin ){
        console.info("Register Loader: ", plugin.name);
        Loader.plugins.push(plugin);
    }

    static parse(binary){

        for (let i in Loader.plugins){
            if (!Loader.plugins.hasOwnProperty(i))
                continue;

            let plugin = Loader.plugins[i];
            if (plugin.canHandle(binary) === false)
                continue;

            console.info("Using Loader: ", plugin.name);

            return plugin.list(binary);
        }

    }

}
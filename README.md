# Manhunt Toolkit (MHT)

MHT allows us to mod Manhunt 1 and 2. Pack, Unpack, Compile. Patch and much more. 

## Getting Started

Every part is developed on PHP 7, you will need to install PHP on your System to use MHT.

### Installing


```
$ git clone https://github.com/Sor3nt/manhunt-toolkit
$ cd manhunt-toolkit
$ composer install
```

Don´t have composer installed ? See here https://getcomposer.org/


## How to use

### Unpack and Pack files

Nothing easier than this. The current version allow us to handle this files:

* *.glg
* *.mls
* *.inst
* *.scc (this is actual an MLS)


You can unpack any file with
```
$ php mht archive:unpack /path/to/my/file
```

And repack again with
```
$ php mht archive:pack /path/to/my/file
```

MHT will autodetect the filetype by reading the header.

> instead of **archive:unpack** you can also write **unpack** / **pack**

> unpack accept also a second parameter "output"

## About the Supported Formats

### GLG (Manhunt 2)
This files are actual INI files, e.g. Setting files. MHT will just unpack them and provide the TXT version of it.

### INST (Manhunt 1 & 2)
Any Object positions is here stored, MHT convert all values into a editable JSON format

```
        "record": "player",
        "internalName": "player(player)",
        "entityClass": "Player_Inst",
        "position": {
            "1": -44.7730712890625,
            "2": -9.298941612243652,
            "3": -0.03999999910593033
        },
        "rotation": {
            "1": 0,
            "2": 0,
            "3": -0,
            "4": 1
        },
        ...
```

### MLS (Manhunt 1 & 2)
The core, the holy grail. Here we have LevelScript code inside. This defines how the game logic is.

MHT unpacker will extract any script block and place it into an folder.

10 sections per script block will be exported

For more details about the MLS Format and where which Data is stored follow the Wiki (todo)


 
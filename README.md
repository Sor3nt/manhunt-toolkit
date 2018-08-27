# Manhunt Toolkit (MHT)

MHT allows us to mod Manhunt 1 and 2. Pack, Unpack, Compile. Patch and much more. 

## Getting Started

Every part is developed on PHP 7, you will need to install PHP on your System to use MHT.

### Installing

**Just load the latest Release and unzip it.**

or grab the night build
```
$ git clone https://github.com/Sor3nt/manhunt-toolkit
$ cd manhunt-toolkit
```


## How to use

### Unpack and Pack files

Nothing easier than this. 

MHT will **autodetect** the given file by reading the header and content.


You can unpack any supported file with
```
$ php mht unpack myManhhunt.file
```

And repack again with
```
$ php mht pack myManhhunt.mod
```

The toolkit provide much more features and parameters, just call **mht** to get the help!
```
php mht
```


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
            "2": 9.298941612243652,
            "3": 0.03999999910593033
        },
        "rotation": {
            "1": 0,
            "2": 0,
            "3": 0,
            "4": 1
        },
        ...
```

### MLS (Manhunt 1 & 2) - Level Script

The MLS file contains the level logic, it define what the player can do and how the level interact with the elements.

After unpacking a MLS File you will receive 2 folders, **Supported** and **Unsupported**.
* Any files inside the **Supported** folder are free editable \*
* The files inside **Unsupported** are not compatible with the compiler.

The compiler is in a **early state** but works already very well.

You can find some documented code examples here: https://github.com/Sor3nt/manhunt-toolkit/tree/master/tests/Resources/Examples

\* free editable: Not all combinations are supported, some action will rais an exception.
 
 **Found a bug ? Need a feature ? Question ?** 
 Feel free to create a Issue on GitHub!

## IFP - Animation Files

This files contains any available animation from the game. At the current state we can export, remove, replace, add and also merge Animations between Manhunt 1 and Manhunt 2\*
 
 \* Cross merging is disabled currently. 
 
#Credits
 
* **Sor3nt** for 4 month sleeples bytecode reverse engineering and compiler building.
* **Ermaccer** for extending the offsets, create awesome helpful ASI, help testing, endure my talks *g* and much more!
* **Allen** for his structure analysis of multiple files.
#HAPPY MODDING!
 
 
 
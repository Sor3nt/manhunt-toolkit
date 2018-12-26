# Manhunt Toolkit

> A free and open source toolkit to quickly modify Rockstar`s game Manhunt.



**Requirements**

You need PHP 7.0 or newer on your System to run MHT

## Installation

**Release**

Load the [latest Release](https://github.com/Sor3nt/manhunt-toolkit/releases) and unzip it.

**Development**

To use the latest features you can clone the current repository.

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
$ php mht unpack allanims_pc.ifp
```

And repack again with
```
$ php mht pack export/allanims_pc_ifp/
```

The toolkit provide much more features and parameters, just call **mht** to get the help!
```
php mht
```

To view available Options for a command call as example

```
php mht mass-extract:dff --help
```

## About the supported formats

Pack/unpack **execution animations** for Manhunt 1 (PC/PS2) and Manhunt 2 (PC/PS2/PSP/WII) (strmanim.bin)
> Contains any execution animation, at the current state you can not copy between Manhunt 1 and Manhunt 2. 

Pack/unpack **animations** for Manhunt 1 (PC/PS2) and Manhunt 2 (PC/PS2/PSP/WII) (allanims.ifp)
> Contains any other animation, at the current state you can not copy between Manhunt 1 and Manhunt 2.

Pack/unpack **models** for Manhunt 1 (PC/PS2) (models.dff)
> Contains the models as DFF file (Can be edit with 3dsmax + Kam´s GTA Script)

Unpack **settings** for Manhunt 2 (PC) (*.glg)
> This files are actual INI files, e.g. Setting files. MHT will just unpack them and provide the TXT version of it.

Pack/unpack **entity positions** for Manhunt 1 (PC/PS2) and Manhunt 2 (PC/PS2/PSP/WII) (*.dff)
>Any Object positions is here stored, MHT convert all values into a editable JSON format
 
Pack/unpack **level script code** for Manhunt 1 (PC/PS2) and Manhunt 2 (PC/PS2/PSP/Wii) (*.mls)

> The MLS file contains the level logic, it define what the player can do and how the level interact with the elements.
>
> After unpacking a MLS File you will receive 2 folders, **Supported** and **Unsupported**.
> * Any files inside the **Supported** folder are free editable
> * The files inside **Unsupported** are not compatible with the compiler.
>
> The compiler is in a **early state** but works already very well.
>
>You can find some documented code examples here: https://github.com/Sor3nt/manhunt-toolkit/tree/master/tests/Resources/Examples


Unpack **textures** for Manhunt 2 (PC) (modelspc.tex)
> Any used texture, export the textures as BMP.


Happy modding!

# Credits
 
* **Sor3nt** who build this Toolkit.
* **Ermaccer** for extending the offsets, create awesome helpful ASI, help testing, endure my talks *g* and much more!
* **Allen** for his structure analysis of multiple files and his helpful hand.
* **Kevin Chapelier** for his awesome DXT implementation.


 
 
 
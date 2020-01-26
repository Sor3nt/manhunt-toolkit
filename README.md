# Manhunt Toolkit

> A free and open source toolkit to quickly modify Rockstar`s game Manhunt.
> Developed for the [Manhunt Modding](https://www.dixmor-hospital.com/) Community Dixmor-Hospital.
## Installation

> Coming soon...


## How to use

### Unpack and Pack files

MHT will **autodetect** the given file by reading the header and content.

> Note for Windows users: You need to type before each command "php "
> as example: "***php*** mht unpack allanims_pc.ifp"

You can unpack any supported file with
```
$ php unpack.php allanims_pc.ifp
```

And repack again with
```
$ php pack.php export/allanims_pc_ifp/
```


## About the supported formats

### Manhunt 1
* Entity and Character animations \[PC, PS2, XBOX\] (allanims.ifp)
* Entity Models \[PC, PS2, XBOX\] (*.dff)
* Entity Positions \[PC, PS2, XBOX\] (*.inst)
* Data container \[PC, PS2, XBOX\] (ManHunt.pak)
* Translations \[PC, PS2, XBOX\] (*.gxt)
* Level script code \[PC\] (*.mls)

### Manhunt 2
* Enitity and Character animations \[PC, PS2, PSP, WII\] (allanims.ifp)
* Execution animations \[PC, PS2, PSP, WII\] (strmanim.bin)
* Settings files \[PC\] (*.glg)
* Entity Positions \[PC, PS2, PSP, WII\] (*.inst)
* Level script code \[PC\] (*.mls)
* Texture extracting \[PC\] (*.tex)
* Translations \[PC, PS2, PSP, WII\] (*.gxt)
* Models \[PC\] (*.mdl, at least A01)



# Credits
 
* **Sor3nt**, **Ermaccer**, **Allen**, **Majest1c_R3**.


 
 
 
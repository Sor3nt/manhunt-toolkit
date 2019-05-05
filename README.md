# Manhunt Toolkit

> A free and open source toolkit to quickly modify Rockstar`s game Manhunt.
> Developed for the [Manhunt Modding](https://www.dixmor-hospital.com/) Community Dixmor-Hospital.
## Installation


**Requirements**

You need PHP 7.2 or newer to run MHT.

**How to install PHP on Windows**

* Download this [PHP Version](https://windows.php.net/downloads/releases/php-7.1.25-Win32-VC14-x64.zip)

* Extract the content of the zip file into C:\PHP7

* Add C:\PHP7 to the Windows 10 system path environment variable.

![no alt](https://github.com/Sor3nt/manhunt-toolkit/blob/master/php7-windows-path.png?raw=true)

**How to install PHP on Mac**

* Open a Terminal (cmd+space terminal)

* Call the magic installer
```
curl -s https://php-osx.liip.ch/install.sh | bash -s 7.2
```

**Install MHT stable release on Windows**

Load the [latest Release](https://github.com/Sor3nt/manhunt-toolkit/releases) and extract the content of the zip file into C:\MHT

Add the C:\MHT path to the system path environment variable. (see above)

**Install MHT stable release on Mac/Linux**

Load the [latest Release](https://github.com/Sor3nt/manhunt-toolkit/releases) and extract the content of the zip file into /usr/local/bin/manhunt-tookit

Add the mht command 

```
echo "alias mht=\"php /usr/local/bin/manhunt-tookit/mht\"" >> ~/.bash_profile
```
> Note: You need to open a new Terminal to use the new configuration


**MHT Development**

To use the latest features you can clone the current repository.

```
$ git clone https://github.com/Sor3nt/manhunt-toolkit
$ cd manhunt-toolkit
```


## How to use

### Unpack and Pack files

MHT will **autodetect** the given file by reading the header and content.

> Note for Windows users: You need to type before each command "php "
> as example: "***php*** mht unpack allanims_pc.ifp"

You can unpack any supported file with
```
$ mht unpack allanims_pc.ifp
```

And repack again with
```
$ mht pack export/allanims_pc_ifp/
```

The toolkit provide much more features and parameters, just call **mht** to get the help!
```
$ mht
```

To view available Options for a command call as example

```
$ mht mass:extraction --help
```

## About the supported formats

### Manhunt 1
* Entity and Character animations \[PC, PS2, XBOX\] (allanims.ifp)
* Entity Models \[PC, PS2, XBOX\] (*.dff)
* Entity Positions \[PC, PS2, XBOX\] (*.inst)
* Data container \[PC, PS2, XBOX\] (ManHunt.pak)
* Translations \[PC, PS2, XBOX\] (*.gxt)
* Level script code \[PC\] (*.mls) (Born Again only at this point)

### Manhunt 2
* Enitity and Character animations \[PC, PS2, PSP, WII\] (allanims.ifp)
* Execution animations \[PC, PS2, PSP, WII\] (strmanim.bin)
* Settings files \[PC\] (*.glg)
* Entity Positions \[PC, PS2, PSP, WII\] (*.inst)
* Level script code \[PC\] (*.mls) (Escape Asylum only at this point)
* Texture extracting \[PC\] (*.tex)
* Translations \[PC, PS2, PSP, WII\] (*.gxt)
* Models \[PC\] (*.mdl, at least A01)

 
 
> The MLS file contains the level logic, it define what the player can do and how the level interact with the elements.
>
> The compiler is in a **early state** but works already very well.
>
> ***Please note that the current compiler only work with the first level from Manhunt 1 and 2!***
>
>You can find some documented code examples here: https://github.com/Sor3nt/manhunt-toolkit/tree/master/tests/Resources/Examples



#Issues


**PREG_JIT_STACKLIMIT_ERROR**

Please open your php.ini search and set this value
````
pcre.jit=0
````



# Credits
 
* **Sor3nt** who build this Toolkit.
* **Ermaccer** for extending the offsets, create awesome helpful ASI, help testing, endure my talks *g* and much more!
* **Allen** for his structure analysis of multiple files and his helpful hand.
* **Kevin Chapelier** for his awesome DXT implementation.


 
 
 
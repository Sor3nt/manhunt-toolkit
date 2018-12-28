# Manhunt Toolkit

> A free and open source toolkit to quickly modify Rockstar`s game Manhunt.

## Installation


**Requirements**

You need PHP 7.0 or newer to run MHT.

**How to install PHP on Windows**

* Download this [PHP Version](https://windows.php.net/downloads/releases/php-7.1.25-Win32-VC14-x64.zip)

* Extract the content of the zip file into C:\PHP7

* Add C:\PHP7 to the Windows 10 system path environment variable.

![no alt](https://github.com/Sor3nt/manhunt-toolkit/blob/master/php7-windows-path.png?raw=true)

**How to install PHP on Mac**

* Open a Terminal (cmd+space terminal)

* Call the magic installer
```
curl -s https://php-osx.liip.ch/install.sh | bash -s 7.1
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
$ mht mass-extract:dff --help
```

## About the supported formats

Pack/unpack **execution animations** for Manhunt 2 (PC/PS2/PSP/WII) (strmanim.bin)
> Contains any execution animation, at the current state you can not copy between Manhunt 1 and Manhunt 2. 

Pack/unpack **animations** for Manhunt 1 (PC/PS2/XBOX) and Manhunt 2 (PC/PS2/PSP/WII) (allanims.ifp)
> Contains any other animation, at the current state you can not copy between Manhunt 1 and Manhunt 2.

Pack/unpack **models** for Manhunt 1 (PC/PS2/XBOX) (*.dff)
> Contains the models as DFF file (Can be edit with 3dsmax + Kam´s GTA Script)

Unpack **settings** for Manhunt 2 (PC) (*.glg)
> This files are actual INI files, e.g. Setting files. MHT will just unpack them and provide the TXT version of it.

Pack/unpack **entity positions** for Manhunt 1 (PC/PS2/XBOX) and Manhunt 2 (PC/PS2/PSP/WII) (*.dff)
>Any Object positions is here stored, MHT convert all values into a editable JSON format
 
Pack/unpack **level script code** for Manhunt 2 (PC/PS2/PSP/Wii) (*.mls)

> The MLS file contains the level logic, it define what the player can do and how the level interact with the elements.
>
> After unpacking a MLS File you will receive 2 folders, **Supported** and **Unsupported**.
> * Any files inside the **Supported** folder are free editable
> * The files inside **Unsupported** are not compatible with the compiler.
>
> The compiler is in a **early state** but works already very well.
>
> ***Please note that the current compiler only works with Manhunt 2!***
>
>You can find some documented code examples here: https://github.com/Sor3nt/manhunt-toolkit/tree/master/tests/Resources/Examples


Unpack **textures** for Manhunt 2 (PC) (modelspc.tex)
> Any used texture, export the textures as BMP.

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


 
 
 
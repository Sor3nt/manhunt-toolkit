#Changelog
**2024-04-05**: **v0.9.9** Release:
* Add new param „-ignore-order“
* Add new param „-memdump“ (only for TXD)
* Add VAS Audio support
* Add PSP MLS build support
* Add PSP/PS2 TXT build support
* Add AFS audio names
* Add MH1 PS2 TXD support
* Add GENH Support
* Some AFS Audio fixes
* Fixed rebuild FSB3 issues
* Fixed AIX for PS2 a02
* Fixed WII GXT issues
* Fixed some alpha channel issues(PC, WII)
* Fixed small ps2 textures issues
* Fixed a lot of GRF issues
* Fixed Windows user related issues

**2020-04-17**: **v0.9.5** Release:
* Add Audio Support (fsb, afs) for PC, WII and PS2
* Add FSB3 Audioname resolver
* Add FSB4 Folder structure resolver
* Add ADPCM to PCM Converter
* Add generic TAG and DIR support
* Add INST Parameter names @Thanks MAJEST1C_R3!
* Add GRF Support
* Add TOC Autoupdater
* Change INST XYZ Handling
* Small fixes for IFP, GLG and MLS Handler


**2020-01-26**: **v0.9.0** Release:
* Remove Symfony as Codebase
* New MLS Compiler, support now near any level (MH1 & MH2)
* Add Wii Texture Support
* Rotation fix for Animation reader (BIN)

**2019-10-01**: **v0.8.0** Release:
* Fix Wii and Ps2 Animation reader (IFP)
* Extend INST parameter mapping
* MLS fixes for MH1 and MH2 + MH1 Der2 support.
* Translation fixes for psp and ps2 (GXT)
* Texture support added for PC, PSP and PS2 (Unpack only)
* New command to generate code highlighter (phpstorm) (mht generate:syntax)
* Add feature to allow porting MH1 animation to MH2 (experimental)
* Collision handler fixes
* Add command to generate global files (mht generate:global)
* Add command to find unused Models (mht find:unused)
* New GLG/INI Handler

**2019-05-01**: **v0.7.0** Release:
* Extend MLS Compiler Supports now MH1 Born Again
* Autoset memory_limit to -1
* Add MDL Alpha Support. (MH2 PC)
* Add Command for mass extraction of MH files.
* Add Command to analyse animation data (Generate a comparison csv)
* Add Command to generate a animation file based on the previous table
* Fix some animation file packing errors.
* Fix game/platform detection for animation files
* Remove some old commands
* Change INST Output to single files
* Add new GLG handler (alpha, only unpack)

**2019-01-27**: **v0.6.0** Release:
* Fix MLS Compiler (MH2 A01 100% Support)
* Add GXT Support. (MH1 PC/PS2 and MH2 PC/PS2/PSP/WII)
* Replace "mass-extract:*" command with "mass:extraction" for all supported files.
* Add Manhunt.pak support.
* Some fixes.

**2019-01-01**: **v0.5.0** Release:
* Add execution animation (strmanim) Support. (MH2 PC/PS2/PSP/Wii)
* Add DFF Support. (MH1 PC/PS2)
* Add TEX Support. (MH2 PC) (Export only!)
* Add command "mass-extract:tex".
* Add command "mass-extract:dff".
* Add command "generate:events".
* Fix INST Support. (Supports now MH1/MH2 PC/PS2/PSP/Wii)
* Extend/Recode MLS handler, more scripts (SRCE) supported.
* Extend Unit testing.
* Reimplement byte reader, huge performance boost.
* Many recodes, cleanup and small fixes.

**2018-08-26**: **v0.1.0** Release:
* Add MLS Support.
* Add IFP Support.
* Add Manhunt Script Examples.
* Fix INST Position format. (mh2) (thx ermaccer for the ASI position viewer )
* Add vendor (for easy installation...).
* And much much more.

**2018-06-10**: Bugfix **v0.0.3** Release:
* Fix Manhunt 1 Inst Packer / Unpacker.
* Add Changelog.

**2018-06-10**: Bugfix **v0.0.2** Release:

* Fix Manhunt 1 MLS Packer / Unpacker.

**2018-06-10**: Initial **v0.0.1** Release:

* Add GLG Packer / Unpacker.
* Add Inst Packer / Unpacker.
* Add MLS Packer / Unpacker.
* Basic code.


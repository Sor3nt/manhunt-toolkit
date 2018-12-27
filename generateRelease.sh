#!/bin/bash

mkdir release/
mkdir release/git
git clone https://github.com/Sor3nt/manhunt-toolkit.git release/git/

mkdir release/build/
mkdir release/build/var
mkdir release/build/var/cache
mkdir release/build/var/log


cp -R release/git/bin release/build/
cp -R release/git/config release/build/
cp -R release/git/src release/build/
cp -R release/git/vendor release/build/
cp -R release/git/mht release/build/
cp -R release/git/README.md release/build/
cp -R release/git/php7-windows-path.png release/build/
cp -R release/git/CHANGELOG.md release/build/

zip -r release/mht-v$1-no-unittest.zip release/build/

cp -R release/git/tests release/build/

zip -r release/mht-v$1-with-unittest.zip release/build/

rm -r release/git/
rm -r release/build/
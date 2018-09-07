#!/data/data/com.termux/files/usr/bin/bash
echo "This is intended to be run on Termux"
pkg install php git || echo "Error at trying to install php git, perhaps you should run \"apt-get update\" first, but is up to you"
pkg install termux-api || echo "Error at trying to install required api, perhaps you should run \"apt-get update\" first, but is up to you"
#git clone https://github.com/pulketo/termux-cute-call || ( echo "git error" ; exit 1)
#cd termux-cute-call || ( echo "cd error" ; exit 1)
chmod +x cc.phar || ( echo "chmod error" ; exit 1)
ln -s $(pwd)/cc.phar ~/../usr/bin/c && echo "run with: cc john doe -f" || echo "someshit happened"

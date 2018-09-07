#!/data/data/com.termux/files/usr/bin/bash
echo "This is intended to be run on Termux"
pkg install php git termux-api
chmod +x cc.phar 
ln -s $(pwd)/cc.phar ~/../usr/bin/c && echo "run with: c john doe -f" || echo "someshit happened"

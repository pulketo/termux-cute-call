WHAT IS
			Script to call a person on your contact list by his/her name on Termux.			
	
			This script is intended to be run on termux emulator (which is a Terminal emulator and Linux environment for Android)

			Requires (Android side):
				Termux (play store)
				Termux:API (play store)
			Requires (Termux side)
				PHP (install with: pkg install php)

NAME
			pcall -  command to call a contact searching by name

SYNOPSIS
			pcall -f -s -p -v [searchterm1] [searchterm2] ...

DESCRIPTION
			pcall is a php script to make easier to call a contact within the termux command line, 
			just run the script with some search terms, and if there is only one result, will call your contact
			 
			all human output goes to stderr and json output goes to stdout useful if you want to pipe 
			it to jq to prettyfy or extra processing.
			It's straighforward to use, if you have a suggestion, please send it to: pulketo at G.mail

EXAMPLES
			pcall john doe -f
				will call the (-f)irst john doe on your list
			pcall john doe 551
				will show john doe with 551 on the name/number, execute termux-call if there is just one match, or show a list if there is more than one.
			pcall john doe -f -p +521
				will call John Doe but dial a prefix before the number.

OPTIONS
      Search terms must not have a dash before the word, it's a bug/feature on the library nategood/commando

   General options
			-f --callthefirst
					No matter if there is more than one, call the first on the list.
			-s	--simcall
					Just show the command to make the call, no call will take place.
			-p	--prefix
					A dial prefix, some countries when you are out of your city, you should dial some numbers at the beginning.
			-v 	--version
					Show version
			-c --call (deprecated, calling if there is one match is the default now)
					If there is one and only one match, call him/her.

EXIT STATUS
       0      Successful program execution.

       1      Too many matches, but -f not specified.

       2      No matches.

       3      Something weird happened

HISTORY
			2018 - Pulketo ;)

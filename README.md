langbuilder
===========

Language Builder Script for the SilverStripe3.
kudos to the original Author:  Roman Schmid, aka Banal
modified to work with silverstripe3 by: Francisco Arenas, aka dospuntocero

This script searches all .php and .ss files in a given directory for calls to the translate function _t.
All found instances will be saved as a yml language file

Usage:  LangBuilder.php <dir>
dir     The directory to search for files. Usually this
        should be your module directory.
        
Examples:
LangBuilder.php mymodule
Will search the "mymodule" folder that\'s in the same 
directory as the LangBuilder.php file. Will extract
all translatable entities and store them in 
mymodule/lang/en.yml

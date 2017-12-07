# Loft Php Library

A collection of Php Classes by In the Loft Studios.

To ensure all necessary dependencies are loaded you should add this to your _composer.json_ file.

    {
        "autoload": {
            "files": ["lib/loft_php_lib/dist/vendor/autoload.php"],
        }
    }



Include this in your module like this:


## Sample `.gitignore` to include everything but the encryption

By default you should add this to your project using Loft Php Lib
    
    lib/loft_php_lib/*
    !lib/loft_php_lib/dist
    lib/loft_php_lib/dist/vendor
    lib/loft_php_lib/dist/src/AKlump/LoftLib/*
    !lib/loft_php_lib/dist/src/AKlump/LoftLib/Code
    lib/loft_php_lib/dist/src/AKlump/LoftLib/Code/*
    !lib/loft_php_lib/dist/src/AKlump/LoftLib/Code/Strings*
    !lib/loft_php_lib/dist/src/AKlump/LoftLib/Code/Encryption*


## Sample `.gitignore` to just use the Xml library

    lib/loft_php_lib/*
    !lib/loft_php_lib/dist
    # Excludes all src files
    lib/loft_php_lib/dist/src/AKlump/LoftLib/*
    
    # Except these...
    !lib/loft_php_lib/dist/src/AKlump/LoftLib/Code
    !lib/loft_php_lib/dist/src/AKlump/LoftLib/Xml
    
    lib/loft_php_lib/dist/src/AKlump/LoftLib/Code/*
    !lib/loft_php_lib/dist/src/AKlump/LoftLib/Code/String.php
    !lib/loft_php_lib/dist/src/AKlump/LoftLib/Code/Grammar.php
    
    lib/loft_php_lib/dist/src/AKlump/LoftLib/Xml/*
    !lib/loft_php_lib/dist/src/AKlump/LoftLib/Xml/LoftXmlElement.php

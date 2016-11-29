## Windows
- install php7 to C:\php7
- add C:\php7 to PATH
- download PEAR from https://pear.php.net/manual/en/installation.getting.php and save go-pear.phar to C:\php7
- Start an elevated cmd in C:\php7 and run `php go-pear.phar`
- Import the generated .reg file into your windows registry
- run the following from within the elevated cmd promp: ```pear install PHP_CodeSniffer```
- open C:\php7\phpcs in Sublime. Replace all instances of "/../CodeSniffer" with "/Pear/PHP/CodeSniffer"
- Checkout git@gitlab:misc/phpcs.git to  C:\php7\pear\PHP\CodeSniffer\Standards\TFD using Sourcetree

## OSX
- Install PHP

- Install PHP_CodeSniffer
```bash
# make a bin directory in your home folder
mkdir ~/.bin
cd ~/.bin
# clone PHP_CodeSniffer from GitHub
git clone https://github.com/squizlabs/PHP_CodeSniffer.git phpcs
# add phpcs to your path
sudo ln -s ~/.bin/phpcs/scripts/phpcs /usr/local/bin/phpcs
# check if phpcs bin is available
phpcs -i
# clone TFD coding standard into correct location
cd ~/.bin/phpcs/CodeSniffer/Standards
git clone git@gitlab:misc/phpcs.git TFD
```

## Install Sublime packages

- SublimeLinter
- SublimeLinter-php
- SublimeLinter-phpcs

Restart SublimeText.

## Sublime config

### User
````
{
    "trim_automatic_white_space": true,
    "trim_trailing_white_space_on_save": true,
    "tab_size": 4,
    "translate_tabs_to_spaces": true
}
````

### Package - SublimeLinter - User
````
{
    "linters": {
        "php": {
            "@disable": false,
            "args": [],
            "excludes": []
        },
        "phpcs": {
            "@disable": false,
            "args": [],
            "excludes": [],
            "standard": "TFD"
        }
    }
}
````
## Windows
- Install PHP7 to C:\php
- Add C:\php to PATH
- Download PEAR from https://pear.php.net/manual/en/installation.getting.php and save go-pear.phar to C:\php
- Start an elevated cmd in C:\php and run `php go-pear.phar`
- Import the generated .reg file into your windows registry
- Run the following from within the elevated cmd promp: ```pear install PHP_CodeSniffer```
- Open C:\php\phpcs in Sublime. Replace all instances of "/../CodeSniffer" with "/Pear/PHP/CodeSniffer"
- Checkout git@github.com:theFrontDoor/CodeSniffer.git to C:\php\pear\PHP\CodeSniffer\Standards\TFD using Sourcetree

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
git clone https://github.com/theFrontDoor/CodeSniffer.git TFD
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
    "trim_trailing_white_space_on_save": true,
    "ensure_newline_at_eof_on_save": true,
    "trim_automatic_white_space": true,
    "translate_tabs_to_spaces": true,
    "default_line_ending": "unix",
    "tab_size": 4
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

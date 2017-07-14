## Windows
- Download PHP7 and extract it to C:\PHP
- Add C:\PHP to PATH
- Download PEAR from https://pear.php.net/manual/en/installation.getting.php and save go-pear.phar to C:\php
- Start an elevated cmd in C:\php and run `php go-pear.phar`
- Import the generated .reg file into your windows registry
- Run the following from within the elevated cmd promp: ```pear install PHP_CodeSniffer```
- Open C:\php\phpcs in Sublime. Replace all instances of "/../CodeSniffer" with "/Pear/PHP/CodeSniffer"
- Checkout https://github.com/theFrontDoor/CodeSniffer.git to C:\php\pear\PHP\CodeSniffer\Standards\TFD

## OSX
- Install PHP 7 with pear
```bash
brew install homebrew/php/php71 --with-pear
```
- Install PHP_CodeSniffer
```bash
sudo pear install PHP_CodeSniffer
```

- Add the TFD standard
```bash

# Link PHPCS and PHPCBF to the bin folder
ln -s $(brew --prefix php71)/bin/phpcs /usr/local/bin/phpcs
ln -s $(brew --prefix php71)/bin/phpcbf /usr/local/bin/phpcbf

# Clone the TFD coding standard into correct location
sudo git clone https://github.com/theFrontDoor/CodeSniffer.git $(brew --prefix php71)/lib/php/PHP/CodeSniffer/Standards/TFD

# Set the default standard
sudo phpcs --config-set default_standard TFD
```

## Install Sublime packages

- EditorConfig
- SublimeLinter
- SublimeLinter-php
- SublimeLinter-phpcs

Restart SublimeText.

### Sublime config

#### User
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

#### Package - SublimeLinter - User
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

Note: The first time you save the "SublimeLinter - User" preferences and restart sublime it will reset the config file

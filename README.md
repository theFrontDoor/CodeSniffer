## Windows
- Download PHP7 and extract it to C:\PHP
- Add C:\PHP to PATH
- Download PEAR from https://pear.php.net/manual/en/installation.getting.php and save go-pear.phar to C:\PHP
- Start an elevated cmd in C:\php and run `php go-pear.phar`
- Import the generated .reg file into your windows registry (Should be in  C:\PHP)
- Run the following from within the elevated cmd promp: ```pear install PHP_CodeSniffer```
- Checkout https://github.com/theFrontDoor/CodeSniffer.git to C:\CodeSniffer_TFD
- Run ```phpcs --config-set installed_paths C:\CodeSniffer_TFD```
- Run ```phpcs --config-set default_standard TFD```

## OSX

```bash
# Ensure xcode is installed with xcode command line tools, note: this might spawn a prompt
xcode-select --install

# Install PHP 7.2 TS with pear, note that thread safety is optional but recommended
brew install php@7.2 --with-pear --with-thread-safety

# Add pear to the include path
echo 'include_path = ".:'$(pear config-get php_dir)'"' | sudo tee -a $(php -r 'echo php_ini_loaded_file();')

# Install PHP_CodeSniffer
sudo pear install PHP_CodeSniffer

# Unlink old PHP versions and re-link this version
brew unlink php && brew link php@7.2

# Delete the existing dir if exists
sudo rm -rf ~/CodeSniffer_TFD/ 2> /dev/null

# Clone the TFD coding standard into correct location
git clone https://github.com/theFrontDoor/CodeSniffer.git ~/CodeSniffer_TFD/

# Add the standard to the sniffers path and set it as the default standard
sudo phpcs --config-set installed_paths ~/CodeSniffer_TFD/TFD
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

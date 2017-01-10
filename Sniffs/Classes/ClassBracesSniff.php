<?php

class TFD_Sniffs_Classes_ClassBracesSniff implements PHP_CodeSniffer_Sniff {

    public function register() {
        return [T_CLASS];
    }

    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {

        $tokens = $phpcsFile->getTokens();

        if (isset($tokens[$stackPtr]['scope_opener']) === TRUE) {

            $openBrace = $tokens[$stackPtr]['scope_opener'];

            $spaceBeforeOpen = 0;
            if ($tokens[($openBrace - 1)]['code'] === T_WHITESPACE) {
                $spaceBeforeOpen = strlen($tokens[($openBrace - 1)]['content']);
            }

            if ($tokens[$stackPtr]['line'] !== $tokens[($openBrace + 1)]['line']) {
                $phpcsFile->addFixableError('Expected opening bracket on the same line as the class definition!', $openBrace, 'SpacingBeforeOpenBrace');
            } elseif ($spaceBeforeOpen !== 1) {

                $error = 'Expected %s spaces after opening bracket; %s found';
                $data  = array(1, $spaceBeforeOpen);

                $phpcsFile->addFixableError($error, $openBrace, 'SpacingBeforeOpenBrace', $data);

            }

            file_put_contents('/tmp/snifftest', print_r($tokens[$stackPtr], TRUE));
            file_put_contents('/tmp/snifftest', print_r($tokens[($openBrace - 1)], TRUE), FILE_APPEND);

        }

    }

}

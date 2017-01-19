<?php
/**
 * TFD_Sniffs_WhiteSpace_ControlStructureSpacingSniff.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * TFD_Sniffs_WhiteSpace_ControlStructureSpacingSniff.
 *
 * Checks that control structures have the correct spacing around brackets.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   Release: 1.5.2
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class TFD_Sniffs_ControlStructures_ControlStructureSpacingSniff implements PHP_CodeSniffer_Sniff {


    /**
     * How many spaces should be before the opening bracket.
     *
     * @var int
     */
    public $requiredSpacesBeforeOpen = 1;


    /**
     * How many spaces should follow the opening bracket.
     *
     * @var int
     */
    public $requiredSpacesAfterOpen = 0;

    /**
     * How many spaces should precede the closing bracket.
     *
     * @var int
     */
    public $requiredSpacesBeforeClose = 0;

    /**
     * How many spaces should follow the closing bracket.
     *
     * @var int
     */
    public $requiredSpacesAfterClose = 1;


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register() {
        return array(
                T_IF,
                T_WHILE,
                T_FOREACH,
                T_FOR,
                T_SWITCH,
                T_DO,
                T_ELSE,
                T_ELSEIF,
               );

    }//end register()


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {

        $this->requiredSpacesBeforeOpen   = (int) $this->requiredSpacesBeforeOpen;
        $this->requiredSpacesAfterOpen   = (int) $this->requiredSpacesAfterOpen;
        $this->requiredSpacesBeforeClose = (int) $this->requiredSpacesBeforeClose;
        $this->requiredSpacesAfterClose = (int) $this->requiredSpacesAfterClose;

        $tokens = $phpcsFile->getTokens();

        if (isset($tokens[$stackPtr]['parenthesis_opener']) === TRUE) {

            $parenOpener    = $tokens[$stackPtr]['parenthesis_opener'];
            $parenCloser    = $tokens[$stackPtr]['parenthesis_closer'];

            $spaceBeforeOpen = 0;
            if ($tokens[($parenOpener - 1)]['code'] === T_WHITESPACE) {
                $spaceBeforeOpen = strlen($tokens[($parenOpener - 1)]['content']);
            }

            if ($spaceBeforeOpen !== $this->requiredSpacesBeforeOpen) {
                $error = 'Expected %s spaces after opening bracket; %s found';
                $data  = array($this->requiredSpacesBeforeOpen, $spaceBeforeOpen);

                $phpcsFile->addError($error, ($parenOpener - 1), 'SpacingBeforeOpenBrace', $data);
            }

            // $phpcsFile->addWarning('$spaceBeforeOpen: ' . $spaceBeforeOpen, ($parenOpener + 1), 'SpacingAfterOasdpenBrace');

            $spaceAfterOpen = 0;
            if ($tokens[($parenOpener + 1)]['code'] === T_WHITESPACE) {
                $spaceAfterOpen = strlen($tokens[($parenOpener + 1)]['content']);
            }

            if ($spaceAfterOpen !== $this->requiredSpacesAfterOpen) {
                $error = 'Expected %s spaces after opening bracket; %s found';
                $data  = array($this->requiredSpacesAfterOpen, $spaceAfterOpen);

                $phpcsFile->addError($error, ($parenOpener + 1), 'SpacingAfterOpenBrace', $data);
            }

            if ($tokens[$parenOpener]['line'] === $tokens[$parenCloser]['line']) {

                $spaceBeforeClose = 0;
                if ($tokens[($parenCloser - 1)]['code'] === T_WHITESPACE) {
                    $spaceBeforeClose = strlen($tokens[($parenCloser - 1)]['content']);
                }

                if ($spaceBeforeClose !== $this->requiredSpacesBeforeClose) {
                    $error = 'Expected %s spaces before closing bracket; %s found';
                    $data  = array($this->requiredSpacesBeforeClose, $spaceBeforeClose);

                    $phpcsFile->addError($error, ($parenCloser - 1), 'SpaceBeforeCloseBrace', $data);
                }

                $spaceAfterClose = 0;
                if ($tokens[($parenCloser + 1)]['code'] === T_WHITESPACE) {
                    $spaceAfterClose = strlen($tokens[($parenCloser + 1)]['content']);
                }

                if ($spaceAfterClose !== $this->requiredSpacesAfterClose && $tokens[($parenCloser + 1)]['code'] !== PHPCS_T_SEMICOLON) {
                    $error = 'Expected %s spaces after closing bracket; %s found';
                    $data  = array($this->requiredSpacesAfterClose, $spaceAfterClose);

                    $phpcsFile->addError($error, ($parenCloser + 1), 'SpaceAfterCloseBrace', $data);
                }

            }

        }//end if

    }//end process()


}//end class

<?php

namespace TFD\Sniffs\ControlStructures;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

class ControlStructureSpacingSniff implements Sniff {

    /**
     * How many spaces should precede the closing bracket.
     *
     * @var int
     */
    public $requiredSpacesBeforeClose = 0;

    /**
     * How many spaces should precede the opening bracket.
     *
     * @var int
     */
    public $requiredSpacesAfterClose = 1;

    /**
     * How many spaces should follow the opening bracket.
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
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register() {

        return [
            T_IF,
            T_WHILE,
            T_FOREACH,
            T_FOR,
            T_SWITCH,
            T_DO,
            T_ELSE,
            T_ELSEIF,
            T_TRY,
            T_CATCH,
        ];

    }


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token
     *                                               in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr) {

        $this->requiredSpacesBeforeClose = (int) $this->requiredSpacesBeforeClose;
        $this->requiredSpacesAfterClose   = (int) $this->requiredSpacesAfterClose;
        $this->requiredSpacesAfterOpen   = (int) $this->requiredSpacesAfterOpen;
        $this->requiredSpacesBeforeOpen = (int) $this->requiredSpacesBeforeOpen;

        $tokens = $phpcsFile->getTokens();

        if (!isset($tokens[$stackPtr]['parenthesis_opener']) || !isset($tokens[$stackPtr]['parenthesis_closer'])) {
            return;
        }

        $parenOpener = $tokens[$stackPtr]['parenthesis_opener'];
        $parenCloser = $tokens[$stackPtr]['parenthesis_closer'];

        $spaceBeforeOpen = 0;
        if ($tokens[($parenOpener - 1)]['code'] === T_WHITESPACE) {
            $spaceBeforeOpen = strlen($tokens[($parenOpener - 1)]['content']);
        }

        if ($spaceBeforeOpen !== $this->requiredSpacesBeforeOpen) {
            $error = 'Expected %s spaces before opening bracket; %s found';
            $data  = [
                $this->requiredSpacesBeforeOpen,
                $spaceBeforeOpen,
            ];

            $fix = $phpcsFile->addFixableError($error, ($parenOpener - 1), 'SpacingBeforeOpenBrace', $data);
            if ($fix) {
                $padding = str_repeat(' ', $this->requiredSpacesBeforeOpen);
                if ($spaceBeforeOpen === 0) {
                    $phpcsFile->fixer->addContentBefore($parenOpener, $padding);
                } else {
                    $phpcsFile->fixer->replaceToken(($parenOpener - 1), $padding);
                }
            }
        }

        $spaceAfterOpen = 0;
        if ($tokens[($parenOpener + 1)]['code'] === T_WHITESPACE) {
            if (strpos($tokens[($parenOpener + 1)]['content'], $phpcsFile->eolChar) !== FALSE) {
                $spaceAfterOpen = 'newline';
            } else {
                $spaceAfterOpen = strlen($tokens[($parenOpener + 1)]['content']);
            }
        }

        $phpcsFile->recordMetric($stackPtr, 'Spaces after control structure open parenthesis', $spaceAfterOpen);
        if ($spaceAfterOpen !== $this->requiredSpacesAfterOpen) {

            $error = 'Expected %s spaces after opening bracket; %s found';
            $data  = [
                $this->requiredSpacesAfterOpen,
                $spaceAfterOpen,
            ];

            $fix = $phpcsFile->addFixableError($error, ($parenOpener + 1), 'SpacingAfterOpenBrace', $data);
            if ($fix) {

                $padding = str_repeat(' ', $this->requiredSpacesAfterOpen);
                if ($spaceAfterOpen === 0) {
                    $phpcsFile->fixer->addContent($parenOpener, $padding);
                } else if ($spaceAfterOpen === 'newline') {
                    $phpcsFile->fixer->replaceToken(($parenOpener + 1), '');
                } else {
                    $phpcsFile->fixer->replaceToken(($parenOpener + 1), $padding);
                }

            }

        }

        if ($tokens[$parenOpener]['line'] === $tokens[$parenCloser]['line']) {

            $spaceBeforeClose = 0;
            if ($tokens[($parenCloser - 1)]['code'] === T_WHITESPACE) {
                $spaceBeforeClose = strlen(ltrim($tokens[($parenCloser - 1)]['content'], $phpcsFile->eolChar));
            }

            $phpcsFile->recordMetric($stackPtr, 'Spaces before control structure close parenthesis', $spaceBeforeClose);

            if ($spaceBeforeClose !== $this->requiredSpacesBeforeClose) {
                $error = 'Expected %s spaces before closing bracket; %s found';
                $data  = [
                    $this->requiredSpacesBeforeClose,
                    $spaceBeforeClose,
                ];

                $fix = $phpcsFile->addFixableError($error, ($parenCloser - 1), 'SpaceBeforeCloseBrace', $data);
                if ($fix) {

                    $padding = str_repeat(' ', $this->requiredSpacesBeforeClose);
                    if ($spaceBeforeClose === 0) {
                        $phpcsFile->fixer->addContentBefore($parenCloser, $padding);
                    } else {
                        $phpcsFile->fixer->replaceToken(($parenCloser - 1), $padding);
                    }

                }

            }

            $spaceAfterClose = 0;
            if ($tokens[($parenCloser + 1)]['code'] === T_WHITESPACE) {
                $spaceAfterClose = strlen($tokens[($parenCloser + 1)]['content']);
            }

            if ($spaceAfterClose !== $this->requiredSpacesAfterClose && $tokens[($parenCloser + 1)]['code'] !== T_SEMICOLON) {

                $error = 'Expected %s spaces after closing bracket; %s found';
                $data  = [
                    $this->requiredSpacesAfterClose,
                    $spaceAfterClose,
                ];

                $fix = $phpcsFile->addFixableError($error, ($parenCloser + 1), 'SpaceAfterCloseBrace', $data);
                if ($fix) {

                    $padding = str_repeat(' ', $this->requiredSpacesAfterClose);
                    if ($spaceAfterClose === 0) {
                        $phpcsFile->fixer->addContent($parenCloser, $padding);
                    } else {
                        $phpcsFile->fixer->replaceToken(($parenCloser + 1), $padding);
                    }

                }

            }

        }

    }

}

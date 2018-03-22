<?php
/**
 * Created by PhpStorm.
 * User: kevinmeijer
 * Date: 11/09/2017
 * Time: 12:47
 */

namespace TFD\Sniffs\Files;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

class OneClassPerFileSniff implements Sniff {

    /**
     * The maximum amount of lines a class may be before it is considered multiple per file
     *
     * @var int
     */
    public $maxLineCost = 20;

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register() {
        return [T_CLASS];
    }

    /**
     * Processes this sniff, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token in
     *                                               the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr) {

        $tokens = $phpcsFile->getTokens();
        $nextClass = $stackPtr;

        $errorClasses = [];

        while ($nextClass = $phpcsFile->findNext($this->register(), ($nextClass + 1))) {

            $classToken = $tokens[$nextClass];

            $startLine = $classToken['line'];
            $stopLine = $tokens[$classToken['scope_closer']]['line'];

            $lineCost = ($stopLine - $startLine) + 1;
            if ($lineCost > $this->maxLineCost) {
                $errorClasses[] = $nextClass;
            }

        }

        foreach ($errorClasses as $errorClassPtr) {
            $error = 'Only one class or multiple smaller ones (Less than ' . $this->maxLineCost . ' lines) is allowed per file';
            $phpcsFile->addError($error, $errorClassPtr, 'MultipleFound');
        }

    }

}

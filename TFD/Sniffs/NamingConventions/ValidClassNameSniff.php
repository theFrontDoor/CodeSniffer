<?php

namespace TFD\Sniffs\NamingConventions;

use PHP_CodeSniffer\Files\File;

if (class_exists('PEAR\Sniffs\NamingConventions\ValidClassNameSniff', TRUE) === FALSE) {
    throw new \PHP_CodeSniffer_Exception('Class PEAR\Sniffs\NamingConventions\ValidClassNameSniff not found');
}

class ValidClassNameSniff extends \PEAR\Sniffs\NamingConventions\ValidClassNameSniff {

    public function register() {
        return array_merge(parent::register(), [
            T_CLASS,
            T_INTERFACE,
            T_TRAIT,
        ]);

    }//end register()

    public function process(File $phpcsFile, $stackPtr) {

        $tokens = $phpcsFile->getTokens();

        $classNamePtr = $phpcsFile->findNext(T_STRING, $stackPtr);
        $className    = trim($tokens[$classNamePtr]['content']);

        if ($tokens[$stackPtr]['type'] === 'T_INTERFACE') {

            if ($className[0] !== 'I') {
                $phpcsFile->addError('Interface "' . $className . '" must be prefixed with an I', $stackPtr, 'InterfaceNoI');
            }

        } elseif ($tokens[$stackPtr]['type'] === 'T_TRAIT') {

            if ($className[0] !== 'T') {
                $phpcsFile->addError('Trait "' . $className . '" must be prefixed with an T', $stackPtr, 'TraitNoT');
            }

        } else {

            $abstract = $phpcsFile->findPrevious(T_ABSTRACT, $classNamePtr);
            $isAbstract = $tokens[$abstract]['line'] === $tokens[$classNamePtr]['line'];

            if ($isAbstract && $className[0] !== 'A') {
                $phpcsFile->addError('Abstract class "' . $className . '" must be prefixed with an A', $stackPtr, 'AbstractClassNoA');
            }

        }

    }

    protected function processTokenOutsideScope(File $phpcsFile, $stackPtr) {

    }

}

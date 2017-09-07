<?php
/**
 * TFD_Sniffs_NamingConventions_ValidVariableNameSniff.
 */


namespace TFD\Sniffs\ControlStructures;

use PHP_CodeSniffer\Files\File;

if (!class_exists('PHP\CodeSniffer\Standards\AbstractVariableSniff', TRUE)) {
    throw new PHP_CodeSniffer_Exception('Class PHP\CodeSniffer\Standards\AbstractVariableSniff not found');
}

/**
 * TFD_Sniffs_NamingConventions_ValidVariableNameSniff.
 *
 * Checks the naming of member variables.
 */
class TFD_Sniffs_NamingConventions_ValidVariableNameSniff extends \PHP\CodeSniffer\Standards\AbstractVariableSniff {

    /**
     * Processes class member variables.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token
     *                                               in the stack passed in $tokens.
     *
     * @return void
     */
    protected function processMemberVar(File $phpcsFile, $stackPtr) {

        $tokens = $phpcsFile->getTokens();

        $memberProps = $phpcsFile->getMemberProperties($stackPtr);
        if (empty($memberProps)) {
            return;
        }

        $memberName     = ltrim($tokens[$stackPtr]['content'], '$');
        $scope          = $memberProps['scope'];
        $scopeSpecified = $memberProps['scope_specified'];
        $isPublic       = $memberProps['scope'] === 'public';
        $isStatic       = $memberProps['is_static'];
        $firstChar      = ($memberName[0] === '_' ? $memberName[1] : $memberName[0]);
        $isUpperCase    = $firstChar === strtoupper($firstChar);

        // If it's a private member, it must have an underscore on the front.
        if (!$isPublic && $memberName[0] !== '_') {
            $error = '%s member variable "%s" must be prefixed with an underscore';
            $data  = [
                ucfirst($scope),
                $memberName
            ];
            $phpcsFile->addError($error, $stackPtr, 'PrivateNoUnderscore', $data);
            return;
        }

        // If it's not a private member, it must not have an underscore on the front.
        if ($isPublic && $scopeSpecified && $memberName{0} === '_') {
            $error = '%s member variable "%s" must not be prefixed with an underscore';
            $data = [
                ucfirst($scope),
                $memberName,
            ];
            $phpcsFile->addError($error, $stackPtr, 'PublicUnderscore', $data);
            return;
        }

        // If the member is static and doesn't start with an uppercase letter
        if ($isStatic && !$isUpperCase) {
            $error = '%s member variable "%s" must is static and must start with an uppercase character';
            $data  = [
                ucfirst($scope),
                $memberName,
            ];
            $phpcsFile->addError($error, $stackPtr, 'StaticUppercase', $data);
            return;
        }

        if (!$isStatic && $isUpperCase) {
            $error = '%s member variable "%s" must is not static and should start with an lowercase character';
            $data  = [
                ucfirst($scope),
                $memberName,
            ];
            $phpcsFile->addError($error, $stackPtr, 'StaticUppercase', $data);
            return;
        }

    }//end processMemberVar()


    /**
     * Processes normal variables.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file where this token was found.
     * @param int                         $stackPtr  The position where the token was found.
     *
     * @return void
     */
    protected function processVariable(File $phpcsFile, $stackPtr) {

        /*
            We don't care about normal variables.
        */

    }//end processVariable()


    /**
     * Processes variables in double quoted strings.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file where this token was found.
     * @param int                         $stackPtr  The position where the token was found.
     *
     * @return void
     */
    protected function processVariableInString(File $phpcsFile, $stackPtr) {

        /*
            We don't care about normal variables.
        */

    }//end processVariableInString()


}//end class

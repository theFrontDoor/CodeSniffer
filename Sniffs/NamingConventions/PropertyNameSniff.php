<?php

if (class_exists('PHP_CodeSniffer_Standards_AbstractVariableSniff', TRUE) === FALSE) {
    $error = 'Class PHP_CodeSniffer_Standards_AbstractVariableSniff not found';
    throw new PHP_CodeSniffer_Exception($error);
}

class TFD_Sniffs_NamingConventions_PropertyNameSniff extends PHP_CodeSniffer_Standards_AbstractVariableSniff {

    /**
     * Processes class member variables.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return void
     */
    protected function processMemberVar(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
        $tokens = $phpcsFile->getTokens();

        $memberProps = $phpcsFile->getMemberProperties($stackPtr);
        if (empty($memberProps) === TRUE) {
            return;
        }

        $memberName     = ltrim($tokens[$stackPtr]['content'], '$');
        $isPublic       = ($memberProps['scope'] !== 'public') ? FALSE : TRUE;
        $isStatic       = $memberProps['is_static'];
        $scope          = $memberProps['scope'];
        $scopeSpecified = $memberProps['scope_specified'];

        // If it's a private member, it must have an underscore on the front.
        if ($isPublic === FALSE && $memberName{0} !== '_') {
            $error = 'Private member variable "%s" must be prefixed with an underscore';
            $data  = array($memberName);
            $phpcsFile->addError($error, $stackPtr, 'PrivateNoUnderscore', $data);
            return;
        }

        // If it's not a private member, it must not have an underscore on the front.
        if ($isPublic === TRUE && $scopeSpecified === TRUE && $memberName{0} === '_') {
            $error = '%s member variable "%s" must not be prefixed with an underscore';
            $data  = array(ucfirst($scope), $memberName);
            $phpcsFile->addError($error, $stackPtr, 'PublicUnderscore', $data);
            return;
        }

        if ($isStatic) {
            $testMemberName = $memberName;
            if ($isPublic === FALSE) {
                $testMemberName = substr($memberName, 1);
            }

            if (PHP_CodeSniffer::isCamelCaps($testMemberName, $isStatic, $isPublic, FALSE) === FALSE) {
                $error = '%s static member variable "%s" is not in upper camel case format';
                $data  = array(
                    ucfirst($scope),
                    $memberName,
                );
                $phpcsFile->addError($error, $stackPtr, 'MemberNotCamelCaps', $data);
            }

        }

    }//end processMemberVar()


    /**
     * Processes normal variables.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file where this token was found.
     * @param int                  $stackPtr  The position where the token was found.
     *
     * @return void
     */
    protected function processVariable(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
        // We don't care about normal variables.
    }//end processVariable()


    /**
     * Processes variables in double quoted strings.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file where this token was found.
     * @param int                  $stackPtr  The position where the token was found.
     *
     * @return void
     */
    protected function processVariableInString(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
        // We don't care about normal variables.
    }//end processVariableInString()


}//end class

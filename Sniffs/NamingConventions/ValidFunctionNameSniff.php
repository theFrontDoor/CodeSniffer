<?php
/**
 * TFD_Sniffs_NamingConventions_ValidFunctionNameSniff.
 */

if (class_exists('PHP_CodeSniffer_Standards_AbstractScopeSniff', TRUE) === FALSE) {
    throw new PHP_CodeSniffer_Exception('Class PHP_CodeSniffer_Standards_AbstractScopeSniff not found');
}

/**
 * TFD_Sniffs_NamingConventions_ValidFunctionNameSniff.
 *
 * Ensures method names are correct depending on whether they are public, private, or static and that functions are named correctly.
 *
 */
class TFD_Sniffs_NamingConventions_ValidFunctionNameSniff extends PHP_CodeSniffer_Standards_AbstractScopeSniff {


    /**
     * A list of all PHP magic methods.
     *
     * @var array
     */
    protected $magicMethods = [
        'construct'  => TRUE,
        'destruct'   => TRUE,
        'call'       => TRUE,
        'callstatic' => TRUE,
        'get'        => TRUE,
        'set'        => TRUE,
        'isset'      => TRUE,
        'unset'      => TRUE,
        'sleep'      => TRUE,
        'wakeup'     => TRUE,
        'tostring'   => TRUE,
        'set_state'  => TRUE,
        'clone'      => TRUE,
        'invoke'     => TRUE,
        'debuginfo'  => TRUE,
    ];

    /**
     * A list of all PHP magic functions.
     *
     * @var array
     */
    protected $magicFunctions = ['autoload' => TRUE];


    /**
     * Constructs a TFD_Sniffs_NamingConventions_ValidFunctionNameSniff.
     */
    public function __construct() {
        parent::__construct([T_CLASS, T_ANON_CLASS, T_INTERFACE, T_TRAIT], [T_FUNCTION], TRUE);

    }//end __construct()


    /**
     * Processes the tokens within the scope.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being processed.
     * @param int                  $stackPtr  The position where this token was
     *                                        found.
     * @param int                  $currScope The position of the current scope.
     *
     * @return void
     */
    protected function processTokenWithinScope(PHP_CodeSniffer_File $phpcsFile, $stackPtr, $currScope) {
        $methodName = $phpcsFile->getDeclarationName($stackPtr);
        if ($methodName === NULL) {
            // Ignore closures.
            return;
        }

        $className = $phpcsFile->getDeclarationName($currScope);
        $errorData = [$className.'::'.$methodName];

        // Is this a magic method. i.e., is prefixed with "__" ?
        if (preg_match('|^__[^_]|', $methodName) !== 0) {
            $magicPart = strtolower(substr($methodName, 2));
            if (isset($this->magicMethods[$magicPart]) === FALSE) {
                $error = 'Method name "%s" is invalid; only PHP magic methods should be prefixed with a double underscore';
                $phpcsFile->addError($error, $stackPtr, 'MethodDoubleUnderscore', $errorData);
            }

            return;
        }

        // PHP4 constructors are allowed to break our rules.
        if ($methodName === $className) {
            return;
        }

        // PHP4 destructors are allowed to break our rules.
        if ($methodName === '_'.$className) {
            return;
        }

        $methodProps    = $phpcsFile->getMethodProperties($stackPtr);
        $scope          = $methodProps['scope'];
        $scopeSpecified = $methodProps['scope_specified'];

        $isPublic = ($methodProps['scope'] === 'public');

        // If it's a private and protected methods must have an underscore on the front.
        if (!$isPublic) {
            if ($methodName{0} !== '_') {
                $error = 'Private method name "%s" must be prefixed with an underscore';
                $phpcsFile->addError($error, $stackPtr, 'PrivateNoUnderscore', $errorData);
                $phpcsFile->recordMetric($stackPtr, 'Private method prefixed with underscore', 'no');
                return;
            } else {
                $phpcsFile->recordMetric($stackPtr, 'Private method prefixed with underscore', 'yes');
            }
        }

        // If it's not a private method, it must not have an underscore on the front.
        if ($isPublic === TRUE && $scopeSpecified === TRUE && $methodName{0} === '_') {
            $error = '%s method name "%s" must not be prefixed with an underscore';
            $data  = [
                ucfirst($scope),
                $errorData[0],
            ];
            $phpcsFile->addError($error, $stackPtr, 'PublicUnderscore', $data);
            return;
        }

        // If the scope was specified on the method, then the method must be
        // camel caps and an underscore should be checked for. If it wasn't
        // specified, treat it like a public method and remove the underscore
        // prefix if there is one because we cant determine if it is private or
        // public.
        $testMethodName = $methodName;
        if ($methodName[0] === '_') {
            $testMethodName = substr($methodName, 1);
        }

        if (!PHP_CodeSniffer::isCamelCaps($testMethodName, $methodProps['is_static'], $isPublic, FALSE)) {

            $error = '%s method name "%s" is not in camel caps format';
            $data  = [
                ($scopeSpecified ? ucfirst($scope) : 'Method'),
                $errorData[0],
            ];
            $phpcsFile->addError($error, $stackPtr, 'ScopeNotCamelCaps', $data);

            return;
        }

    }//end processTokenWithinScope()


    /**
     * Processes the tokens outside the scope.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being processed.
     * @param int                  $stackPtr  The position where this token was
     *                                        found.
     *
     * @return void
     */
    protected function processTokenOutsideScope(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
        $functionName = $phpcsFile->getDeclarationName($stackPtr);
        if ($functionName === NULL) {
            // Ignore closures.
            return;
        }

        if (ltrim($functionName, '_') === '') {
            // Ignore special functions.
            return;
        }

        $errorData = [$functionName];

        // Is this a magic function. i.e., it is prefixed with "__".
        if (preg_match('|^__[^_]|', $functionName) !== 0) {
            $magicPart = strtolower(substr($functionName, 2));
            if (isset($this->magicFunctions[$magicPart]) === FALSE) {
                $error = 'Function name "%s" is invalid; only PHP magic methods should be prefixed with a double underscore';
                $phpcsFile->addError($error, $stackPtr, 'FunctionDoubleUnderscore', $errorData);
            }

            return;
        }

        // Function names can be in two parts; the package name and
        // the function name.
        $packagePart   = '';
        $camelCapsPart = '';
        $underscorePos = strrpos($functionName, '_');
        if ($underscorePos === FALSE) {
            $camelCapsPart = $functionName;
        } else {
            $packagePart   = substr($functionName, 0, $underscorePos);
            $camelCapsPart = substr($functionName, ($underscorePos + 1));

            // We don't care about _'s on the front.
            $packagePart = ltrim($packagePart, '_');
        }

        // If it has a package part, make sure the first letter is a capital.
        if ($packagePart !== '') {
            if ($functionName{0} === '_') {
                $error = 'Function name "%s" is invalid; only private methods should be prefixed with an underscore';
                $phpcsFile->addError($error, $stackPtr, 'FunctionUnderscore', $errorData);
                return;
            }

            if ($functionName{0} !== strtoupper($functionName{0})) {
                $error = 'Function name "%s" is prefixed with a package name but does not begin with a capital letter';
                $phpcsFile->addError($error, $stackPtr, 'FunctionNoCapital', $errorData);
                return;
            }
        }

        // If it doesn't have a camel caps part, it's not valid.
        if (trim($camelCapsPart) === '') {
            $error = 'Function name "%s" is not valid; name appears incomplete';
            $phpcsFile->addError($error, $stackPtr, 'FunctionInvalid', $errorData);
            return;
        }

        $validName        = TRUE;
        $newPackagePart   = $packagePart;
        $newCamelCapsPart = $camelCapsPart;

        // Every function must have a camel caps part, so check that first.
        if (PHP_CodeSniffer::isCamelCaps($camelCapsPart, FALSE, TRUE, FALSE) === FALSE) {
            $validName        = FALSE;
            $newCamelCapsPart = strtolower($camelCapsPart{0}).substr($camelCapsPart, 1);
        }

        if ($packagePart !== '') {
            // Check that each new word starts with a capital.
            $nameBits = explode('_', $packagePart);
            foreach ($nameBits as $bit) {
                if ($bit{0} !== strtoupper($bit{0})) {
                    $newPackagePart = '';
                    foreach ($nameBits as $bit) {
                        $newPackagePart .= strtoupper($bit{0}).substr($bit, 1).'_';
                    }

                    $validName = FALSE;
                    break;
                }
            }
        }

        if ($validName === FALSE) {
            $newName = rtrim($newPackagePart, '_').'_'.$newCamelCapsPart;
            if ($newPackagePart === '') {
                $newName = $newCamelCapsPart;
            } else {
                $newName = rtrim($newPackagePart, '_').'_'.$newCamelCapsPart;
            }

            $error  = 'Function name "%s" is invalid; consider "%s" instead';
            $data   = $errorData;
            $data[] = $newName;
            $phpcsFile->addError($error, $stackPtr, 'FunctionNameInvalid', $data);
        }

    }//end processTokenOutsideScope()


}//end class

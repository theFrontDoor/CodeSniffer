<?php

if (class_exists('PHP_CodeSniffer_Standards_AbstractScopeSniff', TRUE) === FALSE) {
    throw new PHP_CodeSniffer_Exception('Class PHP_CodeSniffer_Standards_AbstractScopeSniff not found');
}

class TFD_Sniffs_NamingConventions_MethodNameSniff extends PHP_CodeSniffer_Standards_AbstractScopeSniff
{

    /**
     * A list of all PHP magic methods.
     *
     * @var array
     */
    protected $magicMethods = array(
        'construct',
        'destruct',
        'call',
        'callstatic',
        'get',
        'set',
        'isset',
        'unset',
        'sleep',
        'wakeup',
        'tostring',
        'set_state',
        'clone',
        'invoke',
        'call',
        'debuginfo'
    );

    /**
     * A list of all PHP non-magic methods starting with a double underscore.
     *
     * These come from PHP modules such as SOAPClient.
     *
     * @var array
     */
    protected $methodsDoubleUnderscore = array(
        'soapcall',
        'getlastrequest',
        'getlastresponse',
        'getlastrequestheaders',
        'getlastresponseheaders',
        'getfunctions',
        'gettypes',
        'dorequest',
        'setcookie',
        'setlocation',
        'setsoapheaders',
    );

    /**
     * A list of all PHP magic functions.
     *
     * @var array
     */
    protected $magicFunctions = array('autoload');

    /**
     * If TRUE, the string must not have two capital letters next to each other.
     *
     * @var bool
     */
    public $strict = FALSE;

    /**
     * Constructs a Generic_Sniffs_NamingConventions_CamelCapsFunctionNameSniff.
     */
    public function __construct() {
        parent::__construct(array(T_CLASS, T_INTERFACE, T_TRAIT), array(T_FUNCTION), TRUE);
    }


    /**
     * Processes the tokens within the scope.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being processed.
     * @param int                  $stackPtr  The position where this token was found.
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
        $errorData = array($className.'::'.$methodName);

        // Is this a magic method. i.e., is prefixed with "__" ?
        if (preg_match('|^__|', $methodName) !== 0) {
            $magicPart = strtolower(substr($methodName, 2));
            if (in_array($magicPart, $this->magicMethods) === FALSE) {
                $error = 'Method name "%s" is invalid; only PHP magic methods should be prefixed with a double underscore';
                $phpcsFile->addWarning($error, $stackPtr, 'MethodDoubleUnderscore', $errorData);
            }

            return;
        }

        $methodProps    = $phpcsFile->getMethodProperties($stackPtr);
        $isPublic       = ($methodProps['scope'] !== 'public') ? FALSE : TRUE;
        $isStatic       = $methodProps['is_static'];
        $scope          = $methodProps['scope'];
        $scopeSpecified = $methodProps['scope_specified'];

        // If it's a private method, it must have an underscore on the front.
        if ($isPublic === FALSE && $methodName{0} !== '_') {
            $error = 'Private method name "%s" must be prefixed with an underscore';
            $phpcsFile->addWarning($error, $stackPtr, 'PrivateNoUnderscore', $errorData);
            return;
        }

        // If it's not a private method, it must not have an underscore on the front.
        if ($isPublic === TRUE && $scopeSpecified === TRUE && $methodName{0} === '_') {

            $error = '%s method name "%s" must not be prefixed with an underscore';
            $data = array( ucfirst($scope), $errorData[0]);

            $phpcsFile->addWarning($error, $stackPtr, 'PublicUnderscore', $data);
            return;
        }

        // If the scope was specified on the method, then the method must be
        // camel caps and an underscore should be checked for. If it wasn't
        // specified, treat it like a public method and remove the underscore
        // prefix if there is one because we cant determine if it is private or
        // public.
        $testMethodName = $methodName;
        if ($isPublic === FALSE && $isStatic) {
            $testMethodName = substr($methodName, 1);
        }

        if (PHP_CodeSniffer::isCamelCaps($testMethodName, $isStatic, $isPublic, FALSE) === FALSE) {
            if ($scopeSpecified === TRUE) {

                $error = '%s method name "%s" is not in camel case format';
                if ($isStatic) {
                    $error = '%s static method name "%s" is not in upper camel case format';
                }
                $data = array(ucfirst($scope), $errorData[0]);

                $phpcsFile->addWarning($error, $stackPtr, 'ScopeNotCamelCaps', $data);
            } else {
                $error = 'Method name "%s" is not in camel case format';
                $phpcsFile->addWarning($error, $stackPtr, 'NotCamelCaps', $errorData);
            }

        }

        $visibility = 0;
        $static     = 0;
        $abstract   = 0;
        $final      = 0;

        $tokens = $phpcsFile->getTokens();
        $find   = PHP_CodeSniffer_Tokens::$methodPrefixes;
        $find[] = T_WHITESPACE;
        $prev   = $phpcsFile->findPrevious($find, ($stackPtr - 1), NULL, TRUE);

        $prefix = $stackPtr;
        while (($prefix = $phpcsFile->findPrevious(PHP_CodeSniffer_Tokens::$methodPrefixes, ($prefix - 1), $prev)) !== FALSE) {
            switch ($tokens[$prefix]['code']) {
                case T_STATIC:
                    $static = $prefix;
                    break;
                case T_ABSTRACT:
                    $abstract = $prefix;
                    break;
                case T_FINAL:
                    $final = $prefix;
                    break;
                default:
                    $visibility = $prefix;
                    break;
            }
        }

        // $phpcsFile->addFixableError('$static: ' . $static . '$abstract: ' . $abstract . '$final: ' . $final . '$visibility: ' . $visibility, $stackPtr, 'Wut');

        $fixes = array();
        if ($visibility !== 0 && $final > $visibility) {
            $error = 'The final declaration must precede the visibility declaration';
            $fix   = $phpcsFile->addFixableError($error, $final, 'FinalAfterVisibility');
            if ($fix === TRUE) {
                $fixes[$final]       = '';
                $fixes[($final + 1)] = '';
                if (isset($fixes[$visibility]) === TRUE) {
                    $fixes[$visibility] = 'final '.$fixes[$visibility];
                } else {
                    $fixes[$visibility] = 'final '.$tokens[$visibility]['content'];
                }
            }
        }
        if ($visibility !== 0 && $abstract > $visibility) {
            $error = 'The abstract declaration must precede the visibility declaration';
            $fix   = $phpcsFile->addFixableError($error, $abstract, 'AbstractAfterVisibility');
            if ($fix === TRUE) {
                $fixes[$abstract]       = '';
                $fixes[($abstract + 1)] = '';
                if (isset($fixes[$visibility]) === TRUE) {
                    $fixes[$visibility] = 'abstract '.$fixes[$visibility];
                } else {
                    $fixes[$visibility] = 'abstract '.$tokens[$visibility]['content'];
                }
            }
        }
        if ($static !== 0 && $static < $visibility) {
            $error = 'The static declaration must come after the visibility declaration';
            $fix   = $phpcsFile->addFixableError($error, $static, 'StaticBeforeVisibility');
            if ($fix === TRUE) {
                $fixes[$static]       = '';
                $fixes[($static + 1)] = '';
                if (isset($fixes[$visibility]) === TRUE) {
                    $fixes[$visibility] = $fixes[$visibility].' static';
                } else {
                    $fixes[$visibility] = $tokens[$visibility]['content'].' static';
                }
            }
        }

        // Batch all the fixes together to reduce the possibility of conflicts.
        if (!empty($fixes)) {
            $phpcsFile->fixer->beginChangeset();
            foreach ($fixes as $stackPtr => $content) {
                $phpcsFile->fixer->replaceToken($stackPtr, $content);
            }
            $phpcsFile->fixer->endChangeset();
            return;
        }

    }

    protected function processTokenOutsideScope(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
        $functionName = $phpcsFile->getDeclarationName($stackPtr);
        if ($functionName === NULL) {
            // Ignore closures.
            return;
        }

        $errorData = array($functionName);

        // Is this a magic function. i.e., it is prefixed with "__".
        if (preg_match('|^__|', $functionName) !== 0) {
            $magicPart = strtolower(substr($functionName, 2));
            if (in_array($magicPart, $this->magicFunctions) === FALSE) {
                 $error = 'Function name "%s" is invalid; only PHP magic methods should be prefixed with a double underscore';
                 $phpcsFile->addWarning($error, $stackPtr, 'FunctionDoubleUnderscore', $errorData);
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
                $phpcsFile->addWarning($error, $stackPtr, 'FunctionUnderscore', $errorData);
                return;
            }

            /*
            if ($functionName{0} !== strtoupper($functionName{0})) {
                $error = 'Function name "%s" is prefixed with a package name but does not begin with a capital letter';
                $phpcsFile->addWarning($error, $stackPtr, 'FunctionNoCapital', $errorData);
                return;
            }
            */
        }

        // If it doesn't have a camel caps part, it's not valid.
        if (trim($camelCapsPart) === '') {
            $error = 'Function name "%s" is not valid; name appears incomplete';
            $phpcsFile->addWarning($error, $stackPtr, 'FunctionInvalid', $errorData);
            return;
        }

        $validName        = TRUE;
        $newPackagePart   = $packagePart;
        $newCamelCapsPart = $camelCapsPart;

    }
}

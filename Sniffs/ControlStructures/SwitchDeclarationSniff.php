<?php

class TFD_Sniffs_ControlStructures_SwitchDeclarationSniff implements PHP_CodeSniffer_Sniff
{

    /**
     * The number of spaces code should be indented.
     *
     * @var int
     */
    public $indent = 4;


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register() {
        return array(T_SWITCH);
    }//end register()


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token in the
     *                                        stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {

        $tokens = $phpcsFile->getTokens();

        // We can't process SWITCH statements unless we know where they start and end.
        if (isset($tokens[$stackPtr]['scope_opener']) === FALSE
            || isset($tokens[$stackPtr]['scope_closer']) === FALSE
        ) {
            return;
        }

        $switch        = $tokens[$stackPtr];
        $nextCase      = $stackPtr;
        $caseAlignment = ($switch['column'] + $this->indent);
        $caseCount     = 0;
        $foundDefault  = FALSE;

        while (($nextCase = $this->_findNextCase($phpcsFile, ($nextCase + 1), $switch['scope_closer'])) !== FALSE) {
            if ($tokens[$nextCase]['code'] === T_DEFAULT) {
                $type         = 'default';
                $foundDefault = TRUE;
            } else {
                $type = 'case';
                $caseCount++;
            }

            if ($tokens[$nextCase]['content'] !== strtolower($tokens[$nextCase]['content'])) {
                $expected = strtolower($tokens[$nextCase]['content']);
                $error    = strtoupper($type).' keyword must be lowercase; expected "%s" but found "%s"';
                $data     = array(
                             $expected,
                             $tokens[$nextCase]['content'],
                            );

                $fix = $phpcsFile->addWarning($error, $nextCase, $type.'NotLower', $data);
                if ($fix === TRUE) {
                    $phpcsFile->fixer->replaceToken($nextCase, $expected);
                }
            }

            if ($type === 'case'
                && ($tokens[($nextCase + 1)]['code'] !== T_WHITESPACE
                || $tokens[($nextCase + 1)]['content'] !== ' ')
            ) {
                $error = 'CASE keyword must be followed by a single space';
                $fix   = $phpcsFile->addWarning($error, $nextCase, 'SpacingAfterCase');
                if ($fix === TRUE) {
                    if ($tokens[($nextCase + 1)]['code'] !== T_WHITESPACE) {
                        $phpcsFile->fixer->addContent($nextCase, ' ');
                    } else {
                        $phpcsFile->fixer->replaceToken(($nextCase + 1), ' ');
                    }
                }
            }

            $opener     = $tokens[$nextCase]['scope_opener'];
            $nextCloser = $tokens[$nextCase]['scope_closer'];
            if ($tokens[$opener]['code'] === T_COLON) {
                if ($tokens[($opener - 1)]['code'] === T_WHITESPACE) {
                    $error = 'There must be no space before the colon in a '.strtoupper($type).' statement';
                    $fix   = $phpcsFile->addWarning($error, $nextCase, 'SpaceBeforeColon'.strtoupper($type));
                    if ($fix === TRUE) {
                        $phpcsFile->fixer->replaceToken(($opener - 1), '');
                    }
                }

                // $next = $phpcsFile->findNext(T_WHITESPACE, ($opener + 1), NULL, TRUE);
                // if ($tokens[$next]['line'] === $tokens[$opener]['line']
                //     && $tokens[$next]['code'] === T_COMMENT
                // ) {
                //     // Skip comments on the same line.
                //     $next = $phpcsFile->findNext(T_WHITESPACE, ($next + 1), NULL, TRUE);
                // }

                // if ($tokens[$next]['line'] !== ($tokens[$opener]['line'] + 1)) {
                //     $error = 'The '.strtoupper($type).' body must start on the line following the statement';
                //     $fix   = $phpcsFile->addWarning($error, $nextCase, 'BodyOnNextLine'.strtoupper($type));
                //     if ($fix === TRUE) {
                //         if ($tokens[$next]['line'] === $tokens[$opener]['line']) {
                //             $padding = str_repeat(' ', ($caseAlignment + $this->indent - 1));
                //             $phpcsFile->fixer->addContentBefore($next, $phpcsFile->eolChar.$padding);
                //         } else {
                //             $phpcsFile->fixer->beginChangeset();
                //             for ($i = ($opener + 1); $i < $next; $i++) {
                //                 if ($tokens[$i]['line'] === $tokens[$next]['line']) {
                //                     break;
                //                 }

                //                 $phpcsFile->fixer->replaceToken($i, '');
                //             }

                //             $phpcsFile->fixer->addNewLineBefore($i);
                //             $phpcsFile->fixer->endChangeset();
                //         }
                //     }
                // }//end if

                if ($tokens[$nextCloser]['scope_condition'] === $nextCase) {
                    // Only need to check some things once, even if the
                    // closer is shared between multiple case statements, or even
                    // the default case.
                    $prev = $phpcsFile->findPrevious(T_WHITESPACE, ($nextCloser - 1), $nextCase, TRUE);
                    if ($tokens[$prev]['line'] === $tokens[$nextCloser]['line']) {
                        $error = 'Terminating statement must be on a line by itself';
                        $fix   = $phpcsFile->addWarning($error, $nextCloser, 'BreakNotNewLine');
                        if ($fix === TRUE) {
                            $phpcsFile->fixer->addNewLine($prev);
                            $phpcsFile->fixer->replaceToken($nextCloser, trim($tokens[$nextCloser]['content']));
                        }
                    } else {
                        $diff = ($caseAlignment + $this->indent - $tokens[$nextCloser]['column']);
                        if ($diff !== 0) {
                            $error = 'Terminating statement must be indented to the same level as the CASE body';
                            $fix   = $phpcsFile->addWarning($error, $nextCloser, 'BreakIndent');
                            if ($fix === TRUE) {
                                if ($diff > 0) {
                                    $phpcsFile->fixer->addContentBefore($nextCloser, str_repeat(' ', $diff));
                                } else {
                                    $phpcsFile->fixer->substrToken(($nextCloser - 1), 0, $diff);
                                }
                            }
                        }
                    }//end if
                }//end if
            } else {
                $error = strtoupper($type).' statements must be defined using a colon';
                $phpcsFile->addError($error, $nextCase, 'WrongOpener'.$type);
            }//end if

            // We only want cases from here on in.
            if ($type !== 'case') {
                continue;
            }

            $nextCode = $phpcsFile->findNext(
                T_WHITESPACE,
                ($tokens[$nextCase]['scope_opener'] + 1),
                $nextCloser,
                TRUE
            );

            if ($tokens[$nextCode]['code'] !== T_CASE && $tokens[$nextCode]['code'] !== T_DEFAULT) {
                // This case statement has content. If the next case or default comes
                // before the closer, it means we dont have a terminating statement
                // and instead need a comment.
                $nextCode = $this->_findNextCase($phpcsFile, ($tokens[$nextCase]['scope_opener'] + 1), $nextCloser);
                if ($nextCode !== FALSE) {
                    $prevCode = $phpcsFile->findPrevious(T_WHITESPACE, ($nextCode - 1), $nextCase, TRUE);
                    if ($tokens[$prevCode]['code'] !== T_COMMENT) {
                        $error = 'There must be a comment when fall-through is intentional in a non-empty case body';
                        $phpcsFile->addError($error, $nextCase, 'TerminatingComment');
                    }
                }
            }
        }//end while

    }//end process()


    /**
     * Find the next CASE or DEFAULT statement from a point in the file.
     *
     * Note that nested switches are ignored.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position to start looking at.
     * @param int                  $end       The position to stop looking at.
     *
     * @return int | bool
     */
    private function _findNextCase(PHP_CodeSniffer_File $phpcsFile, $stackPtr, $end) {

        $tokens = $phpcsFile->getTokens();
        while (($stackPtr = $phpcsFile->findNext(array(T_CASE, T_DEFAULT, T_SWITCH), $stackPtr, $end)) !== FALSE) {
            // Skip nested SWITCH statements; they are handled on their own.
            if ($tokens[$stackPtr]['code'] === T_SWITCH) {
                $stackPtr = $tokens[$stackPtr]['scope_closer'];
                continue;
            }

            break;
        }

        return $stackPtr;

    }//end _findNextCase()


}//end class

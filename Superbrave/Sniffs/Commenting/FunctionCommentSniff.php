<?php
/**
 * This file is part of the Superbrave coding standards
 *
 * Minimal required PHP version is 5.6
 *
 * @category PHP
 * @package  Superbrave-codingstandards
 * @author   Stefan Thoolen <st@superbrave.nl>
 * @license  http://spdx.org/licenses/MIT MIT License
 * @link     https://github.com/superbrave/coding-standards
 */

namespace Superbrave\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;

/**
 * FunctionComment Sniffs
 *
 * This class extends the PEAR checks, but catches {@inheritdoc} docblocks so they can be ignored.
 */
class FunctionCommentSniff extends \PHP_CodeSniffer\Standards\PEAR\Sniffs\Commenting\FunctionCommentSniff
{
    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        parent::process($phpcsFile, $stackPtr);
        $this->processReturnAboveThrows($phpcsFile, $stackPtr);
    }

    /**
     * A throws call must be below the return call.
     *
     * The Superbrave standards follows the "Symfony Way" in this sniff.
     *
     * @param File $phpcsFile    The file being scanned.
     * @param int  $stackPtr     The position of the current token in the stack passed in $tokens.
     *
     * @return void
     */
    protected function processReturnAboveThrows(File $phpcsFile, $stackPtr)
    {
        // Fetches the full function docblock
        $startToken = null;
        $endToken = null;
        $docblock = $this->getDocBlock($phpcsFile, $stackPtr, $startToken, $endToken);
        if ($this->hasInheritDoc($phpcsFile, $stackPtr, $docblock)) {
            return;
        }

        // Fetches tag locations
        $return_pos = strpos($docblock, '@return');
        $throws_pos = strpos($docblock, '@throws');

        // One of the elements doesn't exist
        if ($throws_pos === false || $return_pos === false) {
            return;
        }

        // Is the throws above return?
        if ($return_pos > $throws_pos) {
            $error = '@throws tags should be below the @return tag, not above the @return tag';
            $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'ReturnAboveThrows');

            // When the patcher is active, fix the issue
            if ($fix === true) {
                // Breaks up the docblock in three parts; original docblock, return and throws
                $parts = explode('* @', $docblock);
                $general = $return = $throws = array();
                foreach ($parts as $part) {
                    if (strtolower(substr($part, 0, 6)) == 'return') {
                        array_push($return, $part);
                    } else if (strtolower(substr($part, 0, 6)) == 'throws') {
                        array_push($throws, $part);
                    } else {
                        array_push($general, $part);
                    }
                }

                // Reglues the three parts in correct sequence
                $newDocblock = implode('* @', array_merge(
                    $general,
                    $return,
                    $throws
                ));

                // Patches the file
                $phpcsFile->fixer->beginChangeset();
                $phpcsFile->fixer->replaceToken($startToken, $newDocblock);
                for ($token = $startToken + 1; $token < $endToken; ++$token) {
                    $phpcsFile->fixer->replaceToken($token, "");
                }
                $phpcsFile->fixer->endChangeset();
            }
        }
    }

    /**
     * Process the return comment of this function comment.
     *
     * @param File $phpcsFile    The file being scanned.
     * @param int  $stackPtr     The position of the current token in the stack passed in $tokens.
     * @param int  $commentStart The position in the stack where the comment started.
     *
     * @return void
     */
    protected function processReturn(File $phpcsFile, $stackPtr, $commentStart)
    {
        if ($this->hasInheritDoc($phpcsFile, $stackPtr)) {
            return;
        }

        parent::processReturn($phpcsFile, $stackPtr, $commentStart);
    }

    /**
     * Process the function parameter comments.
     *
     * @param File $phpcsFile    The file being scanned.
     * @param int  $stackPtr     The position of the current token in the stack passed in $tokens.
     * @param int  $commentStart The position in the stack where the comment started.
     *
     * @return void
     */
    protected function processParams(File $phpcsFile, $stackPtr, $commentStart)
    {
        if ($this->hasInheritDoc($phpcsFile, $stackPtr)) {
            return;
        }

        parent::processParams($phpcsFile, $stackPtr, $commentStart);
    }

    /**
     * Detects an {@inheritdoc} tag inside of the docblock
     *
     * @param File   $phpcsFile The file being scanned.
     * @param int    $stackPtr  The position of the current token in the stack passed in $tokens.
     * @param string $docblock  Optional; when specified, this will be used as docblock
     *
     * @return boolean True if the comment contains an {@inheritdoc}
     */
    protected function hasInheritDoc(File $phpcsFile, $stackPtr, $docblock = null)
    {
        if ($docblock === null) {
            $docblock = $this->getDocBlock($phpcsFile, $stackPtr);
        }

        // Returns true when {@inheritdoc} exists somewhere in the docblock, otherwise false
        return preg_match('#{@inheritdoc}#i', $docblock) === 1;
    }

    /**
     * Fetches the docblock as string
     *
     * @param File     $phpcsFile  The file being scanned.
     * @param int      $stackPtr   The position of the current token in the stack passed in $tokens.
     * @param int|bool $startToken By reference; the token where the docblock starts
     * @param int|bool $endToken   By reference; the token where the docblock ends
     *
     * @return string
     */
    protected function getDocBlock(File $phpcsFile, $stackPtr, &$startToken = null, &$endToken = null)
    {
        // Fetches the full function docblock
        $startToken = $phpcsFile->findPrevious(T_DOC_COMMENT_OPEN_TAG, $stackPtr - 1);
        if ($startToken === false) {
            return '';
        }
        $endToken = $phpcsFile->findNext(T_DOC_COMMENT_CLOSE_TAG, $startToken);
        if ($endToken === false) {
            return '';
        }
        return $phpcsFile->getTokensAsString($startToken, ($endToken - $startToken));
    }
}

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
        if ($this->hasInheritDoc($phpcsFile, $stackPtr)) {
            return;
        }

        // Fetches the full function docblock
        $docblock = $this->getDocBlock($phpcsFile, $stackPtr);

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
            $phpcsFile->addError($error, $stackPtr, 'ReturnAboveThrows');
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
     * @param File $phpcsFile The file being scanned.
     * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
     *
     * @return boolean True if the comment contains an {@inheritdoc}
     */
    protected function hasInheritDoc(File $phpcsFile, $stackPtr)
    {
        // Returns true when {@inheritdoc} exists somewhere in the docblock, otherwise false
        return preg_match('#{@inheritdoc}#i', $this->getDocBlock($phpcsFile, $stackPtr)) === 1;
    }

    /**
     * Fetches the docblock as string
     *
     * @param File $phpcsFile The file being scanned.
     * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
     *
     * @return string
     */
    protected function getDocBlock(File $phpcsFile, $stackPtr)
    {
        // Fetches the full function docblock
        $start = $phpcsFile->findPrevious(T_DOC_COMMENT_OPEN_TAG, $stackPtr - 1);
        if ($start === false) {
            return '';
        }
        $end   = $phpcsFile->findNext(T_DOC_COMMENT_CLOSE_TAG, $start);
        if ($end === false) {
            return '';
        }
        return $phpcsFile->getTokensAsString($start, ($end - $start));
    }
}

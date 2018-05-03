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

        return parent::processReturn($phpcsFile, $stackPtr, $commentStart);
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

        return parent::processParams($phpcsFile, $stackPtr, $commentStart);
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
        // Fetches the full function docblock
        $start    = $phpcsFile->findPrevious(T_DOC_COMMENT_OPEN_TAG, $stackPtr - 1);
        $end      = $phpcsFile->findNext(T_DOC_COMMENT_CLOSE_TAG, $start);
        $docblock = $phpcsFile->getTokensAsString($start, ($end - $start));

        // Returns true when {@inheritdoc} exists somewhere in the docblock, otherwise false
        return preg_match('#{@inheritdoc}#i', $docblock) === 1;
    }
}

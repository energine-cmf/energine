<?php
/**
 * Document interface.
 *
 * @code
interface IDocument;
@endcode
 */
interface IDocument {
    /**
     * Build.
     */
    public function build();

    /**
     * Get result.
     *
     * @return DOMDocument
     */
    public function getResult();
}
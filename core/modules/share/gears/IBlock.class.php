<?php
/**
 * Block interface.
 *
 * @code
interface IBlock;
@endcode
 */
interface IBlock {
    /**
     * Run execution.
     * @return void
     */



    public function run();

    /**
     * Is enabled?
     * @return bool
     */
    public function enabled();

    /**
     * Get current rights level of the user.
     * This is needed for running current action.
     * @return mixed
     */
    public function getCurrentStateRights();

    /**
     * Build block.
     * @return DOMDocument
     */
    public function build();

    /**
     * Get name.
     * @return string
     */
    public function getName();
}
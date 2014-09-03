<?php
/**
 * @file
 * Link
 *
 * It contains the definition to:
 * @code
class Link;
@endcode
 *
 * @author 1m.dm
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */

namespace Energine\share\gears;
/**
 * Link control element.
 *
 * @code
class Link;
@endcode
 */
class Link extends Control {
    /**
     * @param string $id Control ID.
     * @param string|bool $action Action name.
     * @param string|bool $title Control title.
     * @param string|bool $tooltip Control tooltip.
     */
    public function __construct($id, $action = false, $title = false, $tooltip = false) {
        parent::__construct($id);
        $this->type = 'link';
        if ($action)  $this->setAttribute('action',  $action);
        if ($title)   $this->setAttribute('title',   $title);
        if ($tooltip) $this->setAttribute('tooltip', $tooltip);
    }

    /**
     * Set title.
     *
     * @param string $title Title.
     */
    public function setTitle($title) {
        $this->setAttribute('title', $title);
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle() {
        return $this->getAttribute('title');
    }

    /**
     * Get action name.
     *
     * @return string
     */
    public function getAction() {
        return $this->getAttribute('action');
    }

    /**
     * Set tooltip.
     *
     * @param string $tooltip Tooltip.
     */
    public function setTooltip($tooltip) {
         $this->setAttribute('tooltip', $tooltip);
    }

    /**
     * Get tooltip.
     *
     * @return string
     */
    public function getTooltip() {
        return $this->getAttribute('tooltip');
    }
}

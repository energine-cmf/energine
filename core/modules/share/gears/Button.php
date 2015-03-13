<?php
/**
 * @file
 * Button
 *
 * It contains the definition to:
 * @code
class Button;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */

namespace Energine\share\gears;
/**
 * Button control.
 *
 * @code
class Button;
@endcode
 *
 * Button control on the toolbar.
 */
class Button extends Control {
    /**
     * @param string $id Control ID.
     * @param string|bool $action Action name.
     * @param string|bool $image Image path.
     * @param string|bool $title Control title.
     * @param string|bool $tooltip Control tooltip.
     */
    public function __construct($id, $action = false, $image = false, $title = false, $tooltip = false) {
        parent::__construct($id);
        $this->type = 'button';
        if ($action !== false)  $this->setAttribute('action',  $action);
        if ($image)   $this->setAttribute('image',   $image);
        if ($title)   $this->setAttribute('title',   $title);
        if ($tooltip) $this->setAttribute('tooltip', $tooltip);
    }

    /**
     * Set title.
     *
     * @param string $title Button title.
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
     * Get image path.
     *
     * @return string
     */
    public function getImage() {
        return $this->getAttribute('image');
    }

    /**
     * Set tooltip.
     *
     * @param string $tooltip Tooltip.
     * @return string
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

/**
 * File.
 */
class File extends Button{

}
<?php

namespace ride\web\form\row;

use ride\library\form\row\HtmlRow;
use ride\library\form\row\StringRow;

/**
 * Auto completable string row
 */
class AutoCompleteStringRow extends StringRow implements HtmlRow {

    /**
     * Name of the auto complete URL option
     * @var string
     */
    const OPTION_AUTO_COMPLETE_URL = 'autocomplete.url';

    /**
     * Name of the auto complete URL option
     * @var string
     */
    const OPTION_AUTO_COMPLETE_MINIMUM = 'autocomplete.minimum';

    /**
     * Name of the auto complete type option
     * @var string
     */
    const OPTION_AUTO_COMPLETE_TYPE = 'autocomplete.type';

    /**
     * URL for auto completion
     * @var string
     */
    protected $autoCompleteUrl;

    /**
     * Minimum number of characters for auto completion
     * @var integer
     */
    protected $autoCompleteMinimum;

    /**
     * Type of auto completion result (json, jsonapi)
     * @var string
     */
    protected $autoCompleteType;

    /**
     * Constructs a new form row
     * @param string $name Name of the row
     * @param array $options Extra options for the row or type implementation
     * @return null
     */
    public function __construct($name, array $options) {
        parent::__construct($name, $options);

        $this->setAutoComplete($this->getOption(self::OPTION_AUTO_COMPLETE_URL), $this->getOption(self::OPTION_AUTO_COMPLETE_MINIMUM, 2), $this->getOption(self::OPTION_AUTO_COMPLETE_TYPE, 'json'));
    }

    /**
     * Sets a URL for auto completion
     * @param string $url URL to fetch the results from. Use %term% placeholder
     * to reserve a place for the term filter
     * @param integer $minimum Minimum number of characters before perform auto
     * completion
     * @return null
     */
    public function setAutoComplete($url, $minimum = 2, $type = 'json') {
        $this->autoCompleteUrl = $url;
        $this->autoCompleteMinimum = $minimum;
        $this->autoCompleteType = $type;
    }

    /**
     * Creates the widget for this row
     * @param string $name
     * @param mixed $default
     * @param array $attributes
     * @return \ride\library\form\widget\Widget
     */
    protected function createWidget($name, $default, array $attributes) {
        if ($this->autoCompleteUrl) {
            $attributes['data-autocomplete-url'] = $this->autoCompleteUrl;
            $attributes['data-autocomplete-minimum'] = $this->autoCompleteMinimum;
            $attributes['data-autocomplete-type'] = $this->autoCompleteType;
        }

        return parent::createWidget($name, $default, $attributes);
    }

}

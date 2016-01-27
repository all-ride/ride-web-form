<?php

namespace ride\web\form\row;

use ride\library\form\row\StringRow;

/**
 * Auto completable string row
 */
class AutoCompleteStringRow extends StringRow {

    /**
     * Name of the auto complete URL option
     * @var string
     */
    const OPTION_AUTO_COMPLETE_URL = 'autocomplete.url';

    /**
     * Name of the auto complete minimum characters option
     * @var string
     */
    const OPTION_AUTO_COMPLETE_MINIMUM = 'autocomplete.minimum';

    /**
     * Name of the auto complete multiple option
     * @var string
     */
    const OPTION_AUTO_COMPLETE_MULTIPLE = 'autocomplete.multiple';

    /**
     * Name of the auto complete type option
     * @var string
     */
    const OPTION_AUTO_COMPLETE_TYPE = 'autocomplete.type';

    /**
     * Name of the auto complete locale option
     * @var string
     */
    const OPTION_LOCALE = 'locale';

    /**
     * Creates the widget for this row
     * @param string $name
     * @param mixed $default
     * @param array $attributes
     * @return \ride\library\form\widget\Widget
     */
    protected function createWidget($name, $default, array $attributes) {
        $url = $this->getOption(self::OPTION_AUTO_COMPLETE_URL);
        if ($url) {
            $attributes['data-autocomplete-url'] = $url;
            $attributes['data-autocomplete-locale'] = strtolower(str_replace('_', '-', $this->getOption(self::OPTION_LOCALE)));
            $attributes['data-autocomplete-multiple'] = $this->getOption(self::OPTION_AUTO_COMPLETE_MULTIPLE, false);
            $attributes['data-autocomplete-minimum'] = $this->getOption(self::OPTION_AUTO_COMPLETE_MINIMUM, 2);
            $attributes['data-autocomplete-type'] = $this->getOption(self::OPTION_AUTO_COMPLETE_TYPE, 'json');
        }

        return parent::createWidget($name, $default, $attributes);
    }

}

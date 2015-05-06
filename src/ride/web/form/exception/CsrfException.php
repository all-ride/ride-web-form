<?php

namespace ride\web\form\exception;

use ride\library\form\exception\FormException;

/**
 * Exception thrown when the submitted CSRF token does not match
 */
class CsrfException extends FormException {

}

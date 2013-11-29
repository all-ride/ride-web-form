<?php

namespace pallo\web\form;

use pallo\library\form\exception\FormException;
use pallo\library\form\AbstractComponentForm;
use pallo\library\http\Request;

/**
 * Web implementation of a form
 */
class WebForm extends AbstractComponentForm {

    /**
     * Sets the request for the form
     * @var pallo\library\http\Request
     */
    protected $request;

    /**
     * Sets the method of the request
     * @var string
     */
    protected $method;

    /**
     * Sets the request
     * @param pallo\library\http\Request $request
     * @param string $method
     * @return null
     */
    public function setRequest(Request $request, $method = null) {
        $this->request = $request;
        $this->method = $method;
    }

    /**
     * Gets the request
     * @return pallo\library\http\Request
     */
    public function getRequest() {
        return $this->request;
    }

    /**
     * Gets the method of the request
     * @return string
     */
    public function getMethod() {
        if ($this->method === null) {
            return Request::METHOD_POST;
        }

        return $this->method;
    }

    /**
     * Performs the build tasks
     * @return pallo\library\form\Form
     */
    public function build() {
        if (!$this->request) {
            throw new FormException('Could not build the form: no request set, use setRequest() first');
        }

        parent::build();

        $data = null;
        if ($this->request->getMethod() == $this->getMethod()) {
            if ($this->request->isGet()) {
                $data = $this->request->getQueryParameters();
            } else {
                $data = $this->request->getBodyParameters();
            }

            if ($_FILES) {
                $data = $this->mergeFiles($data, $_FILES);
            }

            $this->isSubmitted = true;
            $this->dataNeedsProcessing = true;
        } else {
            $this->isSubmitted = false;
        }

        $this->buildRows($data);

        return $this;
    }

    /**
     * Merge the files array with the data
     * @param array $data Submitted form data
     * @param array $files File upload definitions
     * @return array Provided data with the file uploads merged into
     */
    protected function mergeFiles(array $data, array $files) {
        if (!$files) {
            return $data;
        }

        foreach ($files as $name => $value) {
            if ($this->isFileArray($value)) {
                $data[$name] = $value;
            } else {
                if (!isset($data[$name])) {
                    $data[$name] = array();
                }

                foreach ($value as $fileAttr => $valueValues) {
                    foreach ($valueValues as $index => $fieldValues) {
                        foreach ($fieldValues as $fieldName => $fieldValue) {
                            if ($files[$name]['error'][$index][$fieldName] == UPLOAD_ERR_NO_FILE) {
                                continue;
                            }

                            if (isset($data[$name][$index][$fieldName]) && is_string($data[$name][$index][$fieldName])) {
                                $data[$name][$index][$fieldName] = array(
                                    'name' => $data[$name][$index][$fieldName],
                                );
                            }

                            $data[$name][$index][$fieldName][$fileAttr] = $fieldValue;
                        }
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Checks if the provided array is a file upload array
     * @param array $data
     * @return boolean
     */
    protected function isFileArray($data) {
        return is_array($data) && isset($data['name']) && !is_array($data['name']) && isset($data['type']) && !is_array($data['type']) && isset($data['tmp_name']) && !is_array($data['type']);
    }

}
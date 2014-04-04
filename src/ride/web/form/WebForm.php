<?php

namespace ride\web\form;

use ride\library\form\exception\FormException;
use ride\library\form\AbstractComponentForm;
use ride\library\http\Request;
use ride\library\mvc\view\HtmlView;
use ride\library\mvc\view\View;

/**
 * Web implementation of a form
 */
class WebForm extends AbstractComponentForm {

    /**
     * Sets the request for the form
     * @var \ride\library\http\Request
     */
    protected $request;

    /**
     * Sets the method of the request
     * @var string
     */
    protected $method;

    /**
     * Sets the request
     * @param \ride\library\http\Request $request
     * @param string $method
     * @return null
     */
    public function setRequest(Request $request, $method = null) {
        $this->request = $request;
        $this->method = $method;
    }

    /**
     * Gets the request
     * @return \ride\library\http\Request
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
     * @return \ride\library\form\Form
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
        }

        $this->buildRows($data);

        return $this;
    }

    /**
     * Processes the assets of the form into the HTML view
     * @param \ride\library\mvc\view\View $view View on which the form is
     * displayed
     * @return null
     */
    public function processView(View $view) {
        if (!$this->rows || !$view instanceof HtmlView) {
            return;
        }

        foreach ($this->rows as $row) {
            $javascripts = $row->getJavascripts();
            if ($javascripts) {
                foreach ($javascripts as $javascript) {
                    $view->addJavascript($javascript);
                }
            }

            $inlineJavascripts = $row->getInlineJavascripts();
            if ($inlineJavascripts) {
                foreach ($inlineJavascripts as $inlineJavascript) {
                    $view->addInlineJavascript($inlineJavascript);
                }
            }

            $styles = $row->getStyles();
            if ($styles) {
                foreach ($styles as $style) {
                    $view->addStyle($style);
                }
            }
        }
    }

}

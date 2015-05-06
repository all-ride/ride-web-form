<?php

namespace ride\web\form\component;

use ride\library\form\component\AbstractHtmlComponent;
use ride\library\form\FormBuilder;
use ride\library\http\Request;
use ride\library\StringHelper;

use ride\web\form\exception\CsrfException;

/**
 * Component to add a session based CSRF token to your form
 */
class CsrfComponent extends AbstractHtmlComponent {

    /**
     * Name for the session variable of the CSRF token
     * @var string
     */
    const SESSION_CSRF = 'csrf';

    /**
     * Instance of the request
     * @var \ride\library\http\Request
     */
    protected $request;

    /**
     * Creates a new csrf component
     * @param \ride\library\http\request\Request
     * @return null
     */
    public function __construct(Request $request) {
        $this->request = $request;
    }

    /**
     * Gets the name of this component, used when this component is the root
     * of the form to be build
     * @return string
     */
    public function getName() {
        return 'csrf';
    }

    /**
     * Parse the data to form values for the component rows
     * @param mixed $data
     * @return array $data
     */
    public function parseSetData($data) {
        return array();
    }

    /**
     * Parse the form values to data of the component
     * @param array $data
     * @return mixed $data
    */
    public function parseGetData(array $data) {
        if (!isset($data['csrf-token']) || $data['csrf-token'] != $this->getCsrfToken()) {
            throw new CsrfException('Invalid CSRF token received');
        }

        return array();
    }

    /**
     * Prepares the form by adding field definitions
     * @param \ride\library\form\FormBuilder $builder
     * @param array $options
     * @return null
     */
    public function prepareForm(FormBuilder $builder, array $options) {
        $builder->addRow('csrf-token', 'hidden', array(
            'default' => $this->getCsrfToken(),
        ));
    }

    /**
     * Gets the CSRF token from the session or generate one if not set
     * @return string
     */
    protected function getCsrfToken() {
        $session = $this->request->getSession();

        $token = $session->get(self::SESSION_CSRF);
        if (!$token) {
            $token = StringHelper::generate(20);

            $session->set(self::SESSION_CSRF, $token);
        }

        return $token;
    }

}

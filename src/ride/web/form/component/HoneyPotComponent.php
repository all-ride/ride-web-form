<?php

namespace ride\web\form\component;

use ride\library\encryption\cipher\Cipher;
use ride\library\form\component\AbstractHtmlComponent;
use ride\library\form\FormBuilder;
use ride\library\StringHelper;

use ride\web\form\exception\HoneyPotException;

/**
 * Component to add a anti spam honeypot on your form
 */
class HoneyPotComponent extends AbstractHtmlComponent {

    /**
     * Instance of the cipher to encrypt the honeypot data
     * @var \ride\library\encryption\cipher\Cipher
     */
    protected $cipher;

    /**
     * Secret key to encrypt and decrypt data
     * @var string
     */
    protected $secretKey;

    /**
     * Flag to see if the honey pot has been processed
     * @var boolean
     */
    protected $isProcessed;

    /**
     * Sets the system to encrypt values
     * @param \ride\library\encryption\cipher\Cipher $cipher
     * @param string $secretKey
     * @return null
     */
    public function setCipher(Cipher $cipher, $secretKey) {
        $this->cipher = $cipher;
        $this->secretKey = $secretKey;
    }

    /**
     * Gets the name of this component, used when this component is the root
     * of the form to be build
     * @return string
     */
    public function getName() {
        return 'honeypot';
    }

    /**
     * Parse the form values to data of the component
     * @param array $data
     * @return mixed $data
    */
    public function parseGetData(array $data) {
        if ($this->isProcessed) {
            return array();
        }

        $this->isProcessed = true;

        if (!isset($data['honeypot-data']) || !isset($data['honeypot-submit'])) {
            throw new HoneyPotException('no honeypot data received');
        }

        $fieldsString = $this->cipher->decrypt($data['honeypot-data'], $this->secretKey);
        $fields = explode(',', $fieldsString);
        $data = explode(',', $data['honeypot-submit']);
        $dataIndex = 0;

        foreach ($fields as $field) {
            if (strpos($field, ':') !== false) {
                list($field, $default) = explode(':', $field);

                if (!isset($data[$dataIndex]) || $data[$dataIndex] != $default) {
                    throw new HoneyPotException('recieved unexpected data for ' . $field . ': expected ' . $default . ', got ' . (isset($data[$dataIndex]) && $data[$dataIndex] ? $data[$dataIndex] : 'no value'));
                }
            } else {
                if (!isset($data[$dataIndex]) || $data[$dataIndex] !== '') {
                    throw new HoneyPotException('recieved unexpected data for ' . $field . ': expected no value, got ' . (isset($data[$dataIndex]) && $data[$dataIndex] ? $data[$dataIndex] : 'no value'));
                }
            }

            $dataIndex++;
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
        $numFields = rand(3, 9);
        $this->fields = array();

        for ($i = 1; $i <= $numFields; $i++) {
            $fieldMethod = rand(0, 2);

            switch ($fieldMethod) {
                case 0:
                    $this->fields[] = $this->addEmptyField($builder);

                    break;
                case 1:
                    $this->fields[] = $this->addDefaultField($builder);

                    break;
                case 2:
                    $this->fields[] = $this->addJavascriptField($builder);

                    break;
            }
        }

        $builder->addRow('honeypot-data', 'hidden', array(
            'default' => $this->cipher->encrypt(implode(',', $this->fields), $this->secretKey),
        ));
        $builder->addRow('honeypot-submit', 'hidden');
    }

    /**
     * Adds a field which needs to stay empty
     * @param \ride\library\form\FormBuilder $builder
     * @return null
     */
    protected function addEmptyField(FormBuilder $builder) {
        $fieldName = StringHelper::generate();

        $builder->addRow($fieldName, 'string', array(
            'attributes' => array(
                'autocomplete' => 'off',
            ),
        ));

        return $fieldName;
    }

    /**
     * Adds a field which has a default value preset
     * @param \ride\library\form\FormBuilder $builder
     * @return null
     */
    protected function addDefaultField(FormBuilder $builder) {
        $fieldName = StringHelper::generate();
        $default = StringHelper::generate();

        $builder->addRow($fieldName, 'string', array(
            'attributes' => array(
                'autocomplete' => 'off',
            ),
            'default' => $default,
        ));

        return $fieldName . ':' . $default;
    }

    /**
     * Adds a field which has a default value set by javascript
     * @param \ride\library\form\FormBuilder $builder
     * @return null
     */
    protected function addJavascriptField(FormBuilder $builder) {
        $fieldName = StringHelper::generate();
        $default = StringHelper::generate();

        $builder->addRow($fieldName, 'string', array(
            'attributes' => array(
                'autocomplete' => 'off',
                'data-value' => $default,
            ),
        ));

        return $fieldName . ':' . $default;
    }

    /**
     * Gets all the javascript files which are needed for this component
     * @return array
     */
    public function getJavascripts() {
        return array('js/honeypot.js');
    }

    /**
     * Gets all the inline javascript  which are needed for this component
     * @return array
     */
    public function getInlineJavascripts() {
        $fields = array();
        foreach ($this->fields as $field) {
            if (strpos($field, ':') !== false) {
                list($field, $default) = explode(':', $field);
            }

            $fields[] = $field;
        }

        $options = array('fields' => $fields);
        $options = json_encode($options);

        return array("$('form[role=form]').honeyPot(" . $options . ");");
    }

}

<?php

namespace ride\web\form\component;

use ride\library\encryption\cipher\Cipher;
use ride\library\form\component\AbstractHtmlComponent;
use ride\library\form\row\AbstractRow;
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
     * Honeypot data to submit along the form data
     * @var string
     */
    protected $honeyData;

    /**
     * Instance of the honeypot data row
     * @var \ride\library\form\row\Row
     */
    protected $rowData;

    /**
     * Rows with a default value
     * @var array
     */
    protected $rowsDefault;

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

        $this->rowData = null;
        $this->rowsDefault = array();
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
     * Resets the honeypot after a validation error
     * @return null
     */
    public function resetHoneyPot() {
        $this->rowData->setData($this->rowData->getOption('default'));

        foreach ($this->rowsDefault as $row) {
            $row->setData($row->getOption(AbstractRow::OPTION_DEFAULT));
        }
    }

    /**
     * Parse the form values to data of the component
     * @param array $data
     * @return mixed $data
    */
    public function parseGetData(array $data) {
        // reset form for new submission
        if ($this->rowData) {
            $this->rowData->setData($this->honeyData);
        }
        foreach ($this->rowsDefault as $row) {
            $row->setData($row->getOption(AbstractRow::OPTION_DEFAULT));
        }

        // process the honeypot only once
        if ($this->isProcessed) {
            return array();
        }

        $this->isProcessed = true;

        // check submitted honeypot
        if (!isset($data['honeypot-data']) || !isset($data['honeypot-submit'])) {
            throw new HoneyPotException('No honeypot data received');
        }

        $fieldsString = $this->cipher->decrypt($data['honeypot-data'], $this->secretKey);
        if ($fieldsString === $data['honeypot-submit']) {
            return array();
        }

        throw new HoneyPotException('Recieved unexpected data');
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

        $this->honeyData = $this->cipher->encrypt(implode(',', $this->fields), $this->secretKey);
        $this->fingerprint = StringHelper::generate();

        $this->rowData = $builder->addRow('honeypot-data', 'hidden', array(
            'default' => $this->honeyData,
        ));
        $builder->addRow('honeypot-submit', 'hidden', array(
            'attributes' => array(
                'class' => 'honeypot-' . $this->fingerprint,
            ),
        ));
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

        $this->rowsDefault[] = $builder->addRow($fieldName, 'string', array(
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

        $options = array(
            'fields' => $fields,
            'submit' => '.honeypot-' . $this->fingerprint,
        );
        $options = json_encode($options);

        return array("$('.honeypot-" . $this->fingerprint . "').closest('form[role=form]').honeyPot(" . $options . ");");
    }

}

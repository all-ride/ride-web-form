You can use the builtin honeypot to protect your forms against spam.

It's designed as a form component which adds a random number of fields to your form.
These random fields require a specific input each.
Failing to submit the specific input will result in a exception and thus an error for the frontend user.

## Code Sample

    <?php

    use ride\web\base\controller\AbstractController;
    use ride\web\form\component\HoneyPotComponent;
    use ride\web\form\exception\HoneyPotException;

    class FooController extends AbstractController {

        public function indexAction(HoneyPotComponent $honeyPotComponent) {
            // retrieve the honeypot through dependency injection,
            // in this case through the method signature

            // create a form and add your rows
            $form = $this->createFormBuilder();
            $form->addRow('name', 'string', array(
                'filters' => array(
                    'trim' => array(),
                ),
                'validators' => array(
                    'required' => array(),
                ),
            ));

            // add the honeypot component as a "regular" row somewhere in your form
            $form->addRow('phone', 'component', array(
                'component' => $honeyPotComponent,
                'embed' => true,
            ));

            // build and process your form
            $form = $form->build();
            if ($form->isSubmitted()) {
                try {
                    $form->validate();

                    $data = $form->getData();

                    // data processing

                    return;
                } catch (HoneyPotException $exception) {
                    // catch the honey pot exception to react
                    $this->addError('error.honeypot');
                }
            }

            $view = $this->setTemplateView('my-template', array(
                'form' => $form->getView(),
            ));

            // don't forget to process the view, this will add the needed javascript
            $form->processView($view);
        }

    }

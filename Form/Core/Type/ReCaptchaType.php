<?php

namespace SymfonyHackers\Bundle\FormBundle\Form\Core\Type;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Exception\FormException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReCaptchaType extends AbstractType
{
    private $validator;
    private $publicKey;
    private $serverUrl;
    private $options;

    /**
     * @param EventSubscriberInterface $validator
     * @param string                   $pulicKey
     * @param string                   $serverUrl
     * @param array                    $options
     */
    public function __construct(EventSubscriberInterface $validator, $publicKey, $serverUrl, array $options)
    {
        if (empty($publicKey)) {
            throw new FormException('The child node "public_key" at path "sh_form.captcha" must be configured.');
        }

        $this->validator = $validator;
        $this->publicKey = $publicKey;
        $this->serverUrl = $serverUrl;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->validator->addOptions($options['validator']);

        $builder
            ->addEventSubscriber($this->validator)
            ->setAttribute('option_validator', $this->validator->getOptions())
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars = array_replace($view->vars, array(
            'public_key' => $this->publicKey,
            'server' => $this->serverUrl,
            'configs' => $options['configs'],
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $configs = array_merge(array(
            'lang' => \Locale::getDefault(),
            ), $this->options);

        $resolver
            ->setDefaults(array(
                'configs' => array(),
                'validator' => array(),
                'error_bubbling' => false,
            ))
            ->setAllowedTypes('configs', 'array')
            ->setAllowedTypes('validator', 'array')
            ->setNormalizer('configs', function (Options $options, $value) use ($configs) {
                    return array_merge($configs, $value);
                }
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'genemu_recaptcha';
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'genemu_recaptcha';
    }
}

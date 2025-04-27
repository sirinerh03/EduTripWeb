<?php

namespace App\Form;

use App\Service\RecaptchaService;
use App\Validator\Constraints\Recaptcha;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecaptchaType extends AbstractType
{
    private $recaptchaService;

    public function __construct(RecaptchaService $recaptchaService)
    {
        $this->recaptchaService = $recaptchaService;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'mapped' => false,
            'constraints' => [
                new Recaptcha(),
            ],
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['site_key'] = $this->recaptchaService->getSiteKey();
    }

    public function getParent()
    {
        return HiddenType::class;
    }

    public function getBlockPrefix()
    {
        return 'recaptcha';
    }
}

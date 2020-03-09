<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\TimeSlot;
use Carbon\Carbon;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class LoopeatRefillType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('refill', ButtonType::class, [
                'label' => 'form.checkout_address.loopeat_refill.label',
                'attr' => [
                    'data-toggle' => 'modal',
                    'data-target' => '#modal-loopeat',
                    'data-loopeat-iframe-src' => 'https://loopeat-conso-vue.herokuapp.com/loopeats',
                ]
            ])
            ;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (isset($options['alert_message'])) {
            $view->vars['alert_message'] = $options['alert_message'];
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('mapped', false);
        $resolver->setDefault('alert_message', '');
    }
}

<?php

declare(strict_types=1);

namespace App\Presentation\Form;

use App\Core\Client\Enum\Education;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Форма собирает простой массив данных ($options['data_class'] не задан),
 * контроллер сам решает, создать нового Client или обновить существующего —
 * так одна форма переиспользуется и для регистрации, и для редактирования.
 */
final class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, ['label' => 'Имя'])
            ->add('lastName', TextType::class, ['label' => 'Фамилия'])
            ->add('phone', TelType::class, ['label' => 'Номер телефона'])
            ->add('email', TextType::class, ['label' => 'Э-почта'])
            ->add('education', EnumType::class, [
                'label' => 'Образование',
                'class' => Education::class,
                'choice_label' => static fn (Education $education): string => $education->translate(),
                'placeholder' => 'Выберите образование',
            ])
            ->add('personalDataConsent', CheckboxType::class, [
                'label' => 'Я даю согласие на обработку моих личных данных',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('data_class');
    }
}

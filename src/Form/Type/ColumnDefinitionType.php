<?php


namespace App\Form\Type;

use App\Form\ColumnDefinition;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Pavel Dyakonov <wapinet@mail.ru>
 */
class ColumnDefinitionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('label', TextType::class, ['required' => true])
            ->add('propertyName', TextType::class, ['required' => true])
            ->add('propertyType', TextType::class, ['required' => true])
            ->add('sortable', CheckboxType::class, ['required' => true]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ColumnDefinition::class,
            'csrf_protection' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'multiple' => true,
            'allow_extra_fields' => true
        ]);
    }

    public function getName(): string
    {
        return 'column_definition';
    }
}

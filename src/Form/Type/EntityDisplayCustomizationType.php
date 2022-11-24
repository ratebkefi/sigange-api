<?php

namespace App\Form\Type;

use App\Entity\EntityDisplayCustomization;
use App\Entity\UserGroup;
use App\Form\DataTransformer\IRIToUserTransformer;
use App\Form\EventListener\AddCodeEventSubscriber;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\SerializerInterface;

class EntityDisplayCustomizationType extends AbstractType
{

    private $transformer;
    private $serializer;


    public function __construct(IRIToUserTransformer $transformer, SerializerInterface $serializer)
    {
        $this->transformer = $transformer;
        $this->serializer = $serializer;

    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $method = $options['method'];
        $builder->addEventSubscriber(new AddCodeEventSubscriber());

        $builder
            ->add('name', TextType::class, ["required" => true])
            ->add('description', TextType::class, ["required" => false])
            ->add('columns', CollectionType::class, [
                'entry_type' => ColumnDefinitionType::class,
                'allow_add' => true,
            ])
            ->add('entityClassName', TextType::class, ["required" => true, "trim" => true])
            ->add('isDefault', CheckboxType::class, [
                "required" => true
            ])
            // IRI path or code
            ->add('owner', TextType::class,
                ["required" => $method === 'POST'])
            ->add('sharedWith', EntityType::class,
                ["class" => UserGroup::class, "choice_value" => 'code', "required" => false]);

        $builder->get('owner')
            ->addModelTransformer($this->transformer);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class' => EntityDisplayCustomization::class,
            'csrf_protection' => false,
            'allow_extra_fields' => true,
            'allow_add' => true,
            'multiple' => true,
            'allow_delete' => true,
        ));
    }


    public function getName(): string
    {
        return 'entity_display_customization';
    }

}

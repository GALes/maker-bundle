<?= "<?php\n" ?>

namespace <?= $namespace ?>;

<?php if ($bounded_full_class_name): ?>
use <?= $bounded_full_class_name ?>;
<?php endif ?>
<?php foreach ($join_type_use_statements as $className): ?>
use <?= $className ?>;
<?php endforeach; ?>
<?php foreach ($constraint_use_statements as $className): ?>
use <?= $className ?>;
<?php endforeach; ?>
use Symfony\Component\Form\AbstractType;
use Spiriit\Bundle\FormFilterBundle\Filter\FilterOperands;
use Spiriit\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
<?php foreach ($field_type_use_statements as $className): ?>
use <?= $className ?>;
<?php endforeach; ?>

class <?= $class_name ?> extends AbstractType
{
/* TODO: a los campos numericos se les debe agregar 'grouping' => true para evitar que el punto sea tomado como separador decimal */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
<?php foreach ($form_fields as $form_field => $typeOptions): ?>
<?php if ( !( isset($typeOptions['metadata']['id']) && $typeOptions['metadata']['id'] ) && null === $typeOptions['type'] && !$typeOptions['options_code'] ): ?>
            ->add('<?= $form_field ?>')
<?php elseif (null !== $typeOptions['type'] && !$typeOptions['options_code']): ?>
            ->add('<?= $form_field ?>', <?= $typeOptions['type'] ?>::class)
<?php elseif ( !( isset($typeOptions['metadata']['id']) && $typeOptions['metadata']['id'] ) ): ?>
            ->add('<?= $form_field ?>', <?= $typeOptions['type'] ? ($typeOptions['type'].'::class') : 'null' ?>, [
<?= $typeOptions['options_code']."\n" ?>
            ])
<?php endif; ?>
<?php endforeach; ?>
        ;
        $builder->setMethod("GET");
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'allow_extra_fields' => true,
            'csrf_protection' => false,
            'validation_groups' => array('filtering') // avoid NotBlank() constraint-related message
        ]);
    }
}


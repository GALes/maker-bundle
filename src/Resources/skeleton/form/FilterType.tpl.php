<?= "<?php\n" ?>

namespace <?= $namespace ?>;

<?php if ($bounded_full_class_name): ?>
use <?= $bounded_full_class_name ?>;
<?php endif ?>
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Petkopara\MultiSearchBundle\Form\Type\MultiSearchType;

class <?= $class_name ?> extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('search', MultiSearchType::class, [
            'class' => <?= $bounded_class_name ?>::class,
            'search_fields' => [ //optional, if it's empty it will search in the all entity columns
<?php foreach ($form_fields as $form_field => $typeOptions): ?>
<?php if ( !( isset($typeOptions['metadata']['joinColumns']) ) ): ?>
                '<?=$form_field ?>',
<?php endif ?>
<?php endforeach; ?>

            ],
        ]);

        $builder->setMethod('GET');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'allow_extra_fields' => true,
            'validation_groups' => array('filtering') // avoid NotBlank() constraint-related message
            // Configure your form options here
        ]);
    }
}

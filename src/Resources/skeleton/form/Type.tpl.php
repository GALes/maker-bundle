<?= "<?php\n" ?>

namespace <?= $namespace ?>;

<?php if ($bounded_full_class_name): ?>
use <?= $bounded_full_class_name ?>;
<?php endif ?>
//use App\Service\ArchivoAdjuntoService;
use Symfony\Component\Form\AbstractType;
<?php foreach ($field_type_use_statements as $className): ?>
use <?= $className ?>;
<?php endforeach; ?>
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
<?php $usaConstraints = false; foreach ($form_fields as $f) { if (!empty($f['constraints_code'])) { $usaConstraints = true; } } ?>
<?php if ($usaConstraints): ?>
use Symfony\Component\Validator\Constraints as Assert;
<?php endif; ?>
<?php foreach ($constraint_use_statements as $className): ?>
use <?= $className ?>;
<?php endforeach; ?>

class <?= $class_name ?> extends AbstractType
{
    /**
    * @var ArchivoAdjuntoService
    */
//    private $archivoAdjuntoService;

    public function __construct(/*ArchivoAdjuntoService $archivoAdjuntoService*/)
    {
//        $this->archivoAdjuntoService = $archivoAdjuntoService;
    }

<?php   /* TODO: a los campos numericos se les debe agregar 'grouping' => true para evitar que el punto sea tomado como separador decimal */
        /* TODO: a los combos de relaciones inversed (not owner) se los debe definir explicitamente como EntityType y se les debe agregar 'by_reference' => false para forzar la persistencia de la entidad relacionada */ ?>
/* TODO: a los campos numericos se les debe agregar 'grouping' => true para evitar que el punto sea tomado como separador decimal */
/* TODO: a los combos de relaciones inversed (not owner) se los debe definir explicitamente como EntityType y se les debe agregar 'by_reference' => false para forzar la persistencia de la entidad relacionada */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $<?= $entity_var_singular ?> = $builder->getData();
        $builder
<?php foreach ($form_fields as $form_field => $typeOptions): ?>
<?php $hasOptions = !empty($typeOptions['options_code']) || !empty($typeOptions['constraints_code']); ?>
<?php if ( !( isset($typeOptions['metadata']['id']) && $typeOptions['metadata']['id'] ) && null === $typeOptions['type'] && !$hasOptions ): ?>
            ->add('<?= $form_field ?>')
<?php elseif (null !== $typeOptions['type'] && !$hasOptions): ?>
            ->add('<?= $form_field ?>', <?= $typeOptions['type'] ?>::class)
<?php elseif ( !( isset($typeOptions['metadata']['id']) && $typeOptions['metadata']['id'] ) ): ?>
            ->add('<?= $form_field ?>', <?= $typeOptions['type'] ? ($typeOptions['type'].'::class') : 'null' ?>, [
<?php if (!empty($typeOptions['options_code'])): ?>
<?= $typeOptions['options_code']."\n" ?>
<?php endif; ?>
<?php if (!empty($typeOptions['constraints_code'])): ?>
                'constraints' => [
<?php foreach ($typeOptions['constraints_code'] as $constraint): ?>
                    <?= $constraint ?>,
<?php endforeach; ?>
                ],
<?php endif; ?>
            ])
<?php endif; ?>
<?php endforeach; ?>
        ;
//        $this->archivoAdjuntoService->addDeleteAction($builder);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
<?php if ($bounded_full_class_name): ?>
            'data_class' => <?= $bounded_class_name ?>::class,
<?php else: ?>
            // Configure your form options here
<?php endif ?>
//            'validation_groups'     => ['Default', 'group_x'],
        ]);
    }
}

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

<?php   /* TODO: a los campos numericos se les debe agregar 'grouping' => true para evitar que el punto sea tomado como separador decimal */ ?>
/* TODO: a los campos numericos se les debe agregar 'grouping' => true para evitar que el punto sea tomado como separador decimal */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $<?= $entity_var_singular ?> = $builder->getData();
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
//        $this->archivoAdjuntoService->addDeleteAction($builder);
    }

    public function configureOptions(OptionsResolver $resolver)
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

<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use <?= $entity_full_class_name ?>;
use <?= $voter_full_class_name ?>;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class <?= $class_name ?> extends TestCase
{
    /**
     * @param string[] $rolesDelUsuario
     */
    private function votar(string $atributo, array $rolesDelUsuario, mixed $sujeto): int
    {
        $security = $this->createMock(Security::class);
        $security->method('isGranted')
            ->willReturnCallback(fn ($rol) => in_array($rol, $rolesDelUsuario, true));

        $token = $this->createMock(TokenInterface::class);

        return (new <?= $voter_class_name ?>($security))->vote($token, $sujeto, [$atributo]);
    }

    /**
     * Atributo y si se vota con una entidad (true) o sin sujeto (false).
     */
    public static function atributosProvider(): array
    {
        return [
            'acceder'  => [<?= $voter_class_name ?>::ACCEDER, false],
            'crear'    => [<?= $voter_class_name ?>::CREAR, false],
            'ver'      => [<?= $voter_class_name ?>::VER, true],
            'editar'   => [<?= $voter_class_name ?>::EDITAR, true],
            'eliminar' => [<?= $voter_class_name ?>::ELIMINAR, true],
        ];
    }

    public static function atributosPorRolProvider(): array
    {
        $casos = [];
        foreach (self::atributosProvider() as $nombre => [$atributo, $conEntidad]) {
            foreach (<?= $voter_class_name ?>::ROLES as $rol) {
                $casos[$nombre.' con '.$rol] = [$atributo, $conEntidad, $rol];
            }
        }

        return $casos;
    }

    /**
     * @dataProvider atributosPorRolProvider
     */
    public function testCadaRolConAccesoConcede(string $atributo, bool $conEntidad, string $rol): void
    {
        $sujeto = $conEntidad ? new <?= $entity_class_name ?>() : null;

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->votar($atributo, [$rol], $sujeto));
    }

    /**
     * @dataProvider atributosProvider
     */
    public function testUsuarioSinRolesDelCrudDeniega(string $atributo, bool $conEntidad): void
    {
        $sujeto = $conEntidad ? new <?= $entity_class_name ?>() : null;

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->votar($atributo, ['ROLE_SIN_ACCESO_AL_CRUD'], $sujeto));
    }

    /**
     * @dataProvider atributosProvider
     */
    public function testSujetoEquivocadoSeAbstiene(string $atributo, bool $conEntidad): void
    {
        $sujeto = $conEntidad ? null : new <?= $entity_class_name ?>();

        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->votar($atributo, <?= $voter_class_name ?>::ROLES, $sujeto));
    }
}

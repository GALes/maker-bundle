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
    private function votar(string $atributo, array $rolesDelUsuario): int
    {
        $security = $this->createMock(Security::class);
        $security->method('isGranted')
            ->willReturnCallback(fn ($rol) => in_array($rol, $rolesDelUsuario, true));

        $token = $this->createMock(TokenInterface::class);

        return (new <?= $voter_class_name ?>($security))->vote($token, new <?= $entity_class_name ?>(), [$atributo]);
    }

    public static function atributosProvider(): array
    {
        return [
            'ver'      => [<?= $voter_class_name ?>::VER],
            'editar'   => [<?= $voter_class_name ?>::EDITAR],
            'eliminar' => [<?= $voter_class_name ?>::ELIMINAR],
        ];
    }

    /**
     * @dataProvider atributosProvider
     */
    public function testAdminConcedeAcceso(string $atributo): void
    {
        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->votar($atributo, ['ROLE_ADMIN']));
    }

    /**
     * @dataProvider atributosProvider
     */
    public function testUsuarioSinRolAdminDeniegaAcceso(string $atributo): void
    {
        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->votar($atributo, ['ROLE_USER']));
    }
}

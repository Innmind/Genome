<?php
declare(strict_types = 1);

namespace Tests\Innmind\Genome\Gene;

use Innmind\Genome\{
    Gene\Name,
    Exception\DomainException,
};
use PHPUnit\Framework\TestCase;
use Eris\{
    TestTrait,
    Generator,
};

class NameTest extends TestCase
{
    use TestTrait;

    public function testInterface()
    {
        $this
            ->forAll(
                Generator\string(),
                Generator\string()
            )
            ->when(static function(string $vendor, string $project): bool {
                return $vendor !== '' && $project !== '';
            })
            ->then(function(string $vendor, string $project): void {
                $this->assertSame(
                    "$vendor/$project",
                    (new Name("$vendor/$project"))->toString()
                );
            });
    }

    public function testThrowWhenNoMatchingPattern()
    {
        $this
            ->forAll(Generator\string())
            ->when(static function(string $string): bool {
                return !preg_match('~^.+/.+$~', $string);
            })
            ->then(function(string $string): void {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage($string);

                new Name($string);
            });
    }
}

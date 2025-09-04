<?php

namespace SolutionForest\InspireCms\Support\Testing;

use Closure;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Livewire\Features\SupportTesting\Testable;

/**
 * @method HasForms instance()
 *
 * @mixin Testable
 */
class TestsForms
{
    public function dispatchFormFieldEvent(): Closure
    {
        return function (string $event, Closure | array $args = [], ?string $schema = null): static {

            $schema ??= $this->instance()->getDefaultTestingSchemaName();

            /** @phpstan-ignore-next-line */
            $this->assertSchemaExists($schema);

            /** @var Schema $schemaInstance */
            $schemaInstance = $this->instance()->{$schema};

            if ($args instanceof Closure) {
                $args = $args($schemaInstance, $schemaInstance->getRawState());
            }

            $fieldKey = $args[0];
            array_shift($args);

            $this->call('callSchemaComponentMethod', $fieldKey, $event, $args);

            return $this;
        };
    }
}

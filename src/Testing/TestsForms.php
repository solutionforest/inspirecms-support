<?php

namespace SolutionForest\InspireCms\Support\Testing;


use Closure;
use Filament\Forms\ComponentContainer;
use Filament\Forms\Contracts\HasForms;
use Livewire\Features\SupportTesting\Testable;

/**
 * @method HasForms instance()
 * 
 * @mixin Testable
 */
class TestsForms
{
    public function dispatchFormFieldEvent()
    {
        return function (string $event, Closure | array $args = [], string $formName = 'form') {
            /** @phpstan-ignore-next-line  */
            $this->assertFormExists($formName);

            $livewire = $this->instance();

            /** @var ComponentContainer $form */
            $form = $livewire->{$formName};

            if ($args instanceof Closure) {
                $args = $args($form, $form->getRawState());
            }

            $this->call('dispatchFormEvent', $event, ...$args);
            
            return $this;
        };
    }
}

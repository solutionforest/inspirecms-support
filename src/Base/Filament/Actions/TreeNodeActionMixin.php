<?php

namespace SolutionForest\InspireCms\Support\Base\Filament\Actions;

use Illuminate\Support\Js;

/**
 * Mixin class that provides tree node action functionality for Filament components.
 * 
 * This mixin adds tree node-specific action capabilities to Filament actions,
 * enabling hierarchical data operations and tree structure management within
 * the Filament admin panel interface.
 * 
 * @mixin \Filament\Actions\Action
 */
class TreeNodeActionMixin
{
    public function recursiveTreeNodeAlpineId()
    {
        return function ($alpineNodeIdVariable) {
            
            if ($alpineNodeIdVariable && filled($alpineNodeIdVariable) && is_string($alpineNodeIdVariable)) {

                if ($this->isLivewireClickHandlerEnabled()) {

                    return $this
                        ->mergeArguments([
                            'treeNodeIdVariable' => $alpineNodeIdVariable,
                        ])
                        ->alpineClickHandler(function () use ($alpineNodeIdVariable) {
                    
                            $jsClickHandler = $this->getJsClickHandler();

                            if (
                                ($jsClickHandler = $this->getJsClickHandler()) 
                                && is_string($jsClickHandler)
                            ) {

                                $jsClickHandler = str_replace("mountAction('{$this->getName()}',", "mountRecursiveTreeNodeAction('{$this->getName()}', {$alpineNodeIdVariable}, ", $jsClickHandler);

                                return "\$wire.{$jsClickHandler}";
                            }

                            return null;
                        });
                }
                
            }

            return $this;
        };
    }
}

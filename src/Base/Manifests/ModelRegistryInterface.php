<?php

namespace SolutionForest\InspireCms\Support\Base\Manifests;

interface ModelRegistryInterface
{
    /**
     * Retrieve an instance of the specified interface class.
     *
     * @param  string  $interfaceClass  The fully qualified name of the interface class to retrieve.
     * @return ?string The instance of the specified interface class.
     */
    public function get(string $interfaceClass);

    /**
     * Replaces the existing model class associated with the given interface class.
     *
     * @param  string  $interfaceClass  The fully qualified name of the interface class.
     * @param  string  $modelClass  The fully qualified name of the new model class to associate with the interface.
     * @return void
     */
    public function replace(string $interfaceClass, string $modelClass);

    public function setTablePrefix(string $tablePrefix): void;

    public function getTablePrefix(): string;
}

<?php

namespace SolutionForest\InspireCms\Support\Base\Models\Interfaces;

/**
 * @see \SolutionForest\InspireCms\Support\Models\Concerns\HasRecursiveRelationships
 */
interface HasRecursiveRelationshipsInterface
{
    /**
     * Get the model's ancestors.
     *
     * @return \Staudenmeir\LaravelAdjacencyList\Eloquent\Relations\Ancestors<static>
     */
    public function ancestors();

    /**
     * Get the model's ancestors and itself.
     *
     * @return \Staudenmeir\LaravelAdjacencyList\Eloquent\Relations\Ancestors<static>
     */
    public function ancestorsAndSelf();

    /**
     * Get the model's bloodline.
     *
     * @return \Staudenmeir\LaravelAdjacencyList\Eloquent\Relations\Bloodline<static>
     */
    public function bloodline();

    /**
     * Get the model's children.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<static>
     */
    public function children();

    /**
     * Get the model's children and itself.
     *
     * @return \Staudenmeir\LaravelAdjacencyList\Eloquent\Relations\Descendants<static>
     */
    public function childrenAndSelf();

    /**
     * Get the model's descendants.
     *
     * @return \Staudenmeir\LaravelAdjacencyList\Eloquent\Relations\Descendants<static>
     */
    public function descendants();

    /**
     * Get the model's descendants and itself.
     *
     * @return \Staudenmeir\LaravelAdjacencyList\Eloquent\Relations\Descendants<static>
     */
    public function descendantsAndSelf();

    /**
     * Get the model's parent.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<static, static>
     */
    public function parent();

    /**
     * Get the model's parent and itself.
     *
     * @return \Staudenmeir\LaravelAdjacencyList\Eloquent\Relations\Ancestors<static>
     */
    public function parentAndSelf();

    /**
     * Get the ID of the parent entity.
     *
     * @return int|string|null The ID of the parent entity.
     */
    public function getParentId();

    /**
     * Retrieve the ID of the root level parent.
     *
     * @return int|string|null The ID of the root level parent.
     */
    public function getRootLevelParentId();

    /**
     * Get the name of the parent key column.
     *
     * @return string
     */
    public function getParentKeyName();

    /**
     * Set the current instance as the root node.
     *
     * @param  bool  $save  Indicates whether to save the instance after setting it as root. Default is true.
     */
    public function asRoot($save = true);

    /**
     * Sets the parent node for the current node.
     *
     * @param  \Illuminate\Database\Eloquent\Model|string|int|null  $parent  The parent node to set.
     * @param  bool  $save  Whether to save the changes immediately. Default is true.
     */
    public function setParentNode($parent, $save = true);
}

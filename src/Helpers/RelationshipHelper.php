<?php

namespace SolutionForest\InspireCms\Support\Helpers;

use Illuminate\Database\Eloquent\Builder;

class RelationshipHelper
{
    public static function joinRelationshipAs(Builder &$query, string $relationName, string $as, string $joinType = 'leftJoin'): Builder
    {
        $useAlias = true;

        if (! static::isJoinedRelationshipAs($query, $as)) {
            $query
                ->joinRelationship(
                    $relationName,
                    callback: fn ($join) => $join->as($as),
                    joinType: $joinType,
                    useAlias: $useAlias
                );
        }

        return $query;
    }

    public static function isJoinedRelationshipAs(Builder $query, string $aliasOrTable): bool
    {
        $joins = $query->getQuery()->joins;

        if ($joins == null) {
            return false;
        }

        foreach ($joins as $join) {
            if (isset($join->alias) && $join->alias != null && $join->alias == $aliasOrTable) {
                return true;
            }
            if ($join->table == $aliasOrTable) {
                return true;
            }
        }

        return false;
    }
}

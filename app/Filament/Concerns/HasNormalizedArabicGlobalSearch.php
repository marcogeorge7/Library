<?php

namespace App\Filament\Concerns;

use App\Support\ArabicSearch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Arr;

/**
 * Overrides Filament's global-search LIKE-clause builders (see
 * vendor/filament/filament/src/Resources/Resource.php) to match across common
 * Arabic letter variants (ا/أ/إ/آ, ي/ى, ه/ة) instead of exact substrings --
 * otherwise a search for "احمد" won't find a record stored as "أحمد". This
 * mirrors Filament's own multi-word/AND/OR/dotted-relation logic verbatim,
 * only swapping in normalized column expressions and a normalized search term.
 */
trait HasNormalizedArabicGlobalSearch
{
    protected static function applyGlobalSearchAttributeConstraints(Builder $query, string $search): void
    {
        $search = ArabicSearch::normalizeTerm($search);

        foreach (explode(' ', $search) as $searchWord) {
            $query->where(function (Builder $query) use ($searchWord) {
                $isFirst = true;

                foreach (static::getGloballySearchableAttributes() as $attributes) {
                    static::applyGlobalSearchAttributeConstraint(
                        query: $query,
                        search: $searchWord,
                        searchAttributes: Arr::wrap($attributes),
                        isFirst: $isFirst,
                    );
                }
            });
        }
    }

    /**
     * @param  array<string>  $searchAttributes
     */
    protected static function applyGlobalSearchAttributeConstraint(Builder $query, string $search, array $searchAttributes, bool &$isFirst): Builder
    {
        foreach ($searchAttributes as $searchAttribute) {
            $whereClause = $isFirst ? 'where' : 'orWhere';

            $query->when(
                str($searchAttribute)->contains('.'),
                fn (Builder $query) => $query->{"{$whereClause}Relation"}(
                    (string) str($searchAttribute)->beforeLast('.'),
                    new Expression(ArabicSearch::normalizedColumnExpression((string) str($searchAttribute)->afterLast('.'))),
                    'like',
                    "%{$search}%",
                ),
                fn (Builder $query) => $query->{$whereClause}(
                    new Expression(ArabicSearch::normalizedColumnExpression($searchAttribute)),
                    'like',
                    "%{$search}%",
                ),
            );

            $isFirst = false;
        }

        return $query;
    }
}

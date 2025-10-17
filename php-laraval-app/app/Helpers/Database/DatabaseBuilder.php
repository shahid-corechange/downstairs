<?php

namespace App\Helpers\Database;

class DatabaseBuilder
{
    /**
     * Get the raw SQL query with placeholders and bindings
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     */
    public static function getSql($query): string
    {
        // Get the raw SQL query with placeholders
        $sqlWithPlaceholders = $query->toSql();

        // Get the bindings for the query
        $bindings = $query->getBindings();

        // Replace placeholders with actual values for demonstration purposes
        $filledSql = str_replace('?', "'%s'", $sqlWithPlaceholders);
        $filledSql = vsprintf($filledSql, $bindings);

        return $filledSql;
    }
}

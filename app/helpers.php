<?php

use Illuminate\Support\Facades\DB;

if (! function_exists('getDetailsByUUID')) {
    /**
     * Get details of a record by UUID and table name.
     */
    function getDetailsByUUID(string $uuid, string $table): ?array
    {
        // Ensure table exists
        if (! Schema::hasTable($table)) {
            return null;
        }

        // Fetch the record
        $record = DB::table($table)->where('uuid', $uuid)->first();

        // Return as array or null if not found
        return $record ? (array) $record : null;
    }
}

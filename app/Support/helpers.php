<?php

use Illuminate\Http\Request;

if (! function_exists('perPage')) {
    /** The page size a list request asked for (?per_page=), kept between 1 and 50. Defaults to 15. */
    function perPage(Request $request): int
    {
        return min(max((int) $request->query('per_page', 15), 1), 50);
    }
}

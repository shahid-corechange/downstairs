<?php

namespace App\Http\Controllers;

use DB;

class CheckController extends Controller
{
    /**
     * Check conneciton to old db.
     */
    public function olddb()
    {
        try {
            DB::connection('olddb')->getPdo();

            return redirect('/dashboard')->with('success', __('old db connected'));
        } catch (\Exception $e) {
            return redirect('/dashboard')->with('error', __('old db not connected'));
        }
    }
}

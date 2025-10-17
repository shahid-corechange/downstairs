<?php

namespace App\Http\Controllers\Logs;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Jackiedo\LogReader\LogReader;

class SystemLogsController extends Controller
{
    /**
     * Display the index view.
     */
    public function index(): Response
    {
        return Inertia::render('Log/System/index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id, LogReader $logReader)
    {
        return $logReader->find($id)->delete();
    }
}

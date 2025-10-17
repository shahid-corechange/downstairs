<?php

namespace App\Http\Controllers\Search;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    use ResponseTrait;

    /**
     * Display the index view.
     */
    public function index(): Response
    {
        return Inertia::render('Search/index');
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\ProPeopleSearchService;
use App\Traits\ResponseMapper;
use Illuminate\Http\Request;

class PeopleSearchController extends Controller
{
    use ResponseMapper;

    protected $peopleSearchService;

    public function __construct(ProPeopleSearchService $peopleSearchService)
    {
        $this->peopleSearchService = $peopleSearchService;
    }

    public function index(Request $request)
    {
        $request->validate([
                'first_name' => ['required', 'string'],
                'last_name' => ['required', 'string']]
        );

        $persons = $this->peopleSearchService->getUsers($request->first_name, $request->last_name);
        if (isset($persons['error'])) {
            $this->setResponseData('Not record found', null,404);
            return  $this->sendJsonResponse();
        }
        $personsCollection = collect($persons);
        $this->payload = $this->peopleSearchService->addScores($personsCollection);
        return $this->sendJsonResponse();
    }
}

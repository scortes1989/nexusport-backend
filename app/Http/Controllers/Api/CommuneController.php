<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CommuneController extends Controller
{
    public function index()
    {
        return \App\Http\Resources\CommuneResource::collection(
            \App\Models\Commune::orderBy('name')->get()
        );
    }
}

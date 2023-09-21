<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\District;
use Illuminate\Http\Request;

class DistrictController extends Controller
{
    function getDistricts()
    {
        return District::select(['id', 'district'])->get();
    }
}

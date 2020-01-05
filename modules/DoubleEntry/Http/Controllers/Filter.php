<?php

namespace Modules\DoubleEntry\Http\Controllers;

use App\Http\Controllers\Controller;
use Date;

class Filter extends Controller
{
    public function __construct()
    {
        // Add R permission check
        $this->middleware('permission:read-double-entry-chart-of-accounts')->only(['index', 'show', 'edit']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $date = Date::now();
        $date_type = request('date_type');

        switch ($date_type) {
            case 'today':
                $start_date = $end_date = Date::today()->format('Y-m-d');
                break;
            case 'this_week':
                $start_date = $date->startOfWeek()->format('Y-m-d');
                $end_date   = $date->endOfWeek()->format('Y-m-d');
                break;
            case 'this_month':
                $start_date = $date->startOfMonth()->format('Y-m-d');
                $end_date   = $date->endOfMonth()->format('Y-m-d');

                break;
            case 'this_quarter':
                $start_date = $date->startOfMonth()->format('Y-m-d');
                $end_date   = $date->endOfMonth()->format('Y-m-d');

                break;
            case 'this_year':
            default:
                $start_date = $date->startOfYear()->format('Y-m-d');
                $end_date   = $date->endOfYear()->format('Y-m-d');

                break;
        }

        return response()->json([
            'start_date' => $start_date,
            'end_date' => $end_date
        ]);
    }
}

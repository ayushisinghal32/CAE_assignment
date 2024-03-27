<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    public function index(Request $request)
    {
        // Fetch events based on request parameters
        $startDate = $request->input('start_date') ?? Carbon::create(2022, 1, 01)->startOfDay();
        $endDate = $request->input('end_date') ?? Carbon::create(2022, 1, 30)->endOfDay();

        $events = Event::whereBetween('start_time', [$startDate, $endDate])->get();

        return response()->json($events);
    }

    public function flightsNextWeek()
    {
        // Logic to get all flights for the next week
        $startDate = Carbon::create(2022, 1, 14)->startOfWeek()->addDays(7);
        $endDate = Carbon::create(2022, 1, 14)->endOfWeek()->addDays(7);

        $flights = Event::where('type', 'Flight')
            ->whereBetween('start_time', [$startDate, $endDate])
            ->get();
        $response =  response()->json($flights);
        if(!count($flights)){
            $response =  response()->json(["message"=> "No Flight"],400);
        }
        return $response;
    }

    public function standbyNextWeek()
    {
        // Logic to get all standby events for the next week
        $startDate = Carbon::create(2022, 1, 14)->startOfWeek()->addDays(7);
        $endDate = Carbon::create(2022, 1, 14)->endOfWeek()->addDays(7);

        $standbyEvents = Event::where('type', 'Standby')
            ->whereBetween('start_time', [$startDate, $endDate])
            ->get();
        $response = response()->json($standbyEvents);
        if(!count($standbyEvents)){
            $response =  response()->json(["message"=> "No Stand By Flight"],400);
        }
        return $response;
    }

    public function flightsFromLocation(Request $request)
    {
        // Validation rules
        $rules = [
            'location' => 'required|string|max:255|exists:events,location',
        ];

        // Validate the request
        $validator = Validator::make($request->all(), $rules);

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        // Logic to get all flights starting from a given location
        $location = $request->input('location');

        $flights = Event::where('location', $location)
            ->get();

        return response()->json($flights);
    }
}

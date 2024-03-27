<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\Event;

class RosterController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'roster_file' => 'required|mimes:html',
        ]);

        $fileContent = file_get_contents($request->file('roster_file')->getPathname());

        try {
// Parse HTML content to extract relevant data
            $events = $this->parseRosterHtml($fileContent);
// Store events in the database
            $this->storeEvents($events);

            return response()->json(['message' => 'Roster uploaded successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while processing the roster file','e'=>$e], 500);
        }
    }

    private function parseRosterHtml($htmlContent)
    {
        $crawler = new Crawler($htmlContent);

        $events = [];

// Loop through each row in the table
        $crawler->filter('tr')->each(function ($row) use (&$events) {
            $rowData = [];

// Extract data from each cell in the row
            $row->filter('td')->each(function ($cell) use (&$rowData) {
                $rowData[] = $cell->text();
            });

// Push the extracted data into the events array
            $events[] = $rowData;
        });

        return $events;
    }

    private function storeEvents($events)
    {
        DB::beginTransaction();
        $eventDate = '';
        try {
            foreach ($events as $eventData) {
                if(isset($eventData[1])){
                    $startDateTime = explode(' ', $eventData[1]);
                    if(isset($startDateTime[1])) {
                        $eventDate =  $startDateTime[1];
                    }
                }
// Assuming the structure of $eventData is ['Date', 'C/I(L)', 'C/I(Z)', 'C/O(L)', 'C/O(Z)', 'Activity', ...]
                if(!empty($eventDate) && isset($eventData[8]) && isset($eventData[11]) && !empty($eventData[8]) && !empty($eventData[11])) {
                        $event = new Event();
                        $event->start_time = '2022-01-' . $eventDate; // Convert Date to timestamp
                        $event->type = $this->parseActivityType($eventData[8]);
                        $event->flight_number = $this->extractFlightNumber($eventData[8]);
                        $event->location = $eventData[11];// Parse activity type
// Extract other relevant data and assign to event properties
                        $event->save();

                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function parseActivityType($activity)
    {
        // Convert activity string to uppercase for case-insensitive comparison
        $activity = strtoupper($activity);

        // Check for various activity types
        if (strpos($activity, 'UNK') !== false) {
            return 'UN-Known';
        } elseif (strpos($activity, 'OFF') !== false) {
            return 'Day-Off';
        } elseif (strpos($activity, 'SBY') !== false) {
            return 'Standby';
        } elseif (strpos($activity, 'CI') !== false) {
            return 'Check-In';
        } elseif (strpos($activity, 'CO') !== false) {
            return 'Check-Out';
        }elseif (strpos($activity, 'CAR') !== false) {
            return 'CAR';
        } else {
            return 'Flight';
        }
    }

    private function extractFlightNumber($activity)
    {
        // Use regex to extract flight number from the activity string
        preg_match('/[A-Z]{2}\d+/', $activity, $matches);
        return !empty($matches) ? $matches[0] : null;
    }

}

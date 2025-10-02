<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EventController extends Controller
{
    // GET /api/events  (any authed user) with filters + caching
    public function index(Request $request)
    {
        $q        = $request->query('q');
        $from     = $request->query('from');
        $to       = $request->query('to');
        $sort     = $request->query('sort', 'date');
        $dir      = strtolower($request->query('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $perPage  = (int) $request->query('per_page', 10);
        $page     = (int) $request->query('page', 1);

        $query = Event::with('organizer:id,name,email');

        if ($q) {
            $query->where(function($w) use ($q) {
                $w->where('title', 'like', "%{$q}%")
                  ->orWhere('location', 'like', "%{$q}%")
                  ->orWhere('description', 'like', "%{$q}%");
            });
        }

        if ($from) {
            $query->whereDate('date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('date', '<=', $to);
        }

        $allowedSorts = ['date','title','created_at'];
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'date';
        }

        $cacheKey = sprintf(
            'events:%s:%s:%s:%s:%s:pp%s:p%s',
            $q ?? '-', $from ?? '-', $to ?? '-', $sort, $dir, $perPage, $page
        );

        $result = Cache::remember($cacheKey, 60, function() use ($query, $sort, $dir, $perPage) {
            return $query->orderBy($sort, $dir)->paginate($perPage);
        });

        return response()->json($result);
    }

    // GET /api/events/{event}
    public function show(Event $event)
    {
        $event->load('organizer:id,name,email', 'tickets');
        return response()->json($event);
    }

    // POST /api/events  (organizer/admin)
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'date'        => ['required','date'],
            'location'    => ['required','string','max:255'],
        ]);

        $data['created_by'] = $request->user()->id;

        $event = Event::create($data);

        Cache::flush(); // invalidate list cache after create

        return response()->json($event, 201);
    }

    // PUT /api/events/{event} (organizer/admin; organizer only own events)
    public function update(Request $request, Event $event)
    {
        $user = $request->user();
        if ($user->role !== 'admin' && (int) $event->created_by !== (int) $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'title'       => ['sometimes','string','max:255'],
            'description' => ['sometimes','nullable','string'],
            'date'        => ['sometimes','date'],
            'location'    => ['sometimes','string','max:255'],
        ]);

        $event->update($data);

        Cache::flush(); // invalidate list cache after update

        return response()->json($event);
    }

    // DELETE /api/events/{event} (admin only; enforced in routes)
    public function destroy(Event $event)
    {
        $event->delete();

        Cache::flush(); // invalidate list cache after delete

        return response()->json(['message' => 'Event deleted']);
    }
}

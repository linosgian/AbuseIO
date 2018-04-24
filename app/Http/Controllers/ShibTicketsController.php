<?php

namespace AbuseIO\Http\Controllers;

use AbuseIO\Http\Requests\TicketFormRequest;
use AbuseIO\Jobs\EvidenceSave;
use AbuseIO\Jobs\IncidentsProcess;
use AbuseIO\Jobs\Notification;
use AbuseIO\Jobs\TicketUpdate;
use AbuseIO\Models\Event;
use AbuseIO\Models\Evidence;
use AbuseIO\Models\Incident;
use AbuseIO\Models\Ticket;
use DB;
use Input;
use Redirect;
use Illuminate\Http\Request;
use yajra\Datatables\Datatables;

/**
 * Class TicketsController.
 */
class ShibTicketsController extends Controller
{
    /**
     * TicketsController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        // is the logged in account allowed to execute an action on the Domain
        /* $this->middleware('checkaccount:Ticket', ['except' => ['search', 'index', 'create', 'store', 'export']]); */
    }

    /**
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $tickets = Ticket::select(
            'tickets.id',
            'tickets.ip',
            'tickets.domain',
            'tickets.type_id',
            'tickets.class_id',
            'tickets.status_id',
            'tickets.ip_contact_account_id',
            'tickets.ip_contact_reference',
            'tickets.ip_contact_name',
            'tickets.domain_contact_account_id',
            'tickets.domain_contact_reference',
            'tickets.domain_contact_name',
            DB::raw('count(distinct events.id) as event_count'),
            DB::raw('count(distinct notes.id) as notes_count')
        )
            ->leftJoin('events', 'events.ticket_id', '=', 'tickets.id')
            ->leftJoin(
                'notes',
                function ($join) {
                    // We need a LEFT JOIN .. ON .. AND ..).
                    // This doesn't exist within Illuminate's JoinClause class
                    // So we use some nesting foo here
                    $join->on('notes.ticket_id', '=', 'tickets.id')
                        ->nest(
                            function ($join) {
                                $join->on('notes.viewed', '=', DB::raw("'false'"));
                            }
                        );
                }
            )
            ->orderBy('id', 'desc')
            ->groupBy('tickets.id');

            // Filter tickets based on the user's Shibboleth metadata
            // For more info: Check /Http/Middleware/ShibbolethAuth.php
            $tickets = $tickets->where(
                function ($query) use($request){
                    $domain = $request->session()->get('domain');
                    $query->where('tickets.ip_contact_email', 'like', '%' . $domain . '%');
                }
            );

        return Datatables::of($tickets)
            // Create the action buttons
            ->addColumn(
                'actions',
                function ($ticket) {
                    $actions = ' <a href="tickets/'.$ticket->id.
                        '" class="btn btn-xs btn-primary"><span class="glyphicon glyphicon-eye-open"></span> '.
                        trans('misc.button.show').'</a> ';

                    return $actions;
                }
            )
            ->editColumn(
                'type_id',
                function ($ticket) {
                    return trans('types.type.'.$ticket->type_id.'.name');
                }
            )
            ->editColumn(
                'class_id',
                function ($ticket) {
                    return trans('classifications.'.$ticket->class_id.'.name');
                }
            )
            ->editColumn(
                'status_id',
                function ($ticket) {
                    return trans('types.status.abusedesk.'.$ticket->status_id.'.name');
                }
            )
            ->make(true);
    }

    /**
     * Display all tickets.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get translations for all statuses
        $statuses = Event::getStatuses();

        return view('shibtickets.index')
            ->with('types', Event::getTypes())
            ->with('classes', Event::getClassifications())
            ->with('statuses', $statuses['abusedesk'])
            ->with('contact_statuses', $statuses['contact'])
            ->with('user_options', $statuses['contact']);
    }

    /**
     * Export tickets to CSV format.
     *
     * @param string $format
     *
     * @return \Illuminate\Http\Response
     */
    public function export($format, Request $request)
    {
        // TODO #AIO-?? ExportProvider - (mark) Move this into an ExportProvider or something?

        if ($format === 'csv') {
            $columns = [
                'id'            => 'Ticket ID',
                'ip'            => 'IP address',
                'class_id'      => 'Classification',
                'type_id'       => 'Type',
                'first_seen'    => 'First seen',
                'last_seen'     => 'Last seen',
                'event_count'   => 'Events',
                'status_id'     => 'Ticket Status',
            ];

            $output = '"'.implode('", "', $columns).'"'.PHP_EOL;

            // Filter tickets based on the user's Shibboleth metadata
            // For more info: Check /Http/Middleware/ShibbolethAuth.php
            $domain = $request->session()->get('domain');
            $tickets = Ticket::select('tickets.*')
                ->where('tickets.ip_contact_email', 'like', '%' . $domain . '%')
                ->chunk(800, function($tickets) use (&$output){
                    foreach($tickets as $ticket){
                        // TODO: This does not scale.
                        // Either times out, because of the processing, (prolly due to concatenation)
                        // Or runs out of memory.
                        // Change to writing chunks to a csv file
                        $row = [
                            $ticket->id,
                            $ticket->ip,
                            trans("classifications.{$ticket->class_id}.name"),
                            trans("types.type.{$ticket->type_id}.name"),
                            $ticket->firstEvent[0]->seen,
                            $ticket->lastEvent[0]->seen,
                            $ticket->events->count(),
                            trans("types.status.abusedesk.{$ticket->status_id}.name"),
                        ];

                        $output .= '"'.implode('", "', $row).'"'.PHP_EOL;

                    }
                });
            return response(substr($output, 0, -1), 200)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="Tickets.csv"');
        }

        return Redirect::route('shib.tickets.index')
            ->with('message', "The requested format {$format} is not available for exports");
    }

    /**
     * Display the specified ticket.
     *
     * @param Ticket $ticket
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Ticket $ticket, Request $request)
    {
        // Show the ticket only if the user has the right metadata
        $domain = $request->session()->get('domain');
        if (strpos($ticket->ip_contact_email, $domain) !== false){
            return view('shibtickets.show')
                ->with('ticket', $ticket)
                ->with('ticket_class', config("types.status.abusedesk.{$ticket->status_id}.class"))
                ->with('contact_ticket_class', config("types.status.contact.{$ticket->contact_status_id}.class"));
        }
        return Redirect::route('shib.tickets.index')
            ->with('message', "You don't have permission to view this ticket");
    }

    /**
     * Set the status of a tickets.
     *
     * @param Ticket $ticket
     * @param string $newstatus
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function status(Ticket $ticket, $newstatus, Request $request)
    {
        // Show the ticket only if the user has the right metadata
        $domain = $request->session()->get('domain');
        if (strpos($ticket->ip_contact_email, $domain) !== false){
            TicketUpdate::status($ticket, $newstatus);

            return Redirect::route('shib.tickets.show', $ticket->id)
                ->with('message', 'Ticket status has been updated.');
        }
        return Redirect::route('shib.tickets.index')
            ->with('message', "You don't have permission to update this ticket");
    }
}

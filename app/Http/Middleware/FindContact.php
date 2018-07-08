<?php

namespace App\Http\Middleware;

use Closure;

class FindContact
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // get id from url parameter
        $id = $request->route()[2]['id'];

        // find contact
        $contact = \App\Models\Contact::find($id);

        // check if the contact not found
        if (!$contact) {

            // prepare response
            $response = [
              'message' => 'Contact not found',
              'id' => (int) $id,
            ];

            return response()->json($response, 404);
        }

        // add contact to request attributes (pass contact to the controller)
        $request->attributes->add(['contact' => $contact]);

        return $next($request);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Elasticsearch\ClientBuilder;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->elasticsearch = ClientBuilder::create()
          ->setHosts([env('ELASTICSEARCH_HOST')])
          ->build();

        $this->index = Contact::INDEX;
        $this->type = Contact::TYPE;
        $this->searchableFields = Contact::SEARCHABLE_FIELDS;
    }

    public function index(Request $request)
    {
        // get all contacts
        $contacts = Contact::all();

        // prepare response
        $response = [
          'message' => 'Here is your contacts',
          'data' => $contacts,
        ];

        return response()->json($response, 200);
    }

    public function store(Request $request)
    {
        // get all input from request
        $input = $request->all();

        // create contact in database
        $contact = new Contact;
        $contact->owner_id = $input['owner_id']; // get from authenticated user id later ...
        $contact->first_name = $input['first_name'];
        $contact->middle_name = $input['middle_name'];
        $contact->last_name = $input['last_name'];
        $contact->email = $input['email'];
        $contact->phone = $input['phone'];
        $contact->address = $input['address'];
        $contact->save();

        // prepare response
        $response = [
          'message' => 'Successfuly updated the contact',
          'data' => $contact,
        ];

        return response()->json($response, 201);
    }

    public function show(Request $request, $id)
    {
        // prepare response
        $response = [
          'message' => 'Here is your contact',
          'data' => $request->get('contact'),
        ];

        return response()->json($response, 200);
    }

    public function update(Request $request, $id)
    {
        // get all input from request
        $input = $request->all();

        // create contact in database
        $contact = $request->get('contact');
        $contact->owner_id = $input['owner_id']; // get from authenticated user id later ...
        $contact->first_name = $input['first_name'];
        $contact->middle_name = $input['middle_name'];
        $contact->last_name = $input['last_name'];
        $contact->email = $input['email'];
        $contact->phone = $input['phone'];
        $contact->address = $input['address'];
        $contact->save();

        // prepare response
        $response = [
          'message' => 'Successfuly updated the contact',
          'data' => $contact,
        ];

        return response()->json($response, 200);
    }

    public function destroy(Request $request, $id)
    {
        // get contact from id
        $contact = $request->get('contact');

        // delete contact in database
        $contact->delete();

        // prepare response
        $response = [
          'message' => 'Successfuly deleted the contact',
          'id' => (int) $id,
        ];

        return response()->json($response, 200);
    }
}

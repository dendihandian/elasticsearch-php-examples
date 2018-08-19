<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Elasticsearch\ClientBuilder;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth;

class ContactController extends Controller
{
    /**
     * @var Tymon\JWTAuth\JWTAuth
     */
    protected $jwt;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(JWTAuth $jwt)
    {
        $this->elasticsearch = ClientBuilder::create()
          ->setHosts([env('ELASTICSEARCH_HOST')])
          ->build();

        $this->index = Contact::INDEX;
        $this->type = Contact::TYPE;
        $this->searchableFields = Contact::SEARCHABLE_FIELDS;
        $this->jwt = $jwt;
    }

    public function index(Request $request)
    {
        // prepare params for searching a document
        $params = [
          'index' => $this->index,
          'type' => $this->type,
          'body' => '{
            "query": {
              "bool": {
                "must": {
                  "match_all": {}
                },
                "filter": {
                  "term": {
                    "owner_id": ' . $this->jwt->user()->id . '
                  }
                }
              }
            }
          }',
        ];

        // searching a document
        $elasticResponse = $this->elasticsearch->search($params);

        // parse source only
        $contacts = $this->getSources($elasticResponse);

        // prepare response
        $response = [
          'message' => 'Contact List',
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
        $contact->owner_id = $this->jwt->user()->id;
        $contact->first_name = $input['first_name'];
        $contact->middle_name = $input['middle_name'];
        $contact->last_name = $input['last_name'];
        $contact->email = $input['email'];
        $contact->phone = $input['phone'];
        $contact->address = $input['address'];
        $contact->save();

        // prepare params for creating a document
        $params = [
          'index' => $this->index,
          'type' => $this->type,
          'id' => $contact->id,
          'body' => $contact->toArray(),
        ];

        // creating a document
        $elasticResponse = $this->elasticsearch->index($params);

        // prepare response
        $response = [
          'message' => 'Contact Created',
          'data' => $contact,
        ];

        return response()->json($response, 201);
    }

    public function show(Request $request, $id)
    {
        // get the contact
        $contact = $request->get('contact');

        // check if the contact's owner is the current jwt authenticated user
        if ($contact->owner_id !== $this->jwt->user()->id) {
            // prepare response
            $response = [
              'message' => 'Contact Not Found',
              'id' => (int) $id,
            ];

            return response()->json($response, 404);
        }

        // prepare response
        $response = [
          'message' => 'Contact Detail',
          'data' => $contact,
        ];

        return response()->json($response, 200);
    }

    public function update(Request $request, $id)
    {
        // get all input from request
        $input = $request->all();

        // create contact in database
        $contact = $request->get('contact');
        $contact->owner_id = $this->jwt->user()->id;
        $contact->first_name = $input['first_name'];
        $contact->middle_name = $input['middle_name'];
        $contact->last_name = $input['last_name'];
        $contact->email = $input['email'];
        $contact->phone = $input['phone'];
        $contact->address = $input['address'];
        $contact->save();

        // prepare params for updating document
        $params = [
          'index' => $this->index,
          'type' => $this->type,
          'id' => $contact->id,
          'body' => [
            'doc'=> $contact->toArray(),
          ]
        ];

        // update the document
        $elasticResponse = $this->elasticsearch->update($params);

        // prepare response
        $response = [
          'message' => 'Contact Updated',
          'data' => $contact,
        ];

        return response()->json($response, 200);
    }

    public function destroy(Request $request, $id)
    {
        // get contact from id
        $contact = $request->get('contact');

        // prepare params for deleting document
        $params = [
          'index' => $this->index,
          'type' => $this->type,
          'id' => $contact->id,
        ];

        // delete document
        $elasticResponse = $this->elasticsearch->delete($params);

        // delete contact in database
        $contact->delete();

        // prepare response
        $response = [
          'message' => 'Contact Deleted',
          'id' => (int) $id,
        ];

        return response()->json($response, 200);
    }
}

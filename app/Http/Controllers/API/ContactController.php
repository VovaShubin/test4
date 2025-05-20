<?php

    namespace App\Http\Controllers\API;

    use App\Http\Resources\ContactResource;
    use App\Models\Contact;
    use Illuminate\Http\Request;
    use App\Http\Controllers\API\BaseController as BaseController;
    use Illuminate\Support\Facades\Validator;
    use Illuminate\Http\JsonResponse;

    class ContactController extends BaseController
    {
        /**
         * Display a listing of the resource.
         *
         * @return \Illuminate\Http\Response
         */
        public function index(): JsonResponse
        {
            $contacts = Contact::all();

            return $this->sendResponse(ContactResource::collection($contacts), 'Contacts retrieved successfully.');
        }

        /**
         * Store a newly created resource in storage.
         *
         * @param  \Illuminate\Http\Request  $request
         * @return \Illuminate\Http\Response
         */
        public function store(Request $request): JsonResponse
        {
            $input = $request->all();

            $validator = Validator::make($input, [
                'email' => 'required|unique:contacts'
            ]);

            if($validator->fails()){
                return $this->sendError('Validation Error.', $validator->errors());
            }

            $contact = Contact::create($input);

            return $this->sendResponse(new ContactResource($contact), 'Contact created successfully.');
        }

        /**
         * Display the specified resource.
         *
         * @param  int  $id
         * @return \Illuminate\Http\Response
         */
        public function show($id): JsonResponse
        {
            $contact = Contact::find($id);

            if (is_null($contact)) {
                return $this->sendError('Contact not found.');
            }

            return $this->sendResponse(new ContactResource($contact), 'Contact retrieved successfully.');
        }

        /**
         * Update the specified resource in storage.
         *
         * @param  \Illuminate\Http\Request  $request
         * @param  int  $id
         * @return \Illuminate\Http\Response
         */
        public function update(Request $request, Contact $contact): JsonResponse
        {
            $input = $request->all();

            $validator = Validator::make($input, [
                'email' => 'unique:contacts'
            ]);

            if($validator->fails()){
                return $this->sendError('Validation Error.', $validator->errors());
            }

            if (isset($input['name']))       $contact->name = $input['name'];
            if (isset($input['email']))     $contact->email = $input['email'];
            if (isset($input['phone']))     $contact->phone = $input['phone'];
            if (isset($input['tag']))       $contact->tag = $input['tag'];
            if (isset($input['comment']))   $contact->comment = $input['comment'];
            $contact->save();

            return $this->sendResponse(new ContactResource($contact), 'Contact updated successfully.');
        }

        /**
         * Remove the specified resource from storage.
         *
         * @param  int  $id
         * @return \Illuminate\Http\Response
         */
        public function destroy(Contact $contact): JsonResponse
        {
            $contact->delete();

            return $this->sendResponse([], 'Contact deleted successfully.');
        }
    }

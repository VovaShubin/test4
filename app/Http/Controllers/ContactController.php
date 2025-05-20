<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Default sorting column and direction
        $column = $request->input('sort_by', 'tag');
        $dir = $request->input('sort_dir', 'asc');

        // Validate sorting direction
        $dir = in_array(strtolower($dir), ['asc', 'desc']) ? $dir : 'asc';

        // Fetch sorted and paginated users
        $contacts = Contact::orderBy($column, $dir)->paginate(10);

        return view('contact.index', compact('contacts','dir'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('contact.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|unique:contacts',
        ]);

        Contact::create($request->all());

        try {
            $response = Telegram::bot('mybot')->sendMessage([
                'chat_id' => 'CHAT_ID',
                'text' => 'Hello World'
            ]);

            $messageId = $response->getMessageId();
        } catch(\Exception $e)
        {
            //die('Ошибка: ' . $e->getMessage() . PHP_EOL . 'Код ошибки: ' . $e->getCode());
        }

        return redirect()->route('contact.index')->with('success','Contact created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Contact $contact)
    {
        return view('contact.show',compact('contact'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contact $contact)
    {
        return view('contact.edit',compact('contact'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contact $contact)
    {
        $request->validate([
            'email' => 'required|unique:contacts',
        ]);

        $contact->update($request->all());

        return redirect()->route('contact.index')->with('success','Contact updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect()->route('contact.index')->with('success','Contact deleted successfully.');
    }
}

@extends('layout.app')
@section('content')

    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pull-left">
                <h2>Contacts </h2>
            </div>
            <div class="pull-right">
                <a class="btn btn-success" href="{{ route('contact.create') }}"> Create New Contact</a>
            </div>
        </div>
    </div>

    @if ($message = Session::get('success'))
        <div class="alert alert-success">
            <p>{{ $message }}</p>
        </div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>name</th>
                <th>email</th>
                <th>phone</th>
                <th><a href="{{ request()->fullUrlWithQuery(['sort_by' => 'tag', 'sort_dir' => $dir === 'asc' ? 'desc' : 'asc']) }}">tag</a></th>
                <th>comment</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($contacts as $contact)
                <tr>
                    <td>{{ $contact->name }}</td>
                    <td>{{ $contact->email }}</td>
                    <td>{{ $contact->phone }}</td>
                    <td>{{ $contact->tag }}</td>
                    <td>{{ $contact->comment }}</td>
                    <td>
                        <a class="btn btn-info" href="{{ route('contact.show',$contact->id) }}">Show</a>
                        <a class="btn btn-primary" href="{{ route('contact.edit',$contact->id) }}">Edit</a>
                        <form action="{{ route('contact.destroy',$contact->id) }}" method="POST">

                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

@endsection

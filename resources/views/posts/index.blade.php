@extends('layouts.app')

@section('content')

<div class="card shadow">

    <div class="card-header d-flex justify-content-between align-items-center">

        <h5 class="mb-0">
            All Posts
        </h5>


        <a href="{{ route('posts.create') }}"
            class="btn btn-primary">

            + Create New Post

        </a>

    </div>


    <div class="card-body">


        {{-- Search & Filter --}}

        <form method="GET"
            action="{{ route('posts.index') }}"
            class="row g-3 mb-4">


            {{-- Search --}}

            <div class="col-md-4">

                <input type="text"
                    name="search"
                    class="form-control"
                    placeholder="Search title, content, author..."
                    value="{{ request('search') }}">

            </div>



            {{-- Author Filter --}}

            <div class="col-md-3">

                <select name="user"
                    class="form-select">


                    <option value="">
                        All Authors
                    </option>


                    @foreach($users as $user)

                    <option value="{{ $user->id }}"
                        {{ request('user') == $user->id ? 'selected' : '' }}>

                        {{ $user->name }}

                    </option>

                    @endforeach


                </select>

            </div>




            {{-- Status Filter --}}

            <div class="col-md-3">

                <select name="status"
                    class="form-select">


                    <option value="">
                        All Status
                    </option>


                    <option value="1"
                        {{ request('status') == '1' ? 'selected' : '' }}>

                        Published

                    </option>


                    <option value="0"
                        {{ request('status') == '0' ? 'selected' : '' }}>

                        Draft

                    </option>


                </select>

            </div>




            {{-- Buttons --}}

            <div class="col-md-2 d-flex gap-2">


                <button class="btn btn-success">

                    Search

                </button>


                <a href="{{ route('posts.index') }}"
                    class="btn btn-secondary">

                    Reset

                </a>


            </div>


        </form>




        {{-- Table --}}

        <div class="table-responsive">


            <table class="table table-striped table-hover">


                <thead class="table-dark">


                    <tr>

                        <th>
                            ID
                        </th>


                        <th>
                            Title
                        </th>


                         <th>
                            Content
                        </th>

                        <th>
                            Author
                        </th>


                        <th>
                            Status
                        </th>


                        <th width="300">
                            Actions
                        </th>


                    </tr>


                </thead>



                <tbody>


                    @forelse($posts as $post)


                    <tr>


                        <td>

                            {{ $post->id }}

                        </td>



                        <td>

                            {{ $post->title }}

                        </td>

                          <td>

                            {{ $post->content }}

                        </td>



                        <td>

                            {{ $post->user->name ?? 'N/A' }}

                        </td>




                        <td>


                            @if($post->is_published)


                            <span class="badge bg-success">

                                Published

                            </span>


                            @else


                            <span class="badge bg-secondary">

                                Draft

                            </span>


                            @endif



                        </td>




                        <td>


                            <a href="{{ route('posts.show',$post) }}"
                                class="btn btn-sm btn-info">

                                View

                            </a>



                            <a href="{{ route('posts.edit',$post) }}"
                                class="btn btn-sm btn-warning">

                                Edit

                            </a>



                            <a href="{{ route('posts.revisions',$post) }}"
                                class="btn btn-sm btn-dark">

                                Revisions

                            </a>




                            <form action="{{ route('posts.destroy',$post) }}"
                                method="POST"
                                class="d-inline">


                                @csrf

                                @method('DELETE')


                                <button class="btn btn-sm btn-danger"
                                    onclick="return confirm('Delete this post?')">

                                    Delete

                                </button>


                            </form>


                        </td>



                    </tr>


                    @empty


                    <tr>

                        <td colspan="5"
                            class="text-center text-muted">

                            No posts found.

                        </td>

                    </tr>


                    @endforelse



                </tbody>



            </table>


        </div>




        {{-- Pagination --}}

        <div class="mt-3">

            {{ $posts->links('pagination::bootstrap-5') }}

        </div>



    </div>

</div>


@endsection
@extends('layouts.app')

@section('content')

<div class="card shadow">


    <div class="card-header d-flex justify-content-between align-items-center">


        <div>

            <h5 class="mb-0">

                Revision History

            </h5>

            <small class="text-muted">

                Post : {{ $post->title }}

            </small>

        </div>




        <div>


            {{-- Export CSV --}}

            <a href="{{ route('posts.revisions.export',$post) }}"
                class="btn btn-success">

                📥 Export CSV

            </a>




            {{-- Delete All Revision --}}

            <form action="{{ route('posts.revisions.deleteAll',$post) }}"
                method="POST"
                class="d-inline">


                @csrf

                @method('DELETE')


                <button class="btn btn-danger"
                    onclick="return confirm('Delete all revisions?')">

                    🗑 Delete All

                </button>


            </form>




            <a href="{{ route('posts.show',$post) }}"
                class="btn btn-primary">

                Back

            </a>



        </div>


    </div>




    <div class="card-body">


        @if($revisions->count())


        <div class="table-responsive">


            <table class="table table-bordered table-hover">


                <thead class="table-dark">


                    <tr>


                        <th>
                            #
                        </th>


                        <th>
                            Date
                        </th>


                        <th>
                            User
                        </th>


                        <th>
                            Field
                        </th>


                        <th>
                            Old Value
                        </th>


                        <th>
                            New Value
                        </th>


                        <th>
                            Action
                        </th>


                    </tr>


                </thead>



                <tbody>


                    @foreach($revisions as $revision)


                    <tr>


                        <td>

                            {{
                                ($revisions->currentPage()-1)
                                *
                                $revisions->perPage()
                                +
                                $loop->iteration
                            }}

                        </td>




                        <td>

                            {{
                                $revision->created_at
                                ->format('d M Y H:i')
                            }}

                        </td>




                        <td>


                            @php

                            $user = $revision->userResponsible();

                            @endphp



                            @if($user)


                            <span class="badge bg-info">

                                {{ $user->name }}

                            </span>


                            @else


                            <span class="badge bg-secondary">

                                System

                            </span>


                            @endif



                        </td>




                        <td>


                            @if(
                            $revision->key == 'created_at'
                            &&
                            !$revision->old_value
                            )


                            <span class="badge bg-success">

                                CREATED

                            </span>


                            @elseif(
                            $revision->key == 'deleted_at'
                            )


                            <span class="badge bg-danger">

                                DELETED

                            </span>


                            @else


                            {{ $revision->fieldName() }}


                            @endif


                        </td>




                        <td>


                            @if($revision->oldValue())


                            <span class="text-danger">

                                {{ $revision->oldValue() }}

                            </span>


                            @else

                            -

                            @endif


                        </td>




                        <td>


                            <span class="text-success">

                                {{ $revision->newValue() }}

                            </span>


                        </td>




                        <td>


                            @if(
                            $revision->key == 'created_at'
                            &&
                            !$revision->old_value
                            )

                            Post Created


                            @elseif(
                            $revision->key == 'deleted_at'
                            )

                            Post Deleted


                            @else

                            Updated

                            @endif


                        </td>



                    </tr>



                    @endforeach



                </tbody>


            </table>


        </div>




        {{-- Pagination --}}

        <div class="mt-3">


            {{
                $revisions
                ->links('pagination::bootstrap-5')
            }}


        </div>



        @else


        <div class="alert alert-warning text-center">


            No revision history found.


        </div>



        @endif



    </div>


</div>


@endsection
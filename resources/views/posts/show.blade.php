@extends('layouts.app')

@section('content')

<div class="row">


    {{-- Post Details --}}

    <div class="col-md-8">


        <div class="card shadow">


            <div class="card-header d-flex justify-content-between align-items-center">


                <h5 class="mb-0">

                    Post Details

                </h5>



                <div>


                    <a href="{{ route('posts.edit',$post) }}"
                        class="btn btn-warning">

                        Edit

                    </a>


                    <a href="{{ route('posts.revisions',$post) }}"
                        class="btn btn-dark">

                        All Revisions

                    </a>


                </div>


            </div>



            <div class="card-body">


                <h3>

                    {{ $post->title }}

                </h3>



                <p class="text-muted">

                    Author :
                    {{ $post->user->name ?? 'N/A' }}

                </p>



                <hr>



                <p>

                    {{ $post->content }}

                </p>




                <p>


                    Status :


                    @if($post->is_published)


                    <span class="badge bg-success">

                        Published

                    </span>


                    @else


                    <span class="badge bg-secondary">

                        Draft

                    </span>


                    @endif



                </p>




                <p>

                    Created :

                    {{ $post->created_at->format('d M Y H:i') }}

                </p>



                <p>

                    Updated :

                    {{ $post->updated_at->format('d M Y H:i') }}

                </p>



            </div>


        </div>



    </div>





    {{-- Revision Statistics --}}

    <div class="col-md-4">


        <div class="card shadow mb-3">


            <div class="card-header bg-primary text-white">


                Revision Statistics


            </div>



            <div class="card-body">


                <h6>

                    Total Revisions

                </h6>


                <h2 class="text-primary">

                    {{ $revisionCount }}

                </h2>



                <hr>



                <h6>

                    First Revision

                </h6>


                <p>


                    @if($firstRevision)


                    {{
                            $firstRevision
                            ->created_at
                            ->format('d M Y H:i')
                        }}


                    @else


                    No Data


                    @endif


                </p>




                <h6>

                    Last Revision

                </h6>


                <p>


                    @if($lastRevision)


                    {{
                            $lastRevision
                            ->created_at
                            ->format('d M Y H:i')
                        }}


                    @else


                    No Data


                    @endif


                </p>




                <h6>

                    Last Edited By

                </h6>


                <p>


                    @if($lastEditor)


                    <span class="badge bg-success">

                        {{ $lastEditor->name }}

                    </span>


                    @else


                    System


                    @endif


                </p>



            </div>


        </div>



    </div>



</div>





{{-- Recent Revision Activity --}}


<div class="card shadow mt-4">


    <div class="card-header bg-dark text-white">


        Recent Revision Activity


    </div>




    <div class="card-body">


        @if($revisions->count())


        <div class="list-group">


            @foreach($revisions->take(5) as $revision)



            <div class="list-group-item">


                <div class="d-flex justify-content-between">


                    <strong>


                        @php

                        $user =
                        $revision->userResponsible();

                        @endphp



                        {{
                                $user
                                ? $user->name
                                : 'System'
                            }}


                    </strong>



                    <small class="text-muted">


                        {{
                                $revision
                                ->created_at
                                ->format('d M Y H:i')
                            }}


                    </small>


                </div>




                <p class="mt-2 mb-0">


                    @if(
                    $revision->key == 'created_at'
                    &&
                    !$revision->old_value
                    )


                    Created this post


                    @else


                    Changed

                    <strong>

                        {{
                                    $revision->fieldName()
                                }}

                    </strong>


                    from


                    <span class="text-danger">


                        {{
                                    $revision->oldValue()
                                }}


                    </span>


                    to


                    <span class="text-success">


                        {{
                                    $revision->newValue()
                                }}


                    </span>


                    @endif



                </p>



            </div>



            @endforeach



        </div>



        @else



        <p class="text-muted text-center">


            No revision history found.


        </p>



        @endif



    </div>


</div>


@endsection
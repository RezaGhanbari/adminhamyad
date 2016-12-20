@extends('voyager::master')

@section('css')
    <style>
        .panel .mce-panel {
            border-left-color: #fff;
            border-right-color: #fff;
        }

        .panel .mce-toolbar,
        .panel .mce-statusbar {
            padding-left: 20px;
        }

        .panel .mce-edit-area,
        .panel .mce-edit-area iframe,
        .panel .mce-edit-area iframe html {
            padding: 0 10px;
            min-height: 350px;
        }

        .mce-content-body {
            color: #555;
            font-size: 14px;
        }

        .panel.is-fullscreen .mce-statusbar {
            position: absolute;
            bottom: 0;
            width: 100%;
        }

        .panel.is-fullscreen .mce-tinymce {
            height:100%;
        }

        .panel.is-fullscreen .mce-edit-area,
        .panel.is-fullscreen .mce-edit-area iframe,
        .panel.is-fullscreen .mce-edit-area iframe html {
            height: 100%;
            position: absolute;
            width: 95%;
            overflow-y: scroll;
            overflow-x: hidden;
            min-height: 100%;
        }

        .nav .open>a, .nav .open>a:focus, .nav .open>a:hover {

            direction: rtl;
        }
        .nav-tabs>li>a {
            direction: rtl;
        }
        .section:focus {
            text-decoration: none;
        }
        a:focus {
            text-decoration: none;
        }
        .panel-collapse {
            direction: rtl;
        }
    </style>
@stop

@section('page_header')
    <h1 class="page-title">
        <i class="{{ $dataType->icon }}"></i> @if(isset($dataTypeContent->id)){{ 'Edit' }}@else{{ 'New' }}@endif {{ $dataType->display_name_singular }}
    </h1>
@stop

@section('content')
    <div class="page-content container-fluid">
        <form role="form" action="@if(isset($dataTypeContent->id)){{ route('posts.update', $dataTypeContent->id) }}@else{{ route('posts.store') }}@endif" method="POST" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="row">
                <div class="col-md-8">
                    <!-- ### TITLE ### -->
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">
                                <i class="voyager-character"></i> Name
                                <span class="panel-desc"> title for course</span>
                            </h3>
                            <div class="panel-actions">
                                <a class="panel-action icon wb-minus" data-toggle="panel-collapse" aria-hidden="true"></a>
                            </div>
                        </div>
                        <div class="panel-body">
                            <input type="text" class="form-control" name="title" placeholder="Title" value="@if(isset($dataTypeContent->name)){{ $dataTypeContent->name }}@endif">
                        </div>
                    </div>

                    <!-- ### CONTENT ### -->
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="icon wb-book"></i> description</h3>
                            <div class="panel-actions">
                                <a class="panel-action icon wb-expand" data-toggle="panel-fullscreen" aria-hidden="true"></a>
                            </div>
                        </div>
                        <textarea class="richTextBox" name="body" style="border:0px;">
                            @if(isset($dataTypeContent->description)){{ $dataTypeContent->description }}@endif
                        </textarea>
                    </div><!-- .panel -->

                    <!-- ### EXCERPT ### -->
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">Tags</h3>
                            <div class="panel-actions">
                                <a class="panel-action icon wb-minus" data-toggle="panel-collapse" aria-hidden="true"></a>
                            </div>
                        </div>
                        <div>
                            @foreach(\App\Tag::all() as $tag)
                                {{$tag->id}}
                                {{$tag->tag_name}}
                            @endforeach
                        </div>
                        <div class="panel-body" >
                            <input class="form-control" name="excerpt" value="
                              @if (isset($dataTypeContent->tags))
                            @foreach($dataTypeContent->tags as $tag)
                            {{$tag->tag_name}}
                            @endforeach
                            @endif
                                    ">
                            </input>
                            <br>
                        </div>
                    </div>
                    <div>
                        <div class="panel-group" id="accordion">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a data-toggle="collapse" data-parent="#accordion" href="#collapse1">Collapsible Group 1</a>
                                        <span class="caret"></span>
                                    </h4>
                                </div>
                                <div id="collapse1" class="panel-collapse collapse in">
                                    <div class="panel-body"><p>name</p><p>time</p></div>
                                    <div class="panel-footer"><input type="button" class="btn btn-primary" value="edit"> <input type="button" class="btn btn-success" value="browse"> </div>
                                </div>
                            </div>
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a data-toggle="collapse" data-parent="#accordion" href="#collapse2">Collapsible Group 2</a>
                                        <span class="caret"></span>
                                    </h4>
                                </div>
                                <div id="collapse2" class="panel-collapse collapse">
                                    <div class="panel-body"><p>name</p><p>time</p></div>
                                    <div class="panel-footer"><input type="button" class="btn btn-primary" value="edit" > <input type="button" class="btn btn-success" value="browse" > </div>
                                </div>
                            </div>
                        </div>

                        @foreach($dataTypeContent->section as $sec)
                            {{$sec->id}}
                            {{$sec->name}}
                            <br>
                        @endforeach
                        <input type="button" class="btn btn-info pull-right" value="Add">
                    </div>
                </div>
                <div class="col-md-4">
                    <!-- ### DETAILS ### -->
                    <div class="panel panel panel-bordered panel-warning">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="icon wb-clipboard"></i> Post Details</h3>
                            <div class="panel-actions">
                                <a class="panel-action icon wb-minus" data-toggle="panel-collapse" aria-hidden="true"></a>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label for="name">Category</label>
                                <select class="form-control" name="category_id">
                                    @foreach(\App\Category::all() as $category)
                                        <option value="{{ $category->id }}" @if(isset($dataTypeContent->category_id) && $dataTypeContent->category_id == $category->id){{ 'selected="selected"' }}@endif>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- ### IMAGE ### -->
                    <div class="panel panel-bordered panel-primary">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="icon wb-image"></i> Course Image</h3>
                            <div class="panel-actions">
                                <a class="panel-action icon wb-minus" data-toggle="panel-collapse" aria-hidden="true"></a>
                            </div>
                        </div>
                        <div class="panel-body">
                            @if(isset($dataTypeContent->image))
                                <img src="{{ Voyager::image( $dataTypeContent->image ) }}" style="width:100%" />
                            @endif
                            <input type="file" name="image">
                        </div>
                    </div>

                    <!-- ### SEO CONTENT ### -->
                    <div class="panel panel-bordered panel-info">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="icon wb-search"></i> SEO Content</h3>
                            <div class="panel-actions">
                                <a class="panel-action icon wb-minus" data-toggle="panel-collapse" aria-hidden="true"></a>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label for="name">Price</label>
                                <input class="form-control" name="meta_description">
                                @if(isset($dataTypeContent->meta_description)){{ $dataTypeContent->meta_description }}@endif
                                </input>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PUT Method if we are editing -->
            @if(isset($dataTypeContent->id))
                <input type="hidden" name="_method" value="PUT">
        @endif
        <!-- PUT Method if we are editing -->

            <button type="submit" class="btn btn-primary pull-right">
                @if(isset($dataTypeContent->id)){{ 'Update Post' }}@else<?= '<i class="icon wb-plus-circle"></i> Create New Post'; ?>@endif
            </button>
        </form>

        <iframe id="form_target" name="form_target" style="display:none"></iframe>
        <form id="my_form" action="{{ route('voyager.upload') }}" target="form_target" method="post" enctype="multipart/form-data" style="width:0px;height:0;overflow:hidden">
            {{ csrf_field() }}
            <input name="image" id="upload_file" type="file" onchange="$('#my_form').submit();this.value='';">
            <input type="hidden" name="type_slug" id="type_slug" value="{{ $dataType->slug }}">
        </form>
    </div>
@stop

@section('javascript')
    <script src="{{ config('voyager.assets_path') }}/lib/js/tinymce/tinymce.min.js"></script>
    <script src="{{ config('voyager.assets_path') }}/js/voyager_tinymce.js"></script>
@stop

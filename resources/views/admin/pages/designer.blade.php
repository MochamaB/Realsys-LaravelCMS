@extends('admin.layouts.master')

@section('title', 'Page Designer')

@section('css')
<link href="{{ asset('assets/admin/libs/grapesjs/dist/css/grapes.min.css') }}" rel="stylesheet" />
@endsection

@section('js')
<script src="{{ asset('assets/admin/libs/grapesjs/dist/grapes.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/page-designer.js') }}"></script>
<script src="{{ asset('assets/admin/js/previewManager.js') }}"></script>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div id="gjs" data-page-id="{{ $page->id }}" style="height: 80vh; border: 1px solid #eee"></div>
        </div>
    </div>
</div>
@endsection 
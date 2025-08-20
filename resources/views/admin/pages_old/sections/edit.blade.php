@extends('admin.layouts.master')

@section('title', 'Edit Page Section')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Edit Page Section</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.pages.index') }}">Pages</a></li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.pages.sections.index', $page) }}">Sections</a>
                        </li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Section Details</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.pages.sections.update', [$page, $section]) }}" 
                          method="POST" 
                          class="row g-3 needs-validation" 
                          novalidate>
                        @csrf
                        @method('PUT')

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="template_section_id" class="form-label">Template Section</label>
                                <select class="form-select @error('template_section_id') is-invalid @enderror" 
                                        id="template_section_id" 
                                        name="template_section_id" 
                                        required>
                                    <option value="">Select Template Section</option>
                                    @foreach($templateSections as $templateSection)
                                        <option value="{{ $templateSection->id }}" 
                                                {{ old('template_section_id', $section->template_section_id) == $templateSection->id ? 'selected' : '' }}>
                                            {{ $templateSection->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('template_section_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $section->name) }}" 
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="identifier" class="form-label">Identifier</label>
                                <input type="text" 
                                       class="form-control @error('identifier') is-invalid @enderror" 
                                       id="identifier" 
                                       name="identifier" 
                                       value="{{ old('identifier', $section->identifier) }}"
                                       placeholder="Leave empty to auto-generate">
                                @error('identifier')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" 
                                          name="description" 
                                          rows="3">{{ old('description', $section->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-check form-switch form-switch-success">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_active"
                                       name="is_active" 
                                       value="1"
                                       {{ old('is_active', $section->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="text-end">
                                <a href="{{ route('admin.pages.sections.index', $page) }}" 
                                   class="btn btn-light me-2">Cancel</a>
                                <button type="submit" class="btn btn-success">Update Section</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

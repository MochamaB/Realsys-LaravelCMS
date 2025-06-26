@extends('admin.layouts.app')

@section('styles')
<!-- Include CodeMirror for code editing -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/monokai.min.css">
<style>
    .CodeMirror {
        height: 500px;
        border: 1px solid #ddd;
    }
    .nav-tabs .nav-link.active {
        background-color: #f8f9fa;
        border-bottom: none;
    }
    .tab-content {
        border: 1px solid #dee2e6;
        border-top: none;
        padding: 15px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Edit Widget Code - {{ $widget->name }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('admin.themes.widgets.show', ['theme' => $widget->theme, 'widget' => $widget]) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Warning: Editing widget code directly affects how widgets function and appear. Make sure you understand the changes you are making.
                    </div>
                    
                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    
                    <form action="{{ route('admin.widgets.update_code', $widget) }}" method="POST">
                        @csrf
                        
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="json-tab" data-toggle="tab" href="#json" role="tab">widget.json</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="view-tab" data-toggle="tab" href="#view" role="tab">view.blade.php</a>
                            </li>
                        </ul>
                        
                        <div class="tab-content mt-3">
                            <div class="tab-pane fade show active" id="json" role="tabpanel">
                                <textarea id="json_editor" name="json_content">{{ $jsonContent }}</textarea>
                            </div>
                            <div class="tab-pane fade" id="view" role="tabpanel">
                                <textarea id="view_editor" name="view_content">{{ $viewContent }}</textarea>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <a href="{{ route('admin.themes.widgets.show', ['theme' => $widget->theme, 'widget' => $widget]) }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- CodeMirror and plugins -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/javascript/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/htmlmixed/htmlmixed.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/css/css.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/php/php.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/edit/matchbrackets.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/fold/foldcode.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/fold/foldgutter.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/lint/json-lint.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/lint/lint.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize JSON editor
        var jsonEditor = CodeMirror.fromTextArea(document.getElementById('json_editor'), {
            mode: { name: "javascript", json: true },
            lineNumbers: true,
            theme: "monokai",
            autoCloseBrackets: true,
            matchBrackets: true,
            foldGutter: true,
            gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"]
        });
        
        // Initialize Blade editor
        var viewEditor = CodeMirror.fromTextArea(document.getElementById('view_editor'), {
            mode: "php",
            lineNumbers: true,
            theme: "monokai",
            matchBrackets: true,
            foldGutter: true,
            gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"]
        });
        
        // Refresh editors when tab is shown
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            if (e.target.id === 'json-tab') {
                jsonEditor.refresh();
            } else if (e.target.id === 'view-tab') {
                viewEditor.refresh();
            }
        });
        
        // Form submission handling
        document.querySelector('form').addEventListener('submit', function(e) {
            // Update textarea values before form submission
            jsonEditor.save();
            viewEditor.save();
            
            // Basic JSON validation
            try {
                JSON.parse(document.getElementById('json_editor').value);
            } catch (error) {
                e.preventDefault();
                alert('Invalid JSON format: ' + error.message);
            }
        });
    });
</script>
@endsection

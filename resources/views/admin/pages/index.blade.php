@extends('admin.layouts.master')

@section('title', 'Pages')

@section('css')
    <!-- Sweet Alert css-->
    <link href="{{ asset('assets/admin/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
 

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Pages List</h4>
                <div>
                    <a href="{{ route('admin.pages.create') }}" class="btn btn-success add-btn">
                        <i class="ri-add-line align-bottom me-1"></i> Create Page
                    </a>
                </div>
            </div>
                <div class="card-body">
                    <div class="table-responsive table-card">
                        <table class="table table-nowrap align-middle">
                            <thead class="text-muted table-light">
                                <tr>
                                    <th scope="col">Title</th>
                                    <th scope="col">Slug</th>
                                    <th scope="col">Template</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Created</th>
                                    <th scope="col" style="width: 150px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pages as $page)
                                    <tr class="clickable-row" 
                                        data-href="{{ route('admin.pages.show', $page->id) }}"
                                        style="cursor: pointer;">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1">{{ $page->title }}</div>
                                            </div>
                                        </td>
                                        <td>{{ $page->slug }}</td>
                                        <td>{{ $page->template->name ?? 'No Template' }}</td>
                                        <td>
                                            {{ $page->status}}
                                           
                                        </td>
                                        <td>{{ $page->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <div class="design">
                                                    <button class="btn btn-sm btn-primary design-page-btn" 
                                                            data-page-id="{{ $page->id }}" 
                                                            data-page-title="{{ $page->title }}"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#designerSelectionModal">
                                                        <i class="ri-brush-line align-bottom"></i>
                                                    </button>
                                                </div>
                                                <div class="edit">
                                                    <a href="{{ route('admin.pages.edit', $page->id) }}" class="btn btn-sm btn-soft-success">
                                                        <i class="ri-pencil-fill align-bottom"></i>
                                                    </a>
                                                </div>
                                                <div class="remove">
                                                    <button class="btn btn-sm btn-soft-danger remove-item-btn" data-page-id="{{ $page->id }}">
                                                        <i class="ri-delete-bin-fill align-bottom"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No pages found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        {{ $pages->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Designer Selection Modal -->
<div class="modal fade" id="designerSelectionModal" tabindex="-1" aria-labelledby="designerSelectionModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="designerSelectionModalLabel">Choose Page Designer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" tabindex="0"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <h6 id="selectedPageTitle" class="text-muted">Select a designer for: <span class="text-primary"></span></h6>
                </div>
                <div class="row g-4" id="designerOptionsContainer">
                    <!-- Designer cards will be populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
    <!-- Sweet Alerts js -->
    <script src="{{ asset('assets/admin/libs/sweetalert2/sweetalert2.min.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let currentPageId = null;
            let currentPageTitle = null;

            // Designer Selection Modal Logic
            const selectedPageTitleSpan = document.querySelector('#selectedPageTitle span');
            const designerOptionsContainer = document.getElementById('designerOptionsContainer');
            
            // Handle design button clicks
            document.querySelectorAll('.design-page-btn').forEach(button => {
                button.addEventListener('click', function() {
                    currentPageId = this.getAttribute('data-page-id');
                    currentPageTitle = this.getAttribute('data-page-title');
                    selectedPageTitleSpan.textContent = currentPageTitle;
                    
                    // Generate designer option cards with proper links
                    designerOptionsContainer.innerHTML = `
                        <div class="col-md-6">
                            <a href="/admin/pages/${currentPageId}/page-builder" class="text-decoration-none">
                                <div class="card h-100" style="transition: all 0.3s ease;">
                                    <div class="card-body text-center">
                                        <div class="avatar-lg mx-auto mb-4">
                                            <div class="avatar-title bg-primary-subtle text-primary rounded-circle fs-2">
                                                <i class="ri-layout-grid-line"></i>
                                            </div>
                                        </div>
                                        <h5 class="card-title text-dark">Page Builder</h5>
                                        <p class="text-muted mb-3">Structure-based layout designer with sections and widgets</p>
                                        <div class="features text-start">
                                            <div class="mb-2"><i class="ri-check-line text-success me-2"></i>Drag & drop sections</div>
                                            <div class="mb-2"><i class="ri-check-line text-success me-2"></i>Widget library</div>
                                            <div class="mb-2"><i class="ri-check-line text-success me-2"></i>Responsive layouts</div>
                                            <div class="mb-2"><i class="ri-check-line text-success me-2"></i>Template system</div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="/admin/pages/${currentPageId}/live-designer" class="text-decoration-none">
                                <div class="card h-100" style="transition: all 0.3s ease;">
                                    <div class="card-body text-center">
                                        <div class="avatar-lg mx-auto mb-4">
                                            <div class="avatar-title bg-info-subtle text-info rounded-circle fs-2">
                                                <i class="ri-brush-line"></i>
                                            </div>
                                        </div>
                                        <h5 class="card-title text-dark">Live Designer</h5>
                                        <p class="text-muted mb-3">Visual WYSIWYG editor for precise content design</p>
                                        <div class="features text-start">
                                            <div class="mb-2"><i class="ri-check-line text-success me-2"></i>Visual editing</div>
                                            <div class="mb-2"><i class="ri-check-line text-success me-2"></i>Component styles</div>
                                            <div class="mb-2"><i class="ri-check-line text-success me-2"></i>Live preview</div>
                                            <div class="mb-2"><i class="ri-check-line text-success me-2"></i>Advanced widgets</div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    `;
                    
                    // Add hover effects
                    designerOptionsContainer.querySelectorAll('.card').forEach(card => {
                        card.addEventListener('mouseenter', function() {
                            this.style.transform = 'translateY(-5px)';
                            this.style.boxShadow = '0 8px 25px rgba(0,0,0,0.15)';
                        });
                        card.addEventListener('mouseleave', function() {
                            this.style.transform = 'translateY(0)';
                            this.style.boxShadow = '';
                        });
                    });
                });
            });

            // Delete confirmation
            document.querySelectorAll('.remove-item-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation(); // Prevent row click
                    const pageId = this.getAttribute('data-page-id');

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonClass: 'btn btn-primary w-xs me-2 mt-2',
                        cancelButtonClass: 'btn btn-danger w-xs mt-2',
                        confirmButtonText: 'Yes, delete it!',
                        buttonsStyling: false,
                        showCloseButton: true
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            // Create and submit a form to delete the page
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = `/admin/pages/${pageId}`;
                            form.innerHTML = `
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="_method" value="DELETE">
                            `;
                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
@endsection

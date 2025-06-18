@extends('admin.users.wizard.layout')

@section('wizard-content')
<div class="tab-pane fade show active" id="step7" role="tabpanel">
    <div class="wizard-step-content">
        <div class="text-center mb-4">
            <h4 class="mb-2">Terms & Photo</h4>
            <p class="text-muted">Final step - upload photo and agree to terms</p>
        </div>

        <form method="POST" action="{{ route('admin.users.wizard.step7.post') }}" enctype="multipart/form-data">
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="photo" class="form-label">Profile Photo</label>
                        <input type="file" class="form-control @error('photo') is-invalid @enderror" 
                               id="photo" name="photo" accept="image/*">
                        @error('photo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Maximum file size: 2MB. Accepted formats: JPG, PNG.</div>
                    </div>
                    
                    <div class="mt-3" id="image-preview-container" style="display: none;">
                        <label class="form-label">Image Preview</label>
                        <div class="border rounded p-2 text-center">
                            <img id="image-preview" src="#" alt="Profile Photo Preview" style="max-width: 100%; max-height: 200px;">
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="alert alert-info">
                        <h6><i class="ri-information-line"></i> Photo Requirements</h6>
                        <ul class="mb-0 ps-3">
                            <li class="text-small">Recent passport-style photo</li>
                            <li class="text-small">Plain background</li>
                            <li class="text-small">Face clearly visible</li>
                            <li class="text-small">Good lighting</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input @error('agree_terms') is-invalid @enderror" 
                                   id="agree_terms" name="agree_terms" value="1" 
                                   {{ old('agree_terms') ? 'checked' : '' }} required>
                            <label for="agree_terms" class="form-check-label">
                                I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a> <span class="text-danger">*</span>
                            </label>
                            @error('agree_terms')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input @error('agree_privacy') is-invalid @enderror" 
                                   id="agree_privacy" name="agree_privacy" value="1" 
                                   {{ old('agree_privacy') ? 'checked' : '' }} required>
                            <label for="agree_privacy" class="form-check-label">
                                I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal">Privacy Policy</a> <span class="text-danger">*</span>
                            </label>
                            @error('agree_privacy')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" 
                                   id="agree_marketing" name="agree_marketing" value="1" 
                                   {{ old('agree_marketing') ? 'checked' : '' }}>
                            <label for="agree_marketing" class="form-check-label">
                                I agree to receive marketing communications and updates from the party
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-between mt-4 wizardbuttons">
                <a href="{{ route('admin.users.wizard.step6') }}" class="btn btn-outline-secondary">
                    <i class="ri-arrow-left-line me-1"></i> Back
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="ri-check-line me-1"></i> Complete Registration
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Terms Modal -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="termsModalLabel">Terms and Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>Party Membership Terms</h6>
                <p>By becoming a member of our party, you agree to:</p>
                <ul>
                    <li>Uphold the party's values and principles</li>
                    <li>Participate in party activities and events</li>
                    <li>Respect other members and party leadership</li>
                    <li>Follow party rules and regulations</li>
                    <li>Maintain confidentiality of party information</li>
                </ul>
                
                <h6>Code of Conduct</h6>
                <p>Members are expected to:</p>
                <ul>
                    <li>Behave ethically and with integrity</li>
                    <li>Respect diversity and inclusion</li>
                    <li>Contribute positively to party objectives</li>
                    <li>Avoid actions that may harm the party's reputation</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Privacy Modal -->
<div class="modal fade" id="privacyModal" tabindex="-1" aria-labelledby="privacyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="privacyModalLabel">Privacy Policy</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>Data Collection</h6>
                <p>We collect the following information:</p>
                <ul>
                    <li>Personal identification information (name, email, phone number)</li>
                    <li>Demographic information (age, gender, location)</li>
                    <li>Party membership details</li>
                    <li>Communication preferences</li>
                </ul>
                
                <h6>Data Usage</h6>
                <p>Your information is used to:</p>
                <ul>
                    <li>Manage your party membership</li>
                    <li>Send important communications</li>
                    <li>Organize party activities and events</li>
                    <li>Improve our services</li>
                </ul>
                
                <h6>Data Protection</h6>
                <p>We are committed to protecting your privacy and will:</p>
                <ul>
                    <li>Never sell your personal information</li>
                    <li>Use secure systems to store your data</li>
                    <li>Only share information with your consent</li>
                    <li>Allow you to update or delete your information</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('step-scripts')
<script>
    $(document).ready(function() {
        // Image preview functionality
        $('#photo').change(function() {
            const file = this.files[0];
            const previewContainer = $('#image-preview-container');
            const preview = $('#image-preview');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.attr('src', e.target.result);
                    previewContainer.show();
                };
                reader.readAsDataURL(file);
            } else {
                previewContainer.hide();
            }
        });
        
        // Form validation
        $('form').submit(function(e) {
            const termsChecked = $('#agree_terms').is(':checked');
            const privacyChecked = $('#agree_privacy').is(':checked');
            
            if (!termsChecked || !privacyChecked) {
                e.preventDefault();
                alert('Please agree to both Terms and Conditions and Privacy Policy to continue.');
                return false;
            }
        });
    });
</script>
@endsection 
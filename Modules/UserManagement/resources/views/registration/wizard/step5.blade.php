@extends('usermanagement::registration.wizard.layout')

@section('wizard-content')
    <h4 class="text-danger mb-3">Terms & Photo</h4>
    <p class="text-muted mb-4">Final step - upload your photo and agree to party terms.</p>
    <hr>   
    <form method="POST" action="{{ route('usermanagement.register.wizard.step5.post') }}" enctype="multipart/form-data">
        @csrf
        
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="photo">Profile Photo</label>
                    <input type="file" class="form-control @error('photo') is-invalid @enderror" id="photo" name="photo" accept="image/*">
                    @error('photo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Maximum file size: 2MB. Accepted formats: JPG, PNG.</small>
                </div>
                
                <div class="mt-3" id="image-preview-container" style="display: none;">
                    <label>Image Preview</label>
                    <div class="border rounded p-2 text-center">
                        <img id="image-preview" src="#" alt="Profile Photo Preview" style="max-width: 100%; max-height: 200px;">
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="alert alert-info">
                    <h6><i class="fa fa-info-circle"></i> Photo Requirements</h6>
                    <ul class="mb-0 ps-3">
                        <li class="text-small">Recent passport-style photo</li>
                        <li class="text-small">Plain background</li>
                        <li class="text-small">Face clearly visible</li>
                        <li class="text-small">Good lighting</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="form-group mb-4">
            <div class="form-check">
                <input type="checkbox" class="form-check-input @error('agree_terms') is-invalid @enderror" id="agree_terms" name="agree_terms" value="1" required {{ old('agree_terms') ? 'checked' : '' }}>
                <label class="form-check-label" for="agree_terms">
                    I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a> of the party membership.
                </label>
                @error('agree_terms')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="form-group mb-4">
            <div class="form-check">
                <input type="checkbox" class="form-check-input @error('agree_privacy') is-invalid @enderror" id="agree_privacy" name="agree_privacy" value="1" required {{ old('agree_privacy') ? 'checked' : '' }}>
                <label class="form-check-label" for="agree_privacy">
                    I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal">Privacy Policy</a> of the party.
                </label>
                @error('agree_privacy')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="form-group mb-4">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="agree_marketing" name="agree_marketing" value="1" {{ old('agree_marketing') ? 'checked' : '' }}>
                <label class="form-check-label" for="agree_marketing">
                    I agree to receive updates and communications from the party.
                </label>
            </div>
        </div>
        
        <div class="wizard-footer">
            <a href="{{ route('usermanagement.register.wizard.step4') }}" class="btn btn-outline-secondary">
                <i class="fa fa-arrow-left me-1"></i> Back
            </a>
            <button type="submit" class="btn btn-success">
                <i class="fa fa-check-circle me-1"></i> Complete Registration
            </button>
        </div>
    </form>
    
    <!-- Terms Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="termsModalLabel">Terms and Conditions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h5>Party Membership Terms and Conditions</h5>
                    <p>Last Updated: {{ date('F d, Y') }}</p>
                    
                    <h6>1. Membership</h6>
                    <p>By registering as a member of our party, you agree to uphold the party's constitution, values, and principles. Membership is open to all citizens of Kenya who are 18 years and above.</p>
                    
                    <h6>2. Responsibilities</h6>
                    <p>As a member, you are expected to participate in party activities, contribute to the party's growth, and represent the party positively in all forums.</p>
                    
                    <h6>3. Fees</h6>
                    <p>Membership may require payment of fees as determined by the party from time to time. These fees contribute to the operational costs of the party.</p>
                    
                    <h6>4. Termination</h6>
                    <p>The party reserves the right to terminate membership for violation of the party's constitution or engagement in activities that bring the party into disrepute.</p>
                    
                    <h6>5. Changes to Terms</h6>
                    <p>The party reserves the right to modify these terms at any time. Members will be notified of any changes.</p>
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
                    <h5>Party Privacy Policy</h5>
                    <p>Last Updated: {{ date('F d, Y') }}</p>
                    
                    <h6>1. Information Collection</h6>
                    <p>We collect personal information such as your name, contact details, identification number, and demographic information to facilitate your membership and participation in party activities.</p>
                    
                    <h6>2. Use of Information</h6>
                    <p>Your information will be used for membership management, communication, event organization, and as required by electoral laws.</p>
                    
                    <h6>3. Data Security</h6>
                    <p>We implement appropriate security measures to protect your personal information from unauthorized access, alteration, or disclosure.</p>
                    
                    <h6>4. Data Sharing</h6>
                    <p>Your information may be shared with electoral bodies as required by law, but will not be sold or shared with third parties for commercial purposes without your explicit consent.</p>
                    
                    <h6>5. Your Rights</h6>
                    <p>You have the right to access, correct, or delete your personal information held by the party, subject to legal requirements.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Image preview functionality
        $('#photo').change(function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#image-preview').attr('src', e.target.result);
                    $('#image-preview-container').show();
                }
                reader.readAsDataURL(file);
            } else {
                $('#image-preview-container').hide();
            }
        });
        
        // Checkbox validation
        $('form').submit(function(e) {
            if (!$('#agree_terms').is(':checked') || !$('#agree_privacy').is(':checked')) {
                e.preventDefault();
                if (!$('#agree_terms').is(':checked')) {
                    $('#agree_terms').addClass('is-invalid');
                }
                if (!$('#agree_privacy').is(':checked')) {
                    $('#agree_privacy').addClass('is-invalid');
                }
                return false;
            }
            return true;
        });
    });
</script>
@endpush

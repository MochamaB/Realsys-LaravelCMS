@props(['user' => null, 'size' => 'xl'])

<div class="profile-user position-relative d-inline-block">
    @if($user && $user->getFirstMediaUrl('profile_photos'))
        <img src="{{ $user->getFirstMediaUrl('profile_photos') }}" 
             class="rounded-circle avatar-{{ $size }} user-profile-image" 
             alt="{{ $user->name }}">
    @else
        <div class="avatar-{{ $size }}">
            <span class="avatar-title rounded-circle bg-primary">
                {{ $user ? substr($user->name, 0, 1) : 'U' }}
            </span>
        </div>
    @endif

    <div class="avatar-xs p-0 rounded-circle profile-photo-edit">
        <input id="profile-img-file-input" type="file" class="profile-img-file-input" accept="image/*">
        <label for="profile-img-file-input" class="profile-photo-edit avatar-xs">
            <span class="avatar-title rounded-circle bg-light text-body material-shadow">
                <i class="ri-camera-fill"></i>
            </span>
        </label>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('profile-img-file-input');
    if (input) {
        input.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const formData = new FormData();
                formData.append('profile_photo', this.files[0]);
                formData.append('_token', '{{ csrf_token() }}');

                fetch('{{ $user ? route("admin.users.update-profile-picture", $user->id) : route("user.profile.update-picture") }}', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload the page to show the new image
                        window.location.reload();
                    } else {
                        alert('Failed to update profile picture. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the profile picture.');
                });
            }
        });
    }
});
</script>
@endpush 
<!-- Contact Form -->
<div class="my-5">
    @php
        // Get contact data from widget content
        $contactData = $content['contact'] ?? [];
        
        // Fallback values if no contact data
        $title = $widget->name ?? 'Contact Us';
        $email = $contactData['email'] ?? null;
        $phone = $contactData['phone'] ?? null;
        $address = $contactData['address'] ?? null;
        $formRecipient = $contactData['form_recipient'] ?? null;
        $showMap = $contactData['show_map'] ?? false;
        $mapLocation = $contactData['map_location'] ?? null;
    @endphp
    
    <h2>{{ $title }}</h2>
    
    @if($email || $phone || $address)
        <div class="contact-info mb-4">
            @if($address)
                <p><strong>Address:</strong> {{ $address }}</p>
            @endif
            @if($phone)
                <p><strong>Phone:</strong> {{ $phone }}</p>
            @endif
            @if($email)
                <p><strong>Email:</strong> <a href="mailto:{{ $email }}">{{ $email }}</a></p>
            @endif
        </div>
    @endif
    
    <form id="contactForm" action="{{ route('contact.submit') }}" method="POST">
        @csrf
        @if($formRecipient)
            <input type="hidden" name="recipient" value="{{ $formRecipient }}">
        @endif
        
        <div class="form-floating mb-3">
            <input class="form-control" id="name" name="name" type="text" placeholder="Enter your name..." required />
            <label for="name">Name</label>
            @error('name')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-floating mb-3">
            <input class="form-control" id="email" name="email" type="email" placeholder="Enter your email..." required />
            <label for="email">Email address</label>
            @error('email')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-floating mb-3">
            <input class="form-control" id="phone" name="phone" type="tel" placeholder="Enter your phone number..." />
            <label for="phone">Phone Number</label>
            @error('phone')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-floating mb-3">
            <textarea class="form-control" id="message" name="message" placeholder="Enter your message here..." style="height: 12rem" required></textarea>
            <label for="message">Message</label>
            @error('message')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        
        <!-- Submit Button-->
        <button class="btn btn-primary text-uppercase" type="submit">Send</button>
    </form>
    
    @if($showMap && $mapLocation)
        <div class="mt-5">
            <h3>Our Location</h3>
            <div class="map-container" style="height: 400px;">
                <iframe 
                    width="100%" 
                    height="100%" 
                    frameborder="0" 
                    scrolling="no" 
                    marginheight="0" 
                    marginwidth="0" 
                    src="https://maps.google.com/maps?q={{ urlencode($mapLocation) }}&t=m&z=15&output=embed&iwloc=near" 
                    title="Location map">
                </iframe>
            </div>
        </div>
    @endif
</div>

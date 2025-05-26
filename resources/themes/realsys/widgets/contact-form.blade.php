<!-- Contact Form -->
<div class="my-5">
    <h2>{{ $widget->title }}</h2>
    <form id="contactForm" action="{{ route('contact.submit') }}" method="POST">
        @csrf
        <div class="form-floating">
            <input class="form-control" id="name" name="name" type="text" placeholder="Enter your name..." required />
            <label for="name">Name</label>
            @error('name')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-floating">
            <input class="form-control" id="email" name="email" type="email" placeholder="Enter your email..." required />
            <label for="email">Email address</label>
            @error('email')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-floating">
            <input class="form-control" id="phone" name="phone" type="tel" placeholder="Enter your phone number..." />
            <label for="phone">Phone Number</label>
            @error('phone')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-floating">
            <textarea class="form-control" id="message" name="message" placeholder="Enter your message here..." style="height: 12rem" required></textarea>
            <label for="message">Message</label>
            @error('message')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <br />
        <!-- Submit Button-->
        <button class="btn btn-primary text-uppercase" type="submit">Send</button>
    </form>
</div>

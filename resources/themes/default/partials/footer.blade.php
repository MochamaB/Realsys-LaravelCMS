<footer class="site-footer bg-light mt-4">
    <div class="container py-4">
        <div class="row">
            <div class="col-md-4">
                <h5>About Us</h5>
                <p>{{ config('app.name') }} - Your trusted partner in web solutions.</p>
            </div>
            <div class="col-md-4">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    @foreach($footerMenu->items as $item)
                        <li>
                            <a href="{{ $item->url }}" 
                               class="text-decoration-none"
                               @if($item->target) target="{{ $item->target }}" @endif>
                                {{ $item->label }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="col-md-4">
                <h5>Contact</h5>
                <ul class="list-unstyled">
                    <li>Email: info@example.com</li>
                    <li>Phone: +1234567890</li>
                    <li>Address: 123 Street Name, City</li>
                </ul>
            </div>
        </div>
        <hr>
        <div class="text-center">
            <p class="mb-0">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</footer>

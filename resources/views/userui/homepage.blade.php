<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventIntel Homepage</title>
    <link rel="stylesheet" href="{{ asset('css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/userui/navbar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/userui/homepage.css') }}">
</head>
<body>
    @php
        $services = [
            ['name' => 'Photographer', 'icon' => 'fa-camera', 'desc' => 'Capture every moment', 'image' => 'photographer.avif'],
            ['name' => 'Catering', 'icon' => 'fa-utensils', 'desc' => 'Delicious food services', 'image' => 'catering.jpg'],
            ['name' => 'Host / MC', 'icon' => 'fa-microphone', 'desc' => 'Professional event hosting', 'image' => 'images.jpg'],
            ['name' => 'Event Coordinator', 'icon' => 'fa-clipboard-list', 'desc' => 'Full event planning', 'image' => 'eri-neeman-24-scaled.jpeg'],
            ['name' => 'Venue', 'icon' => 'fa-building', 'desc' => 'Perfect event locations', 'image' => 'venue.avif'],
            ['name' => 'Stylist', 'icon' => 'fa-wand-magic-sparkles', 'desc' => 'Event styling & design', 'image' => 'clothing_stylist.jpg'],
            ['name' => 'Lights & Sound', 'icon' => 'fa-music', 'desc' => 'Audio & lighting setup', 'image' => 'ledlights.jpg'],
        ];
    @endphp

    <div class="homepage-container">
        @include('userui.partials.navbar', ['active' => 'home'])

        <main>
            <section class="homepage-hero">
                <h1>Plan Better Events with<span>EventIntel</span></h1>
                <div class="homepage-subtitle">Smart Event Planning Platform</div>
                <p>Organize memorable events, connect with professional coordinators, and receive intelligent recommendations tailored to your needs.</p>
                <div class="homepage-button-group">
                    <a class="homepage-action primary" href="{{ route('coordinator.events') }}">Create an Event</a>
                    <a class="homepage-action" href="{{ route('coordinator.dashboard') }}">Find an Event Coordinator</a>
                    <a class="homepage-action" href="{{ route('supplier.feed') }}">View Supplier Newsfeed</a>
                </div>
            </section>

            <section class="homepage-service-section">
                <h2>Browse Supplier Categories</h2>
                <p>Select the service you need for your event</p>

                <div class="homepage-carousel">
                    <button class="homepage-carousel-button left" type="button" onclick="moveServiceCarousel(-1)" aria-label="Previous services">&#10094;</button>
                    <div class="homepage-track-container">
                        <div class="homepage-track" id="serviceTrack">
                            @foreach ($services as $service)
                                <article class="homepage-service-card">
                                    <div class="homepage-service-image">
                                        <img src="{{ asset('images/userui/' . $service['image']) }}" alt="{{ $service['name'] }}">
                                    </div>
                                    <div>
                                        <i class="fas {{ $service['icon'] }}" aria-hidden="true"></i>
                                        <h3>{{ $service['name'] }}</h3>
                                        <p>{{ $service['desc'] }}</p>
                                    </div>
                                    <button type="button" onclick="selectService(@js($service['name']))">View Providers</button>
                                </article>
                            @endforeach
                        </div>
                    </div>
                    <button class="homepage-carousel-button right" type="button" onclick="moveServiceCarousel(1)" aria-label="Next services">&#10095;</button>
                </div>
            </section>
        </main>
    </div>

    <script>
        let serviceIndex = 0;

        function moveServiceCarousel(direction) {
            const track = document.getElementById('serviceTrack');
            const cards = document.querySelectorAll('.homepage-service-card');
            if (!track || cards.length === 0) return;

            const visibleCards = window.innerWidth <= 720 ? 1 : window.innerWidth <= 1100 ? 2 : 3;
            const cardWidth = cards[0].offsetWidth + 44;
            const maxScrollable = Math.max(0, cards.length - visibleCards);
            serviceIndex = Math.min(Math.max(serviceIndex + direction, 0), maxScrollable);
            track.style.transform = `translateX(-${serviceIndex * cardWidth}px)`;
            updateActiveCard(visibleCards);
        }

        function selectService(service) {
            const destination = service === 'Event Coordinator'
                ? @js(route('coordinator.dashboard'))
                : @js(route('supplier.feed')) + '?service=' + encodeURIComponent(service);
            window.location.href = destination;
        }

        function updateActiveCard(visibleCards = 3) {
            const cards = document.querySelectorAll('.homepage-service-card');
            cards.forEach((card, index) => {
                card.classList.toggle('visible', index >= serviceIndex && index < serviceIndex + visibleCards);
                card.classList.toggle('active', index === serviceIndex + Math.floor(visibleCards / 2));
            });
        }

        window.addEventListener('load', () => updateActiveCard());
        window.addEventListener('resize', () => {
            serviceIndex = 0;
            moveServiceCarousel(0);
        });
    </script>
</body>
</html>

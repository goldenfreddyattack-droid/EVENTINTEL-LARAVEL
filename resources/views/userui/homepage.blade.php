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

        $browseServices = [
            ['key' => 'venue', 'label' => 'Venues', 'icon' => 'fa-building', 'desc' => 'Find the perfect place for weddings, birthdays, and corporate events.', 'href' => route('services.index', ['service' => 'venue'])],
            ['key' => 'catering', 'label' => 'Catering', 'icon' => 'fa-utensils', 'desc' => 'Explore catering packages, menu options, and food experiences.', 'href' => route('services.index', ['service' => 'catering'])],
            ['key' => 'host', 'label' => 'Host / MC', 'icon' => 'fa-microphone', 'desc' => 'Book professional hosts, emcees, and event anchors.', 'href' => route('services.index', ['service' => 'host'])],
            ['key' => 'photographer', 'label' => 'Photographer', 'icon' => 'fa-camera', 'desc' => 'Browse photography and videography teams for your event.', 'href' => route('services.index', ['service' => 'photographer'])],
            ['key' => 'sounds_lights', 'label' => 'Lights & Sound', 'icon' => 'fa-music', 'desc' => 'Compare AV teams, sound systems, and stage lighting providers.', 'href' => route('services.index', ['service' => 'sounds_lights'])],
            ['key' => 'clothes', 'label' => 'Styling & Attire', 'icon' => 'fa-wand-magic-sparkles', 'desc' => 'Discover designers, stylists, and event attire providers.', 'href' => route('services.index', ['service' => 'clothes'])],
            ['key' => 'packages', 'label' => 'Packages', 'icon' => 'fa-box-open', 'desc' => 'Compare pre-arranged package offers and bundled event services.', 'href' => route('packages')],
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
                    <a class="homepage-action primary" id="homepageCreateEvent" href="{{ route('events.create') }}" data-active-event-limit="{{ $planningLimitReached ? '1' : '0' }}">Create an Event</a>
                    <a class="homepage-action" href="{{ route('coordinators.index') }}">Find an Event Coordinator</a>
                    <a class="homepage-action" href="{{ route('newsfeed') }}">View Supplier Newsfeed</a>
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

            <section class="homepage-browse-section">
                <div class="homepage-browse-header">
                    <div>
                        <span class="homepage-browse-tag">Before you create</span>
                        <h2>Browse all services and packages</h2>
                    </div>
                    <div class="homepage-browse-tools">
                        <label for="serviceCategoryFilter" class="sr-only">Filter service categories</label>
                        <select id="serviceCategoryFilter" aria-label="Choose a service category">
                            <option value="all">All categories</option>
                            <option value="venue">Venues</option>
                            <option value="catering">Catering</option>
                            <option value="host">Host / MC</option>
                            <option value="photographer">Photographer</option>
                            <option value="sounds_lights">Lights & Sound</option>
                            <option value="clothes">Styling & Attire</option>
                            <option value="packages">Packages</option>
                        </select>
                        <a href="{{ route('services.index', ['service' => 'venue']) }}">Browse all services</a>
                    </div>
                </div>

                <div class="homepage-browse-grid" id="browseGrid">
                    @foreach ($browseServices as $service)
                        <article class="homepage-browse-card" data-category="{{ $service['key'] }}">
                            <div class="homepage-browse-icon"><i class="fas {{ $service['icon'] }}" aria-hidden="true"></i></div>
                            <h3>{{ $service['label'] }}</h3>
                            <p>{{ $service['desc'] }}</p>
                            <div class="homepage-browse-meta">
                                <span>{{ $service['key'] === 'packages' ? 'Bundle offers' : 'Verified suppliers' }}</span>
                                <a href="{{ $service['href'] }}">Explore</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        </main>

        <footer class="homepage-footer">
            <div class="homepage-footer-brand">
                <div class="homepage-footer-logo">EventIntel</div>
                <p>Streamlining event planning so clients can discover suppliers, compare packages, and build events faster without the usual back-and-forth.</p>
            </div>
            <div class="homepage-footer-links">
                <div>
                    <h4>Browse</h4>
                    <a href="{{ route('services.index', ['service' => 'venue']) }}">Venues</a>
                    <a href="{{ route('services.index', ['service' => 'catering']) }}">Catering</a>
                    <a href="{{ route('services.index', ['service' => 'photographer']) }}">Photographer</a>
                </div>
                <div>
                    <h4>Planning</h4>
                    <a href="{{ route('events.create') }}">Create Event</a>
                    <a href="{{ route('packages') }}">Packages</a>
                    <a href="{{ route('coordinators.index') }}">Coordinators</a>
                </div>
                <div>
                    <h4>Support</h4>
                    <a href="{{ route('your.events') }}">My Events</a>
                    <a href="{{ route('supplier.feed') }}">Supplier Feed</a>
                    <a href="{{ route('home') }}">Home</a>
                </div>
            </div>
        </footer>
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
            const serviceKeys = {
                'Photographer': 'photographer',
                'Catering': 'catering',
                'Host / MC': 'host',
                'Venue': 'venue',
                'Stylist': 'clothes',
                'Lights & Sound': 'sounds_lights',
            };
            const destination = service === 'Event Coordinator'
                ? @js(route('coordinators.index'))
                : @js(url('/carousel/services')) + '/' + serviceKeys[service];
            window.location.href = destination;
        }

        function updateActiveCard(visibleCards = 3) {
            const cards = document.querySelectorAll('.homepage-service-card');
            cards.forEach((card, index) => {
                card.classList.toggle('visible', index >= serviceIndex && index < serviceIndex + visibleCards);
                card.classList.toggle('active', index === serviceIndex + Math.floor(visibleCards / 2));
            });
        }

        const serviceCategoryFilter = document.getElementById('serviceCategoryFilter');
        if (serviceCategoryFilter) {
            serviceCategoryFilter.addEventListener('change', function () {
                const selected = this.value;
                document.querySelectorAll('.homepage-browse-card').forEach((card) => {
                    const shouldShow = selected === 'all' || card.dataset.category === selected;
                    card.style.display = shouldShow ? '' : 'none';
                });
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

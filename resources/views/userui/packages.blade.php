<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EventIntel - Packages</title>
    <link rel="stylesheet" href="{{ asset('css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/userui/navbar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/userui/packages.css') }}">
</head>
<body>
    <div class="packages-page">
        @include('userui.partials.navbar', ['active' => 'packages'])

        <main class="packages-content">
            <header class="packages-intro">
                <p class="packages-eyebrow">Plan with confidence</p>
                <h1>Package View</h1>
                <p>Choose a package that fits your event and see exactly how your budget is distributed.</p>
            </header>

            <form class="event-type-bar" method="GET" action="{{ route('packages') }}">
                <label for="eventTypeSelect">Event type</label>
                <select id="eventTypeSelect" name="event_type" onchange="this.form.submit()">
                    @foreach (['Birthday', 'Debut', 'Wedding', 'Anniversary', 'Christening', 'Gender Reveal', 'Reunion'] as $type)
                        <option value="{{ $type }}" @selected($eventKey === strtolower($type))>{{ $type }}</option>
                    @endforeach
                </select>
                <a class="packages-back" href="{{ route('coordinator.events') }}">Back to Create Event</a>
            </form>

            <section class="packages-grid" aria-label="Available packages">
                @foreach ($activePackages as $index => $package)
                    <article class="package-card {{ $index === 1 ? 'recommended' : '' }}">
                        @if ($index === 1)
                            <span class="package-badge">Most popular</span>
                        @endif
                        <p class="package-tier">{{ $package['tier'] }} tier</p>
                        <h2>{{ $package['name'] }}</h2>
                        <p class="package-description">{{ $package['desc'] }}</p>
                        <p class="package-price">&#8369;{{ number_format($package['price']) }} <small>total</small></p>

                        <ul class="service-list">
                            @foreach (array_keys($serviceNames) as $serviceKey)
                                @php
                                    $included = in_array($serviceKey, $package['services'], true);
                                    $startingPrice = isset($minByCategory[$serviceKey]) ? 'from &#8369;' . number_format($minByCategory[$serviceKey]) : '';
                                @endphp
                                <li class="{{ $included ? '' : 'missing' }}">
                                    <i class="fa-solid {{ $serviceIcons[$serviceKey] }}" aria-hidden="true"></i>
                                    <span>{{ $serviceNames[$serviceKey] }}</span>
                                    @if ($included && $startingPrice)
                                        <small>{!! $startingPrice !!}</small>
                                    @endif
                                </li>
                            @endforeach
                        </ul>

                        <div class="package-total"><span>Package total</span><strong>&#8369;{{ number_format($package['price']) }}</strong></div>
                        <button
                            class="package-choose"
                            type="button"
                            data-name="{{ $package['name'] }}"
                            data-price="{{ $package['price'] }}"
                            data-event-type="{{ $eventType }}"
                            data-services='@json($package['services'])'>
                            Choose this package
                        </button>
                    </article>
                @endforeach
            </section>

            <section class="budget-section">
                <div class="section-heading">
                    <div>
                        <p class="packages-eyebrow">Make every peso count</p>
                        <h2>Budget recommendation distribution</h2>
                    </div>
                    <i class="fa-solid fa-chart-pie" aria-hidden="true"></i>
                </div>
                <p class="budget-description">Enter your total budget and guest count to see a practical starting allocation for each service.</p>

                <div class="budget-controls">
                    <label>
                        <span>Total budget (&#8369;)</span>
                        <input type="number" id="budgetInput" value="{{ $selectedBudget ?: 50000 }}" min="1000">
                    </label>
                    <label>
                        <span>Number of guests</span>
                        <input type="number" id="paxInput" value="100" min="1">
                    </label>
                    <button class="package-choose compute-button" type="button" onclick="updateAllocation()">Compute distribution</button>
                </div>

                <div class="allocation-grid" id="allocGrid"></div>
                <div class="allocation-total"><span>Total allocated</span><strong>&#8369;<span id="allocTotal">0</span></strong></div>
                <p class="budget-note"><i class="fa-solid fa-lightbulb" aria-hidden="true"></i> These percentages are industry-inspired starting points. Adjust them around what matters most for your event.</p>
            </section>
        </main>
    </div>

    <script>
        const allocation = @json($allocation);
        const chooseEventUrl = @json(route('coordinator.events'));

        function choosePackage(button) {
            const services = JSON.parse(button.dataset.services || '[]');
            const payload = {
                selectedPackage: button.dataset.name,
                budget: Number(button.dataset.price),
                eventType: button.dataset.eventType,
                services
            };

            sessionStorage.setItem('event_package_selection', JSON.stringify(payload));
            document.cookie = `event_package_name=${encodeURIComponent(payload.selectedPackage)}; path=/; max-age=3600`;
            document.cookie = `event_budget=${payload.budget}; path=/; max-age=3600`;
            document.cookie = `event_package_services=${encodeURIComponent(services.join(','))}; path=/; max-age=3600`;
            window.location.href = `${chooseEventUrl}?from=package&budget=${payload.budget}&event_type=${encodeURIComponent(payload.eventType)}&services=${encodeURIComponent(services.join(','))}`;
        }

        function updateAllocation() {
            const budget = Number(document.getElementById('budgetInput').value) || 0;
            const guests = Number(document.getElementById('paxInput').value) || 1;
            const grid = document.getElementById('allocGrid');
            grid.innerHTML = '';
            let total = 0;

            Object.entries(allocation).forEach(([category, share]) => {
                const amount = Math.round(budget * share);
                total += amount;
                grid.insertAdjacentHTML('beforeend', `
                    <div class="allocation-item">
                        <div class="allocation-head"><strong>${category}</strong><span>${Math.round(share * 100)}%</span></div>
                        <div class="allocation-bar"><i style="width:${share * 100}%"></i></div>
                        <strong class="allocation-amount">&#8369;${amount.toLocaleString()}</strong>
                        <small>~ &#8369;${Math.round(amount / guests).toLocaleString()} / guest</small>
                    </div>
                `);
            });

            document.getElementById('allocTotal').textContent = total.toLocaleString();
        }

        document.querySelectorAll('.package-choose[data-services]').forEach(button => {
            button.addEventListener('click', () => choosePackage(button));
        });
        updateAllocation();
    </script>
</body>
</html>

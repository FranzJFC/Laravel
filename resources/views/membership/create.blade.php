@extends('layouts.auth')

@section('title', 'Member Signup')
@section('eyebrow', 'Become a member')
@section('heading', 'Create your membership.')
@section('intro', 'Choose your membership, create your login, and tell us where you are. Already a member? Use member login.')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <link rel="stylesheet" href="{{ asset('css/membership-application.css') }}">
@endpush

@section('content')
    <form method="POST" action="{{ route('register.store') }}" class="application-form">
        @csrf
        <div class="application-grid">
            <section class="panel" aria-labelledby="details-heading">
                <div class="panel-head">
                    <div class="application-section-heading">
                        <span class="application-step" aria-hidden="true">01</span>
                        <div><p class="kicker">About you</p><h2 id="details-heading">Membership details</h2></div>
                    </div>

                </div>
                <p class="section-copy">Choose your category and enter your contact information. Fields marked * are required.</p>
                <div class="form-grid">
                    <div class="field full">
                        <label for="category">Membership category <span aria-hidden="true">*</span></label>
                        <select id="category" name="membership_category_id" required @error('membership_category_id') aria-invalid="true" aria-describedby="category-error" @enderror>
                            <option value="">Choose a category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('membership_category_id') == $category->id)>{{ $category->name }}{{ $category->level ? ' - '.$category->level : '' }}</option>
                            @endforeach
                        </select>
                        @error('membership_category_id') <span id="category-error" class="error">{{ $message }}</span> @enderror
                    </div>
                    <div class="field full">
                        <label for="membership_level_id">Membership level / subscription <span aria-hidden="true">*</span></label>
                        <select id="membership_level_id" name="membership_level_id" required>
                            <option value="">Choose a membership level</option>
                            @foreach($levels as $level)
                                <option value="{{ $level->id }}" @selected(old('membership_level_id') == $level->id)>{{ $level->name }} &mdash; &#8369;{{ number_format($level->fee, 2) }} / {{ $level->validity_months }} months</option>
                            @endforeach
                        </select>
                        <p class="application-hint">Your subscription fee is based on this level. No payment is collected during signup.</p>
                        @error('membership_level_id') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div class="field full">
                        <label for="full_name">Full name <span aria-hidden="true">*</span></label>
                        <input id="full_name" name="full_name" value="{{ old('full_name') }}" placeholder="e.g. Ana Cruz" maxlength="150" autocomplete="name" required @error('full_name') aria-invalid="true" aria-describedby="full-name-error" @enderror>
                        @error('full_name') <span id="full-name-error" class="error">{{ $message }}</span> @enderror
                    </div>
                    <div class="field full">
                        <label for="email">Email address <span aria-hidden="true">*</span></label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" maxlength="255" autocomplete="email" required @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                        @error('email') <span id="email-error" class="error">{{ $message }}</span> @enderror
                        <p class="application-hint">Use this email to sign in. We'll send a verification link and application reference here.</p>
                    </div>
                    <div class="field full">
                        <label for="password">Password <span aria-hidden="true">*</span></label>
                        <input id="password" type="password" name="password" minlength="8" autocomplete="new-password" required aria-describedby="password-hint">
                        <p id="password-hint" class="application-hint">At least 8 characters, including letters and numbers.</p>
                        @error('password') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div class="field full">
                        <label for="password_confirmation">Confirm password <span aria-hidden="true">*</span></label>
                        <input id="password_confirmation" type="password" name="password_confirmation" minlength="8" autocomplete="new-password" required>
                    </div>
                    <div class="field full">
                        <label for="phone">Phone <span class="application-optional">Optional</span></label>
                        <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" placeholder="e.g. 0917 123 4567" maxlength="30" autocomplete="tel" @error('phone') aria-invalid="true" aria-describedby="phone-error" @enderror>
                        @error('phone') <span id="phone-error" class="error">{{ $message }}</span> @enderror
                    </div>
                    <div class="field full">
                        <label for="address">Address <span class="application-optional">Optional</span></label>
                        <input id="address" name="address" value="{{ old('address') }}" placeholder="Street, barangay, city" maxlength="255" autocomplete="street-address" @error('address') aria-invalid="true" aria-describedby="address-error" @enderror>
                        @error('address') <span id="address-error" class="error">{{ $message }}</span> @enderror
                    </div>
                </div>
            </section>

            <section class="panel application-location" aria-labelledby="location-heading">
                <div class="panel-head">
                    <div class="application-section-heading">
                        <span class="application-step" aria-hidden="true">02</span>
                        <div><p class="kicker">Find your spot</p><h2 id="location-heading">Your location</h2></div>
                    </div>
                    <span class="badge">Interactive map</span>
                </div>
                <p class="section-copy">Click the map or drag the marker to choose your location. The weather updates automatically.</p>
                <div id="map" aria-label="Choose membership application location"></div>
                <input id="lat" type="hidden" name="latitude" value="{{ old('latitude', '14.5995') }}">
                <input id="lon" type="hidden" name="longitude" value="{{ old('longitude', '120.9842') }}">
                <p id="coordinates" class="application-coordinates" aria-live="polite"></p>
                @error('latitude') <span class="error">{{ $message }}</span> @enderror
                @error('longitude') <span class="error">{{ $message }}</span> @enderror
                <div class="application-weather" aria-labelledby="weather-heading">
                    <span class="application-weather-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v2M5.6 5.6 7 7M3 12h2M18 12h2M17 7l1.4-1.4"/><path d="M8 13a4 4 0 1 1 7.7-1.5"/><path d="M8 20a4 4 0 1 1 1-7.9A5 5 0 0 1 19 14a3 3 0 0 1-1 6Z"/></svg>
                    </span>
                    <div>
                        <h3 id="weather-heading">Weather at your location</h3>
                        <p id="weather" role="status">Weather has not been loaded.</p>
                        <span class="application-weather-credit">Weather data by <a href="https://open-meteo.com/" target="_blank" rel="noopener noreferrer">Open-Meteo</a></span>
                    </div>
                </div>
            </section>
        </div>
        <div class="application-submit panel">
            <div><h2>Ready to join?</h2><p>Create your account and submit your application for verification. You can then view tasks, your transactions, and your payments.</p></div>
            <button class="button" type="submit">Sign up &amp; apply <span aria-hidden="true">&rarr;</span></button>
        </div>
    </form>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    const latitudeInput = document.getElementById('lat');
    const longitudeInput = document.getElementById('lon');
    const weatherPanel = document.getElementById('weather');
    const validCoordinate = (value, limit, fallback) => {
        const number = Number(value);
        return value.trim() !== '' && Number.isFinite(number) && Math.abs(number) <= limit ? number : fallback;
    };
    const lat = validCoordinate(latitudeInput.value, 90, 14.5995);
    const lon = validCoordinate(longitudeInput.value, 180, 120.9842);
    const map = L.map('map').setView([lat, lon], 13);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    const marker = L.marker([lat, lon], {draggable: true}).addTo(map);
    let weatherRequest;
    async function loadWeather(latitude, longitude) {
        weatherRequest?.abort();
        const controller = new AbortController();
        weatherRequest = controller;
        weatherPanel.textContent = 'Loading weather...';
        const url = new URL(@json(route('weather.show', [], false)), window.location.origin);
        url.search = new URLSearchParams({lat: latitude, lon: longitude});
        try {
            const response = await fetch(url, {headers: {'Accept': 'application/json'}, signal: controller.signal});
            if (!response.ok) throw new Error('Weather unavailable');
            const weather = await response.json();
            if (weatherRequest !== controller) return;
            weatherPanel.textContent = `${weather.place}: ${weather.temperature}\u00b0C, ${weather.description}, humidity ${weather.humidity}%`;
        } catch (error) {
            if (error.name !== 'AbortError' && weatherRequest === controller) weatherPanel.textContent = 'Weather is currently unavailable.';
        }
    }
    function setLocation(latitude, longitude) {
        latitude = Math.max(-90, Math.min(90, latitude));
        longitude = ((longitude + 180) % 360 + 360) % 360 - 180;
        latitudeInput.value = latitude.toFixed(7);
        longitudeInput.value = longitude.toFixed(7);
        document.getElementById('coordinates').textContent = `Latitude: ${latitude.toFixed(5)}, Longitude: ${longitude.toFixed(5)}`;
        marker.setLatLng([latitude, longitude]);
        loadWeather(latitude, longitude);
    }
    map.on('click', event => setLocation(event.latlng.lat, event.latlng.lng));
    marker.on('dragend', () => { const position = marker.getLatLng(); setLocation(position.lat, position.lng); });
    setLocation(lat, lon);
</script>
@endpush

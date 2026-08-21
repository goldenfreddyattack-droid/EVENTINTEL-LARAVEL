<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EventIntel - Profile</title>
    <link rel="stylesheet" href="{{ asset('css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/userui/navbar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/userui/profile.css') }}">
</head>
<body>
    <div class="profile-page">
        @include('userui.partials.navbar')

        <div class="profile-breadcrumbs"><a href="{{ route('home') }}">Home</a><span>/</span><span>Profile</span></div>
        <div class="profile-grid">
            <section class="profile-panel profile-summary">
                <div class="profile-avatar"><i class="fa-regular fa-user" aria-hidden="true"></i></div>
                <div>
                    <h1>{{ $user->full_name ?: $user->name ?: $user->username }}</h1>
                    <p>{{ $user->username ?: $user->email }}</p>
                    <span class="profile-status">{{ $user->role === 'client' ? 'Client' : ucfirst($user->role) }} / {{ ucfirst($user->status ?? 'approved') }}</span>
                </div>
                <div class="profile-stats">
                    <div><strong>Account</strong><span>{{ $user->role === 'client' ? 'Client' : ucfirst($user->role) }}</span></div>
                    <div><strong>Status</strong><span>{{ ucfirst($user->status ?? 'approved') }}</span></div>
                    <div><strong>Joined</strong><span>{{ $user->created_at ? \Carbon\Carbon::parse($user->created_at)->format('M j, Y') : 'N/A' }}</span></div>
                    <div><strong>Business</strong><span>{{ $user->business_name ?: 'None' }}</span></div>
                </div>
                <div class="profile-actions">
                    <a href="{{ route('home') }}">Back to Home</a>
                    <a href="{{ route('recommendation') }}">Recommendations</a>
                    <a href="{{ route('supplier.feed') }}">Newsfeed</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="profile-logout">Logout</button>
                    </form>
                </div>
            </section>

            <main class="profile-panel profile-content">
                @if (session('success'))
                    <div class="profile-message success">{{ session('success') }}</div>
                @endif
                @php($validationErrors = session()->get('errors'))
                @if ($validationErrors instanceof \Illuminate\Support\ViewErrorBag && $validationErrors->any())
                    <div class="profile-message error">{{ $validationErrors->first() }}</div>
                @endif

                @if ($user->business_name || $user->business_address)
                    <div class="profile-notice">
                        <strong>Application details</strong>
                        <span>Business: {{ $user->business_name ?: 'N/A' }}</span>
                        <span>Address: {{ $user->business_address ?: 'N/A' }}</span>
                        <div>
                            @if ($user->valid_id)
                                <a href="{{ asset($user->valid_id) }}" target="_blank" rel="noopener">View ID</a>
                            @endif
                            @if ($user->business_permit)
                                <a href="{{ asset($user->business_permit) }}" target="_blank" rel="noopener">View Permit</a>
                            @endif
                        </div>
                    </div>
                @endif

                @if ($user->status === 'approved' && $user->role !== 'client')
                    <div class="profile-notice">Your application is approved. You now have {{ $user->role }} access and can publish supplier services.</div>
                @else
                    <div class="profile-notice">Apply for supplier or coordinator access. Administration will review your application and update your profile status.</div>
                    <div class="profile-apply-buttons">
                        <button type="button" onclick="showApplication('coordinator')">Apply as Coordinator</button>
                        <button type="button" onclick="showApplication('supplier')">Apply as Supplier</button>
                    </div>

                    <form id="applicationForm" class="profile-application hidden" method="POST" action="{{ route('profile.application') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="apply_role" id="apply_role" value="supplier">
                        <input type="hidden" name="face_capture" id="face_capture">
                        <h2>Apply as <span id="applyRoleLabel">Supplier</span></h2>
                        <p class="profile-hint">Upload your business details and ID for verification.</p>
                        <div class="profile-form-row">
                            <label>Business / Organization Name<input type="text" name="business_name" value="{{ old('business_name', $user->business_name) }}" required></label>
                            <label>Business Address<input type="text" name="business_address" value="{{ old('business_address', $user->business_address) }}" required></label>
                        </div>
                        <div class="profile-form-row">
                            <label>Upload Valid ID<input type="file" name="valid_id" accept="image/*,.pdf"></label>
                            <label>Upload Business Permit<input type="file" name="business_permit" accept="image/*,.pdf"></label>
                        </div>
                        <label>Live Face Scan</label>
                        <div class="profile-video"><video id="faceVideo" autoplay playsinline></video></div>
                        <canvas id="faceCanvas" width="360" height="250" hidden></canvas>
                        <button type="button" class="profile-capture" onclick="captureFace()">Capture Face</button>
                        <p id="faceStatus" class="profile-hint">Camera will open when you choose an application type.</p>
                        <div class="profile-form-footer">
                            <button type="submit" class="profile-submit">Submit Application</button>
                            <button type="button" class="profile-cancel" onclick="hideApplication()">Cancel</button>
                        </div>
                    </form>
                @endif
            </main>
        </div>
    </div>

    <script>
        let faceStream = null;

        function showApplication(role) {
            document.getElementById('applicationForm').classList.remove('hidden');
            document.getElementById('apply_role').value = role;
            document.getElementById('applyRoleLabel').textContent = role === 'supplier' ? 'Supplier' : 'Coordinator';
            startFaceCamera();
        }

        function hideApplication() {
            document.getElementById('applicationForm').classList.add('hidden');
            if (faceStream) faceStream.getTracks().forEach(track => track.stop());
        }

        async function startFaceCamera() {
            try {
                faceStream = await navigator.mediaDevices.getUserMedia({ video: true });
                document.getElementById('faceVideo').srcObject = faceStream;
                document.getElementById('faceStatus').textContent = 'Camera ready. Capture your face when ready.';
            } catch (error) {
                document.getElementById('faceStatus').textContent = 'Camera unavailable. You can still submit the application with files.';
            }
        }

        function captureFace() {
            const video = document.getElementById('faceVideo');
            const canvas = document.getElementById('faceCanvas');
            if (!video.srcObject) return;
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
            document.getElementById('face_capture').value = canvas.toDataURL('image/png');
            document.getElementById('faceStatus').textContent = 'Face captured for admin verification.';
        }
    </script>
</body>
</html>

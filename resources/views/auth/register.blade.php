<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventIntel - Sign Up</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color-scheme: light;
        }
        *, *::before, *::after { box-sizing: border-box; }
        html, body { margin: 0; height: 100%; background: #f2f3f7; color: #222; }
        body { min-height: 100vh; }
        .container { display: flex; min-height: 100vh; }
        .left-panel, .right-panel { flex: 1; display: flex; align-items: center; justify-content: center; padding: 48px; }
        .left-panel { background: #ffffff; overflow: hidden; }
        .brand-wrapper { max-width: 560px; }
        .brand-title { margin: 0; font-size: clamp(3rem, 5vw, 5.2rem); line-height: 0.95; letter-spacing: -1px; color: #d4af37; font-weight: 800; }
        .brand-tagline { margin: 24px 0 0; font-size: 1.05rem; line-height: 1.9; color: #5f5f6f; max-width: 560px; }
        .right-panel { background: #dadada; overflow: hidden; }
        .signup-card { width: min(100%, 560px); max-height: calc(100vh - 96px); background: #eff2f7; border-radius: 28px; box-shadow: 0 28px 80px rgba(15,15,15,.12); padding: 42px 40px; overflow-y: auto; overflow-x: hidden; }
        .page-title { margin: 0 0 10px; font-size: 2.25rem; color: #232323; }
        .page-description { margin: 0 0 28px; color: #6c6c75; line-height: 1.7; font-size: 0.96rem; }
        .alert { margin: 0 0 24px; padding: 16px 18px; border-radius: 16px; font-size: 0.95rem; line-height: 1.5; }
        .alert.error { background: #ffe9e9; color: #7b1f1f; border: 1px solid #f5c4c4; }
        .alert.success { background: #e8f8eb; color: #1f5f35; border: 1px solid #bde3c4; }
        .alert.warning { background: #fff5d6; color: #7b5f18; border: 1px solid #f3d786; }
        .signup-form { display: grid; gap: 18px; }
        .input-wrapper { display: flex; align-items: center; gap: 12px; width: 100%; min-height: 54px; border-radius: 16px; border: 1px solid #d5d7df; background: #f8f9fb; padding: 0 14px; }
        .input-wrapper:focus-within { border-color: #c1b06f; box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.12); }
        .icon { color: #9a9aa5; font-size: 1rem; width: 24px; text-align: center; }
        .input-field, .select-field { flex: 1; width: 100%; border: none; background: transparent; padding: 14px 0; font-size: 1rem; color: #232323; outline: none; }
        .select-field { appearance: none; }
        .input-field::placeholder { color: #a2a2ad; }
        .toggle-password { color: #8f8f9b; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; }
        .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; }
        .form-group { min-width: 0; }
        .section-title { font-size: 13px; font-weight: 600; color: #1a1a1a; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 12px; }
        .info-box { padding: 16px; border-radius: 16px; background: rgba(243,197,71,.12); border: 1px solid rgba(243,197,71,.2); color: #111; line-height: 1.6; font-size: 0.95rem; }
        .submit-button { width: 100%; padding: 16px 18px; border: none; border-radius: 16px; background: linear-gradient(135deg, #e0b83f, #c78d12); color: #fff; font-size: 1rem; font-weight: 700; cursor: pointer; }
        .signup-footer { margin-top: 22px; text-align: center; font-size: 0.96rem; color: #5f5f6f; }
        .signup-footer a { color: #b48f14; font-weight: 700; text-decoration: none; }
        .signup-footer a:hover { text-decoration: underline; }
        .privacy-label { font-size: 0.94rem; color: #4a4a55; line-height: 1.6; }
        .privacy-label a { color: #b48f14; text-decoration: underline; }
        @media (max-width: 920px) { .container { flex-direction: column; } .left-panel, .right-panel { padding: 32px 24px; } .left-panel { order: 2; } .right-panel { order: 1; } }
        @media (max-width: 760px) { .signup-card { width: 100%; padding: 32px 22px; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <div class="brand-wrapper">
                <h1 class="brand-title">EventIntel</h1>
                <p class="brand-tagline">EventIntel guides every decision with smart recommendations and AI generated event flow.</p>
            </div>
        </div>

        <div class="right-panel">
            <div class="signup-card">
                <h1 class="page-title">Create Account</h1>
                <p class="page-description">Sign up for EventIntel to manage your events, suppliers, and recommendations in one beautiful interface.</p>

                @if ($errors->any())
                    <div class="alert error">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form class="signup-form" method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="form-row">
                        <div class="form-group">
                            <div class="input-wrapper">
                                <i class="icon fas fa-user"></i>
                                <input type="text" name="first_name" value="{{ old('first_name') }}" placeholder="First Name" class="input-field" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-wrapper">
                                <i class="icon fas fa-user"></i>
                                <input type="text" name="middle_initial" value="{{ old('middle_initial') }}" placeholder="Middle Initial" class="input-field" maxlength="1">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-wrapper">
                                <i class="icon fas fa-user"></i>
                                <input type="text" name="last_name" value="{{ old('last_name') }}" placeholder="Last Name" class="input-field" required>
                            </div>
                        </div>
                    </div>

                    <div class="input-wrapper">
                        <i class="icon fas fa-envelope"></i>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="Email Address" class="input-field" required>
                    </div>

                    <div class="input-wrapper">
                        <i class="icon fas fa-user-circle"></i>
                        <input type="text" name="username" value="{{ old('username') }}" placeholder="Username" class="input-field" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <div class="input-wrapper">
                                <i class="icon fas fa-birthday-cake"></i>
                                <input type="number" name="age" value="{{ old('age') }}" placeholder="Age" class="input-field" min="18" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-wrapper">
                                <i class="icon fas fa-venus-mars"></i>
                                <select class="input-field select-field" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="input-wrapper">
                        <i class="icon fas fa-phone"></i>
                        <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="Phone Number" class="input-field" required>
                    </div>

                    <div>
                        <p class="section-title">Address Information</p>
                        <div class="form-row">
                            <div class="form-group">
                                <div class="input-wrapper">
                                    <i class="icon fas fa-map-pin"></i>
                                    <select name="province" id="province" class="input-field select-field" required>
                                        <option value="Pampanga">Pampanga</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="input-wrapper">
                                    <i class="icon fas fa-map-pin"></i>
                                    <select name="municipality" id="municipality" class="input-field select-field" required>
                                        <option value="Apalit">Apalit</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <div class="input-wrapper">
                                    <i class="icon fas fa-map-pin"></i>
                                    <select name="barangay" id="barangay" class="input-field select-field" required>
                                        <option value="">Select Barangay</option>
                                        <option value="Balucuc">Balucuc</option>
                                        <option value="Calantipe">Calantipe</option>
                                        <option value="Cansinala">Cansinala</option>
                                        <option value="Capalangan">Capalangan</option>
                                        <option value="Colgante">Colgante</option>
                                        <option value="Paligui">Paligui</option>
                                        <option value="Poblacion">Poblacion</option>
                                        <option value="San Juan">San Juan</option>
                                        <option value="San Vicente">San Vicente</option>
                                        <option value="Santa Cruz">Santa Cruz</option>
                                        <option value="Succad">Succad</option>
                                        <option value="Tabuyuc">Tabuyuc</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="input-wrapper">
                                    <i class="icon fas fa-map-pin"></i>
                                    <input type="text" name="postal_code" value="2016" placeholder="Postal Code" class="input-field" required readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="role" value="client">

                    <div class="info-box">
                        <strong>Note:</strong> You are creating a client account. Supplier and coordinator applications are available later from your profile after login.
                    </div>

                    <div class="input-wrapper">
                        <i class="icon fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Password" class="input-field" required>
                        <span class="toggle-password"><i class="fas fa-eye"></i></span>
                    </div>

                    <div class="input-wrapper">
                        <i class="icon fas fa-lock"></i>
                        <input type="password" id="confirm-password" name="password_confirmation" placeholder="Confirm Password" class="input-field" required>
                        <span class="toggle-password"><i class="fas fa-eye"></i></span>
                    </div>

                    <label class="privacy-label">
                        <input type="checkbox" name="data_privacy_consent" required style="margin-right:10px;vertical-align:middle;">
                        I have read and agree to the Data Privacy Policy, and I consent to EventIntel collecting and processing my personal information in accordance with the Data Privacy Act of 2012 (RA 10173).
                    </label>

                    <button type="submit" class="submit-button">Sign Up</button>
                </form>

                <div class="signup-footer">Already have an account? <a href="{{ route('login') }}">Log In</a></div>
            </div>
        </div>
    </div>

    <script>
        const passwordFields = document.querySelectorAll('#password, #confirm-password');
        document.querySelectorAll('.toggle-password').forEach((toggle) => {
            toggle.addEventListener('click', function () {
                const field = this.parentElement.querySelector('input');
                const icon = this.querySelector('i');
                const isPassword = field.type === 'password';
                field.type = isPassword ? 'text' : 'password';
                icon.classList.toggle('fa-eye', !isPassword);
                icon.classList.toggle('fa-eye-slash', isPassword);
            });
        });

        document.querySelector('.signup-form').addEventListener('submit', function (e) {
            const pw = document.getElementById('password').value;
            const confirm = document.getElementById('confirm-password').value;
            if (pw !== confirm) {
                e.preventDefault();
                alert('Passwords do not match.');
            }
        });
    </script>
</body>
</html>


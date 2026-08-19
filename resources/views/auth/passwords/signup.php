<?php
function esc($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

$error = $_GET['error'] ?? '';
$message = '';
$messageType = 'error';
if ($error === 'missing') {
    $message = 'Please fill in all required fields.';
}
elseif ($error === 'exists') {
    $message = 'Username or email already registered.';
}
elseif (isset($_GET['registered'])) {
    $message = 'Account created! You can now login.';
    $messageType = 'success';
}
elseif (isset($_GET['pending'])) {
    $message = 'Account created! Awaiting admin approval.';
    $messageType = 'warning';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventIntel - Sign Up</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        :root {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color-scheme: light;
        }
        *, *::before, *::after {
            box-sizing: border-box;
        }
        html,
        body {
            margin: 0;
            height: 100%;
            overflow: hidden;
            background: #f2f3f7;
            color: #222;
        }
        body {
            min-height: 100vh;
        }
        .container {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }
        .left-panel,
        .right-panel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px;
            overflow: hidden;
        }
        .left-panel {
            background: #ffffff;
            color: #222222;
        }
        .brand-wrapper {
            max-width: 560px;
        }
        .brand-title {
            margin: 0;
            font-size: clamp(3rem, 5vw, 5.2rem);
            line-height: 0.95;
            letter-spacing: -1px;
            color: #d4af37;
            font-weight: 800;
        }
        .brand-tagline {
            margin: 24px 0 0;
            font-size: 1.05rem;
            line-height: 1.9;
            color: #5f5f6f;
            max-width: 560px;
        }
        .right-panel {
            background: #dadada;
        }
        .signup-card {
            width: min(100%, 560px);
            max-height: calc(100vh - 96px);
            background: #eff2f7;
            border-radius: 28px;
            box-shadow: 0 28px 80px rgba(15, 15, 15, 0.12);
            padding: 42px 40px;
            overflow: auto;
        }
        .page-title {
            margin: 0 0 10px;
            font-size: 2.25rem;
            color: #232323;
        }
        .page-description {
            margin: 0 0 28px;
            color: #6c6c75;
            line-height: 1.7;
            font-size: 0.96rem;
        }
        .alert {
            margin: 0 0 24px;
            padding: 16px 18px;
            border-radius: 16px;
            font-size: 0.95rem;
            line-height: 1.5;
        }
        .alert.error {
            background: #ffe9e9;
            color: #7b1f1f;
            border: 1px solid #f5c4c4;
        }
        .alert.success {
            background: #e8f8eb;
            color: #1f5f35;
            border: 1px solid #bde3c4;
        }
        .alert.warning {
            background: #fff5d6;
            color: #7b5f18;
            border: 1px solid #f3d786;
        }
        .signup-form {
            display: grid;
            gap: 18px;
        }
        .input-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            min-height: 54px;
            border-radius: 16px;
            border: 1px solid #d5d7df;
            background: #f8f9fb;
            padding: 0 14px;
        }
        .input-wrapper:focus-within {
            border-color: #c1b06f;
            box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.12);
        }
        .icon {
            color: #9a9aa5;
            font-size: 1rem;
            width: 24px;
            text-align: center;
        }
        .input-field,
        .select-field {
            flex: 1;
            width: 100%;
            border: none;
            background: transparent;
            padding: 14px 0;
            font-size: 1rem;
            color: #232323;
            outline: none;
        }
        .select-field {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            padding-right: 8px;
        }
        .input-field::placeholder {
            color: #a2a2ad;
        }
        .toggle-password {
            color: #8f8f9b;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
        }
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        }
        .form-group {
            min-width: 0;
        }
        .section-title {
            font-size: 13px;
            font-weight: 600;
            color: #1a1a1a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 12px;
        }
        .info-box {
            padding: 16px;
            border-radius: 16px;
            background: rgba(243,197,71,0.12);
            border: 1px solid rgba(243,197,71,0.2);
            color: #111;
            line-height: 1.6;
            font-size: 0.95rem;
        }
        .submit-button {
            width: 100%;
            padding: 16px 18px;
            border: none;
            border-radius: 16px;
            background: linear-gradient(135deg, #e0b83f, #c78d12);
            color: #ffffff;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        .submit-button:hover {
            transform: translateY(-1px);
        }
        .signup-footer {
            margin-top: 22px;
            text-align: center;
            font-size: 0.96rem;
            color: #5f5f6f;
        }
        .signup-footer a {
            color: #b48f14;
            font-weight: 700;
            text-decoration: none;
        }
        .signup-footer a:hover {
            text-decoration: underline;
        }
        .privacy-label {
            font-size: 0.94rem;
            color: #4a4a55;
            line-height: 1.6;
        }
        .privacy-label a {
            color: #b48f14;
            text-decoration: underline;
        }
        @media (max-width: 920px) {
            .container {
                flex-direction: column;
            }
            .left-panel,
            .right-panel {
                padding: 32px 24px;
            }
            .left-panel {
                order: 2;
            }
            .right-panel {
                order: 1;
            }
        }
        @media (max-width: 760px) {
            .signup-card {
                width: 100%;
                padding: 32px 22px;
            }
        }
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

                <?php if ($message): ?>
                    <div class="alert <?= htmlspecialchars($messageType) ?>"><?= esc($message) ?></div>
                <?php endif; ?>

                <form class="signup-form" action="../auth/register.php" method="POST" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <div class="input-wrapper">
                                <i class="icon fas fa-user"></i>
                                <input type="text" name="first_name" placeholder="First Name" class="input-field" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-wrapper">
                                <i class="icon fas fa-user"></i>
                                <input type="text" name="middle_initial" placeholder="Middle Initial" class="input-field" maxlength="1">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-wrapper">
                                <i class="icon fas fa-user"></i>
                                <input type="text" name="last_name" placeholder="Last Name" class="input-field" required>
                            </div>
                        </div>
                    </div>

                    <div class="input-wrapper">
                        <i class="icon fas fa-envelope"></i>
                        <input type="email" name="email" placeholder="Email Address" class="input-field" required>
                    </div>

                    <div class="input-wrapper">
                        <i class="icon fas fa-user-circle"></i>
                        <input type="text" name="username" placeholder="Username" class="input-field" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <div class="input-wrapper">
                                <i class="icon fas fa-birthday-cake"></i>
                                <input type="number" name="age" placeholder="Age" class="input-field" min="18" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-wrapper">
                                <i class="icon fas fa-venus-mars"></i>
                                <select class="input-field select-field" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="input-wrapper">
                        <i class="icon fas fa-phone"></i>
                        <input type="tel" name="phone" placeholder="Phone Number" class="input-field" required>
                    </div>

                    <div>
                        <p class="section-title">Address Information</p>
                        <div class="form-row">
                            <div class="form-group">
                                <div class="input-wrapper">
                                    <i class="icon fas fa-map-pin"></i>
                                    <select name="province" id="province" class="input-field select-field" required></select>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="input-wrapper">
                                    <i class="icon fas fa-map-pin"></i>
                                    <select name="municipality" id="municipality" class="input-field select-field" required disabled></select>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <div class="input-wrapper">
                                    <i class="icon fas fa-map-pin"></i>
                                    <select name="barangay" id="barangay" class="input-field select-field" required disabled></select>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="input-wrapper">
                                    <i class="icon fas fa-map-pin"></i>
                                    <input type="text" name="postal_code" placeholder="Postal Code" class="input-field" required readonly>
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
                        <input type="password" id="confirm-password" name="confirm_password" placeholder="Confirm Password" class="input-field" required>
                        <span class="toggle-password"><i class="fas fa-eye"></i></span>
                    </div>

                    <label class="privacy-label">
                        <input type="checkbox" name="data_privacy_consent" id="data-privacy-consent" required style="margin-right:10px;vertical-align:middle;">
                        I have read and agree to the <a href="#" onclick="document.getElementById('privacy-modal').style.display='flex'; return false;">Data Privacy Policy</a>, and I consent to EventIntel collecting and processing my personal information in accordance with the Data Privacy Act of 2012 (RA 10173).
                    </label>

                    <button type="submit" class="submit-button">Sign Up</button>
                </form>

                <div class="signup-footer">Already have an account? <a href="index.php">Log In</a></div>
            </div>
        </div>
    </div>

    <div id="privacy-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:999;align-items:center;justify-content:center;padding:20px;">
        <div style="background:#fff;border-radius:16px;max-width:520px;width:100%;max-height:80vh;overflow-y:auto;padding:28px;">
            <h3 style="margin-top:0;color:#222;">Data Privacy Policy</h3>
            <p style="font-size:13px;line-height:1.6;color:#444;">EventIntel collects the personal information you provide during sign-up (name, contact details, address, and, for supplier/coordinator applications, valid ID, business permit, and a face photo) solely to create and verify your account, process bookings, and operate the platform's features, in accordance with the Data Privacy Act of 2012 (RA 10173).</p>
            <p style="font-size:13px;line-height:1.6;color:#444;">Your information will not be sold or shared with third parties outside of what is needed to deliver the service (e.g. connecting you with suppliers or coordinators for your event). You may request access to, correction of, or deletion of your personal data at any time by contacting the system administrator.</p>
            <button type="button" onclick="document.getElementById('privacy-modal').style.display='none';" class="submit-button" style="margin-top:16px;width:auto;">Close</button>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = event.target.closest('.toggle-password').querySelector('i');
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        document.querySelector('.signup-form').addEventListener('submit', function(e) {
            const pw = document.getElementById('password').value;
            const confirm = document.getElementById('confirm-password').value;
            if (pw !== confirm) {
                e.preventDefault();
                alert('Passwords do not match.');
            }
        });

        const provinceSelect = document.getElementById('province');
        const municipalitySelect = document.getElementById('municipality');
        const barangaySelect = document.getElementById('barangay');
        const postalInput = document.querySelector('input[name="postal_code"]');

        const apalitBarangays = [
            'Balucuc', 'Calantipe', 'Cansinala', 'Capalangan', 'Colgante',
            'Paligui', 'Poblacion', 'San Juan', 'San Vicente', 'Santa Cruz',
            'Succad', 'Tabuyuc'
        ];

        window.addEventListener('DOMContentLoaded', () => {
            const provOpt = document.createElement('option');
            provOpt.value = 'Pampanga';
            provOpt.textContent = 'Pampanga';
            provinceSelect.appendChild(provOpt);
            provinceSelect.value = 'Pampanga';

            const muniOpt = document.createElement('option');
            muniOpt.value = 'Apalit';
            muniOpt.textContent = 'Apalit';
            municipalitySelect.appendChild(muniOpt);
            municipalitySelect.value = 'Apalit';
            municipalitySelect.disabled = false;

            populateBarangays();
            postalInput.value = '2016';
            postalInput.readOnly = true;
        });

        function populateBarangays() {
            barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
            apalitBarangays.sort().forEach(brgy => {
                const opt = document.createElement('option');
                opt.value = brgy;
                opt.textContent = brgy;
                barangaySelect.appendChild(opt);
            });
            barangaySelect.disabled = false;
        }
    </script>
</body>
</html>

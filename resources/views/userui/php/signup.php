<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventIntel - Sign Up</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="container">
        <!-- Left Side - Branding -->
        <div class="left-section">
            <div class="branding-content">
                <h1 class="brand-title">EventIntel</h1>
                <p class="brand-tagline">EventIntel guides every decision with smart recommendations and Ai Generated Event Flow</p>
            </div>
        </div>

        <!-- Right Side - Sign Up Form -->
        <div class="right-section">
            <div class="login-form-container">
                <h2 class="welcome-title">Create Account</h2>

                <form class="login-form" action="../../auth/register.php" method="POST">
                    <!-- Name Fields -->
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
                                <input type="text" name="last_name" placeholder="Last Name" class="input-field" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-wrapper">
                                <i class="icon fas fa-user"></i>
                                <input type="text" name="middle_initial" placeholder="Middle Initial" class="input-field" maxlength="1">
                            </div>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <div class="input-wrapper">
                            <i class="icon fas fa-envelope"></i>
                            <input type="email" name="email" placeholder="Email Address" class="input-field" required>
                        </div>
                    </div>

                    <!-- Username -->
                    <div class="form-group">
                        <div class="input-wrapper">
                            <i class="icon fas fa-user-circle"></i>
                            <input type="text" name="username" placeholder="Username" class="input-field" required>
                        </div>
                    </div>

                    <!-- Age and Gender -->
                    <div class="form-row">
                        <div class="form-group">
                            <div class="input-wrapper">
                                <i class="icon fas fa-birthday-cake"></i>
                                <input type="number" name="age" placeholder="Age" class="input-field" min="18" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <select class="input-field select-field" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <!-- Phone Number -->
                    <div class="form-group">
                        <div class="input-wrapper">
                            <i class="icon fas fa-phone"></i>
                            <input type="tel" name="phone" placeholder="Phone Number" class="input-field" required>
                        </div>
                    </div>

                    <!-- Address Fields -->
                    <div class="address-section">
                        <p class="section-title">Address Information</p>
                        <div class="form-row">
                            <div class="form-group">
                                <div class="input-wrapper">
                                    <i class="icon fas fa-map-pin"></i>
                                    <input type="text" name="province" placeholder="Province" class="input-field" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="input-wrapper">
                                    <i class="icon fas fa-map-pin"></i>
                                    <input type="text" name="municipality" placeholder="Municipality" class="input-field" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <div class="input-wrapper">
                                    <i class="icon fas fa-map-pin"></i>
                                    <input type="text" name="barangay" placeholder="Barangay" class="input-field" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="input-wrapper">
                                    <i class="icon fas fa-map-pin"></i>
                                    <input type="text" name="postal_code" placeholder="Postal Code" class="input-field" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="role" value="client">
                    <div class="info-box" style="margin:18px 0;padding:16px;border-radius:16px;background:rgba(243,197,71,0.12);border:1px solid rgba(243,197,71,0.2);color:#111;">
                        <strong>Note:</strong> You are creating a client account. Supplier and coordinator applications are available later from your profile after login.
                    </div>

                    <!-- Passwords -->
                    <div class="form-group">
                        <div class="input-wrapper">
                            <i class="icon fas fa-lock"></i>
                            <input type="password" name="password" placeholder="Password" class="input-field" id="password" required>
                            <span class="toggle-password" onclick="togglePassword('password')"><i class="fas fa-eye"></i></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-wrapper">
                            <i class="icon fas fa-lock"></i>
                            <input type="password" name="confirm_password" placeholder="Confirm Password" class="input-field" id="confirm-password" required>
                            <span class="toggle-password" onclick="togglePassword('confirm-password')"><i class="fas fa-eye"></i></span>
                        </div>
                    </div>

                    <div class="form-group privacy-consent" style="margin:18px 0 8px;">
                        <label style="display:flex;align-items:flex-start;gap:10px;font-size:13px;line-height:1.5;color:#333;cursor:pointer;">
                            <input type="checkbox" name="data_privacy_consent" id="data-privacy-consent" required style="margin-top:3px;width:16px;height:16px;flex-shrink:0;">
                            <span>
                                I have read and agree to the
                                <a href="#" onclick="document.getElementById('privacy-modal').style.display='flex'; return false;" style="color:#d4a017;text-decoration:underline;">Data Privacy Policy</a>,
                                and I consent to EventIntel collecting and processing my personal information in accordance with the Data Privacy Act of 2012 (RA 10173).
                            </span>
                        </label>
                    </div>

                    <button type="submit" class="login-button">Sign Up</button>
                </form>

                <div class="signup-section">
                    <p class="signup-text">Already have an account? <a href="index.php" class="signup-link">Log In</a></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Privacy Policy Modal -->
    <div id="privacy-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:999;align-items:center;justify-content:center;padding:20px;">
        <div style="background:#fff;border-radius:16px;max-width:520px;width:100%;max-height:80vh;overflow-y:auto;padding:28px;">
            <h3 style="margin-top:0;color:#222;">Data Privacy Policy</h3>
            <p style="font-size:13px;line-height:1.6;color:#444;">
                EventIntel collects the personal information you provide during sign-up (name, contact
                details, address, and, for supplier/coordinator applications, valid ID, business permit,
                and a face photo) solely to create and verify your account, process bookings, and operate
                the platform's features, in accordance with the Data Privacy Act of 2012 (RA 10173).
            </p>
            <p style="font-size:13px;line-height:1.6;color:#444;">
                Your information will not be sold or shared with third parties outside of what is needed
                to deliver the service (e.g. connecting you with suppliers or coordinators for your event).
                You may request access to, correction of, or deletion of your personal data at any time by
                contacting the system administrator.
            </p>
            <button type="button" onclick="document.getElementById('privacy-modal').style.display='none';" class="login-button" style="margin-top:10px;">Close</button>
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

        document.querySelector('.login-form').addEventListener('submit', function(e) {
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

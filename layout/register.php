<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/theme/mytheme/lib.php');

$sesskey = sesskey();
$templatecontext = theme_mytheme_get_base_context();
$logo = !empty($templatecontext['setting']['logo']) ? $templatecontext['setting']['logo'] : false;
$PAGE->requires->css('/theme/mytheme/styles/login.css');


echo $OUTPUT->doctype();
?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $PAGE->title; ?></title>
    <?php echo $OUTPUT->standard_head_html(); ?>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap"
        rel="stylesheet">
    <style>
        .amd-error-box {
            background: #ff3b3b;
            color: white;
            padding: 16px 20px;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
            margin-bottom: 20px;
        }

        /*
        .amd-error-box ul {
            list-style-type: disc;
            margin: 5px 0 0 0;
        }

        .amd-error-box li {
            margin-bottom: 4px;
        } */
    </style>

</head>

<body>
    <main class="p-4">
        <div class="amd-lms-login-main-container amd-lms-login-main-container-register">
            <!-- Form Container -->
            <div class="amd-lms-login-form-container">
                <div class="amd-login-top-part">

                    <!-- Form Controls (Buttons) -->
                    <div class="amd-lms-login-form-controls">
                        <a href="?login=1"><button class="amd-lms-login-control-btn"
                                data-form="login">Login</button></a>
                        <a href="?register=1"> <button class="amd-lms-login-control-btn amd-lms-login-active-btn"
                                data-form="register">Register</button>
                        </a>
                    </div>
                </div>
                <!-- Forms Wrapper -->
                <div class="amd-lms-login-forms-wrapper">

                    <?php
                    $errors = $_SESSION['register_errors'] ?? [];
                    $formdata = $_SESSION['register_form_data'] ?? [];
                    unset($_SESSION['register_errors']);
                    unset($_SESSION['register_form_data']);
                    ?>

                    <!-- Global Error Box with List -->
                    <?php if (!empty($errors)): ?>
                        <div id="global-error-box" class="amd-error-box"
                            style="display:flex; flex-direction: column; align-items: flex-start;">
                            <div style="display: flex; align-items: center; gap: 10px; width: 100%; margin-bottom: 8px;">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Please correct the following errors:</strong>
                            </div>

                            <ul style="margin: 0; padding-left: 25px; width: 100%; font-size: 0.95rem;">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Register Form -->
                    <!-- Register Form -->
                    <form action="<?php echo $CFG->wwwroot; ?>/theme/mytheme/pages/register.php" method="post"
                        id="register-form" class="amd-lms-login-form amd-lms-login-active overflow-auto grid-container">
                        <input type="hidden" name="sesskey" value="<?php echo $sesskey; ?>">

                        <h2>Create Account!</h2>
                        <p>Fill in your details to get started with E-learning.</p>

                        <!-- Personal Information Section -->
                        <div style="width: 100%; text-align: left; margin-bottom: 10px;">
                            <h5 style="color: #2c2c2c;margin-bottom: 10px;"><i class="fas fa-user"></i> Personal
                                Information</h5>
                        </div>

                        <div class="grid-row grid-3">
                            <div class="amd-lms-login-input-group">
                                <input type="text" name="firstname" id="FirstName"
                                    value="<?= htmlspecialchars($formdata['firstname'] ?? '') ?>" required
                                    placeholder=" ">
                                <label for="FirstName">First Name<span class="text-danger mx-1">*</span></label>
                            </div>
                            <div class="amd-lms-login-input-group">
                                <input type="text" name="middle_name" id="MiddleName"
                                    value="<?= htmlspecialchars($formdata['middle_name'] ?? '') ?>" placeholder=" ">
                                <label for="MiddleName">Middle Name</label>
                            </div>
                            <div class="amd-lms-login-input-group">
                                <input type="text" name="lastname" id="LastName"
                                    value="<?= htmlspecialchars($formdata['lastname'] ?? '') ?>" required
                                    placeholder=" ">
                                <label for="LastName">Last Name<span class="text-danger mx-1">*</span></label>
                            </div>
                        </div>

                        <div class="grid-row grid-2">
                            <div class="amd-lms-login-input-group">
                                <select name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="male" <?= ($formdata['gender'] ?? '') === 'male' ? 'selected' : '' ?>>
                                        Male</option>
                                    <option value="female" <?= ($formdata['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                                    <option value="other" <?= ($formdata['gender'] ?? '') === 'other' ? 'selected' : '' ?>>
                                        Other</option>
                                </select>
                                <label>Gender<span class="text-danger mx-1">*</span></label>
                            </div>

                            <div class="amd-lms-login-input-group">
                                <select name="employed">
                                    <option value="">Select</option>
                                    <option value="yes" <?= ($formdata['employed'] ?? '') === 'yes' ? 'selected' : '' ?>>
                                        Yes</option>
                                    <option value="no" <?= ($formdata['employed'] ?? '') === 'no' ? 'selected' : '' ?>>No
                                    </option>
                                </select>
                                <label>Employed?</label>
                            </div>
                        </div>

                        <div class="grid-row grid-2">
                            <div class="amd-lms-login-input-group">
                                <input type="text" name="citizenship_no"
                                    value="<?= htmlspecialchars($formdata['citizenship_no'] ?? '') ?>" placeholder=" ">
                                <label>Citizenship No.</label>
                            </div>

                            <div class="amd-lms-login-input-group">
                                <select name="citizenship_district" id="citizenship_district_dropdown">
                                    <option value="">Select Issued District</option>
                                </select>
                                <label>Citizenship Issued District</label>
                            </div>
                        </div>

                        <div class="grid-row grid-2">
                            <div class="amd-lms-login-input-group">
                                <input type="text" name="nid_no" id="NIDNo"
                                    value="<?= htmlspecialchars($formdata['nid_no'] ?? '') ?>" placeholder=" "
                                    maxlength="16">
                                <label for="NIDNo">NID No</label>
                            </div>
                            <div class="amd-lms-login-input-group">
                                <input type="text" name="pan_no" id="PANNo"
                                    value="<?= htmlspecialchars($formdata['pan_no'] ?? '') ?>" placeholder=" "
                                    maxlength="9" required>
                                <label for="PANNo">PAN No (9 digits)</label>
                            </div>
                        </div>

                        <div class="grid-row grid-1">
                            <div class="amd-lms-login-input-group">
                                <select name="ethnicity" id="ethnicity-select" class="ethnicity-dropdown" required>
                                    <option value="">Select Ethnicity</option>
                                    <option value="brahmin" <?= ($formdata['ethnicity'] ?? '') === 'brahmin' ? 'selected' : '' ?>>Brahmin</option>
                                    <option value="chhetri" <?= ($formdata['ethnicity'] ?? '') === 'chhetri' ? 'selected' : '' ?>>Chhetri</option>
                                    <option value="janajati" <?= ($formdata['ethnicity'] ?? '') === 'janajati' ? 'selected' : '' ?>>Janajati</option>
                                    <option value="indigenous" <?= ($formdata['ethnicity'] ?? '') === 'indigenous' ? 'selected' : '' ?>>Indigenous</option>
                                    <option value="madheshi" <?= ($formdata['ethnicity'] ?? '') === 'madheshi' ? 'selected' : '' ?>>Madheshi</option>
                                    <option value="dalit" <?= ($formdata['ethnicity'] ?? '') === 'dalit' ? 'selected' : '' ?>>Dalit</option>
                                    <option value="muslim" <?= ($formdata['ethnicity'] ?? '') === 'muslim' ? 'selected' : '' ?>>Muslim</option>
                                    <option value="others" <?= ($formdata['ethnicity'] ?? '') === 'others' ? 'selected' : '' ?>>Others</option>
                                </select>
                                <label>Ethnicity<span class="text-danger mx-1">*</span></label>
                                <input type="text" id="ethnicity-others-text" name="ethnicity_others"
                                    value="<?= htmlspecialchars($formdata['ethnicity_others'] ?? '') ?>"
                                    placeholder="Specify other ethnicity"
                                    style="margin-top: 10px; width: 100%; padding: 8px; background: #ffffff36; border: 1px solid #00164578; color: var(--amd-dark); display: none;">
                            </div>
                        </div>

                        <!-- Permanent Address Section -->
                        <div style="width: 100%; text-align: left; margin: 20px 0;" class="">
                            <h5 style="color: var(--amd-dark); margin-bottom: 10px;"><i class="fas fa-home"></i>
                                Permanent Address Details</h5>
                        </div>

                        <div class="grid-row grid-3">
                            <div class="amd-lms-login-input-group">
                                <select name="province_id" class="state-dropdown" required>
                                    <option value="">Select Province</option>
                                    <option value="1" <?= ($formdata['province_id'] ?? '') == '1' ? 'selected' : '' ?>>
                                        Koshi Province</option>
                                    <option value="2" <?= ($formdata['province_id'] ?? '') == '2' ? 'selected' : '' ?>>
                                        Madhesh Province</option>
                                    <option value="3" <?= ($formdata['province_id'] ?? '') == '3' ? 'selected' : '' ?>>
                                        Bagmati Province</option>
                                    <option value="4" <?= ($formdata['province_id'] ?? '') == '4' ? 'selected' : '' ?>>
                                        Gandaki Province</option>
                                    <option value="5" <?= ($formdata['province_id'] ?? '') == '5' ? 'selected' : '' ?>>
                                        Lumbini Province</option>
                                    <option value="6" <?= ($formdata['province_id'] ?? '') == '6' ? 'selected' : '' ?>>
                                        Karnali Province</option>
                                    <option value="7" <?= ($formdata['province_id'] ?? '') == '7' ? 'selected' : '' ?>>
                                        Sudurpashchim Province</option>
                                </select>
                                <label>Permanent Province<span class="text-danger mx-1">*</span></label>
                            </div>
                            <div class="amd-lms-login-input-group">
                                <select name="district_id" class="district-dropdown" required>
                                    <option value="">Select District</option>
                                </select>
                                <label>Permanent District<span class="text-danger mx-1">*</span></label>
                            </div>
                            <div class="amd-lms-login-input-group">
                                <select name="municipality_id" class="municipality-dropdown" required>
                                    <option value="">Select Municipality</option>
                                </select>
                                <label>Permanent Municipality<span class="text-danger mx-1">*</span></label>
                            </div>
                        </div>

                        <div class="grid-row grid-2">
                            <div class="amd-lms-login-input-group">
                                <input type="number" name="ward"
                                    value="<?= htmlspecialchars($formdata['ward'] ?? '') ?>" required placeholder=" ">
                                <label>Permanent Ward<span class="text-danger mx-1">*</span></label>
                            </div>
                            <div class="amd-lms-login-input-group">
                                <input type="text" name="tole" value="<?= htmlspecialchars($formdata['tole'] ?? '') ?>"
                                    placeholder=" ">
                                <label>Permanent Tole / Street Name</label>
                            </div>
                        </div>

                        <!-- Same Address Checkbox -->
                        <div class="amd-checkbox-group" style="max-width: 320px; margin: 20px 0;">
                            <label
                                style="font-size: 1rem; color: var(--amd-dark); cursor: pointer; display: flex; align-items: center;">
                                <input type="checkbox" id="sameAddress" class="amd-custom-input form-check-input"
                                    style="margin-right: 10px;">
                                Temporary Address same as Permanent
                            </label>
                        </div>

                        <!-- Temporary Address Section -->
                        <div style="width: 100%; text-align: left; margin: 20px 0;">
                            <h5 style="color: var(--amd-dark); margin-bottom: 10px;"><i
                                    class="fas fa-map-marker-alt"></i> Temporary Address Details</h5>
                        </div>
                        <div class="grid-row grid-3">
                            <div class="amd-lms-login-input-group">
                                <select name="temp_province_id" class="temp-state-dropdown" required>
                                    <option value="">Select Province</option>
                                    <option value="1" <?= ($formdata['temp_province_id'] ?? '') == '1' ? 'selected' : '' ?>>Koshi Province</option>
                                    <option value="2" <?= ($formdata['temp_province_id'] ?? '') == '2' ? 'selected' : '' ?>>Madhesh Province</option>
                                    <option value="3" <?= ($formdata['temp_province_id'] ?? '') == '3' ? 'selected' : '' ?>>Bagmati Province</option>
                                    <option value="4" <?= ($formdata['temp_province_id'] ?? '') == '4' ? 'selected' : '' ?>>Gandaki Province</option>
                                    <option value="5" <?= ($formdata['temp_province_id'] ?? '') == '5' ? 'selected' : '' ?>>Lumbini Province</option>
                                    <option value="6" <?= ($formdata['temp_province_id'] ?? '') == '6' ? 'selected' : '' ?>>Karnali Province</option>
                                    <option value="7" <?= ($formdata['temp_province_id'] ?? '') == '7' ? 'selected' : '' ?>>Sudurpashchim Province</option>
                                </select>
                                <label>Temporary Province<span class="text-danger mx-1">*</span></label>
                            </div>
                            <div class="amd-lms-login-input-group">
                                <select name="temp_district_id" class="temp-district-dropdown" required>
                                    <option value="">Select District</option>
                                </select>
                                <label>Temporary District<span class="text-danger mx-1">*</span></label>
                            </div>
                            <div class="amd-lms-login-input-group">
                                <select name="temp_municipality_id" class="temp-municipality-dropdown" required>
                                    <option value="">Select Municipality</option>
                                </select>
                                <label>Temporary Municipality<span class="text-danger mx-1">*</span></label>
                            </div>
                        </div>
                        <div class="grid-row grid-2">
                            <div class="amd-lms-login-input-group">
                                <input type="number" name="temp_ward"
                                    value="<?= htmlspecialchars($formdata['temp_ward'] ?? '') ?>" required
                                    placeholder=" ">
                                <label>Temporary Ward <span class="text-danger mx-1">*</span></label>
                            </div>
                            <div class="amd-lms-login-input-group">
                                <input type="text" name="temp_tole"
                                    value="<?= htmlspecialchars($formdata['temp_tole'] ?? '') ?>" placeholder=" ">
                                <label>Temporary Tole / Street Name</label>
                            </div>
                        </div>

                        <!-- Contact & Professional Section -->
                        <div style="width: 100%; text-align: left; margin: 30px 0 20px 0;">
                            <h5 style="color: var(--amd-dark); margin-bottom: 10px;"><i class="fas fa-address-card"></i>
                                Contact & Professional Information</h5>
                        </div>
                        <div class="grid-row grid-3">
                            <div class="amd-lms-login-input-group">
                                <input type="tel" name="phone_number"
                                    value="<?= htmlspecialchars($formdata['phone_number'] ?? '') ?>" placeholder=" ">
                                <label>Phone Number</label>
                            </div>
                            <div class="amd-lms-login-input-group">
                                <input type="tel" name="mobile_number"
                                    value="<?= htmlspecialchars($formdata['mobile_number'] ?? '') ?>" placeholder=" ">
                                <label>Mobile Number</label>
                            </div>
                            <div class="amd-lms-login-input-group">
                                <input type="email" name="email"
                                    value="<?= htmlspecialchars($formdata['email'] ?? '') ?>" required placeholder=" ">
                                <label>Email Address<span class="text-danger mx-1">*</span></label>
                            </div>
                            <div class="amd-lms-login-input-group">
                                <input type="email" name="email2"
                                    value="<?= htmlspecialchars($formdata['email2'] ?? '') ?>" required placeholder=" ">
                                <label>Confirm Email</label>
                            </div>

                            <div class="amd-lms-login-input-group">
                                <select name="age_group" id="AgeGroup" required>
                                    <option value="">-- Select Your Age Group --</option>
                                    <option value="1" <?= ($formdata['age_group'] ?? '') == '1' ? 'selected' : '' ?>>Below
                                        18</option>
                                    <option value="2" <?= ($formdata['age_group'] ?? '') == '2' ? 'selected' : '' ?>>18-25
                                    </option>
                                    <option value="3" <?= ($formdata['age_group'] ?? '') == '3' ? 'selected' : '' ?>>26-40
                                    </option>
                                    <option value="4" <?= ($formdata['age_group'] ?? '') == '4' ? 'selected' : '' ?>>40+
                                    </option>
                                </select>
                                <label for="AgeGroup">Age Group<span class="text-danger mx-1">*</span></label>
                            </div>
                            <div class="amd-lms-login-input-group">
                                <input type="text" name="organization_name"
                                    value="<?= htmlspecialchars($formdata['organization_name'] ?? '') ?>"
                                    placeholder=" ">
                                <label>Organization Name</label>
                            </div>
                            <div class="amd-lms-login-input-group">
                                <input type="text" name="qualification"
                                    value="<?= htmlspecialchars($formdata['qualification'] ?? '') ?>" placeholder=" ">
                                <label>Qualification</label>
                            </div>
                            <div class="amd-lms-login-input-group">
                                <input type="text" name="designation"
                                    value="<?= htmlspecialchars($formdata['designation'] ?? '') ?>" placeholder=" ">
                                <label>Designation</label>
                            </div>
                            <div class="amd-lms-login-input-group">
                                <input type="text" name="expertise"
                                    value="<?= htmlspecialchars($formdata['expertise'] ?? '') ?>" placeholder=" ">
                                <label>Expertise</label>
                            </div>
                            <div class="amd-lms-login-input-group">
                                <input type="number" name="years_experience" min="0"
                                    value="<?= htmlspecialchars($formdata['years_experience'] ?? '') ?>"
                                    placeholder=" ">
                                <label>Years of Experience</label>
                            </div>
                        </div>

                        <!-- Login Information Section -->
                        <div style="width: 100%; text-align: left; margin: 10px 0;">
                            <h5 style="color: var(--amd-dark); margin-bottom: 10px;"><i class="fas fa-lock"></i>
                                Login Information</h5>
                        </div>

                        <div class="grid-row grid-3">
                            <div class="amd-lms-login-input-group">
                                <input type="text" name="username"
                                    value="<?= htmlspecialchars($formdata['username'] ?? '') ?>" required
                                    placeholder=" ">
                                <label>User Name</label>
                            </div>
                            <div class="amd-lms-login-input-group" style="position: relative;">
                                <input type="password" name="password" required placeholder=" ">
                                <label>Password</label>
                                <span class="amd-eye-toggle togglePassword password-toggle-icon"
                                    style="position: absolute; top: 50%; right: 15px; transform: translateY(-50%); cursor: pointer; font-size: 1.3rem; color: var(--amd-muted);">
                                    <i class="fas fa-eye-slash password-icon"></i>
                                </span>
                            </div>
                            <div class="amd-lms-login-input-group" style="position: relative;">
                                <input type="password" name="password_confirmation" required placeholder=" ">
                                <label>Confirm Password</label>
                                <span class="amd-eye-toggle togglePassword password-toggle-icon"
                                    style="position: absolute; top: 50%; right: 15px; transform: translateY(-50%); cursor: pointer; font-size: 1.3rem; color: var(--amd-muted);">
                                    <i class="fas fa-eye-slash password-icon"></i>
                                </span>
                            </div>
                        </div>

                        <!-- Terms Checkbox -->
                        <div style="margin: 20px 0; text-align: left; max-width: 320px;">
                            <div class="form-check" style="display: flex; align-items: flex-start;">
                                <input class="form-check-input" type="checkbox" id="accept_pp" name="accept_pp"
                                    style="margin-top: 2px; width: 18px; height: 18px;">
                                <label class="form-check-label" for="accept_pp"
                                    style="font-size: 0.9rem; color: var(--amd-dark); line-height: 1.4; margin-left: 10px; cursor: pointer;">
                                    I accept the <a href="#" style="color: var(--amd-secondary);">Terms and Privacy
                                        Policy</a>
                                </label>
                            </div>
                        </div>

                        <input type="hidden" name="_token" value="tOppCeAZt7A371tz6TPnkWchjScgufiOMggqVQjA"
                            autocomplete="off">

                        <div class="text-center">
                            <button type="submit" class="amd-lms-login-submit-btn" id="registerSubmitBtn">
                                <i class="fas fa-user-plus me-2"></i> Create Account
                            </button>
                        </div>
                    </form>

                </div>
            </div>

            <!-- Image Container -->

        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const wwwroot = "<?php echo $CFG->wwwroot; ?>";
            const citizenDistrictSelect = document.getElementById('citizenship_district_dropdown');

            if (citizenDistrictSelect) {
                console.log("Fetching districts from: ", wwwroot + '/theme/mytheme/pages/ajax/locations.php?action=districts');

                fetch(wwwroot + '/theme/mytheme/pages/ajax/locations.php?action=districts')
                    .then(res => {
                        if (!res.ok) throw new Error('Network response was not ok');
                        return res.json();
                    })
                    .then(data => {
                        console.log("Districts received:", data); // Check if data is coming
                        citizenDistrictSelect.innerHTML = '<option value="">Select Issued District</option>';
                        if (data.length > 0) {
                            data.forEach(d => {
                                citizenDistrictSelect.innerHTML += `<option value="${d.id}">${d.name}</option>`;
                            });
                        } else {
                            console.warn("No districts found in database.");
                        }
                    })
                    .catch(err => console.error('Fetch error:', err));
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const wwwroot = "<?php echo $CFG->wwwroot; ?>";

            // ======================
            // PERMANENT ADDRESS
            // ======================
            const provinceSelect = document.querySelector('.state-dropdown');
            const districtSelect = document.querySelector('.district-dropdown');
            const muniSelect = document.querySelector('.municipality-dropdown');

            if (provinceSelect) {
                provinceSelect.addEventListener('change', function () {

                    fetch(wwwroot + '/theme/mytheme/pages/ajax/locations.php?action=districts&province_id=' + this.value)
                        .then(res => res.json())
                        .then(data => {

                            districtSelect.innerHTML = '<option value="">Select District</option>';
                            muniSelect.innerHTML = '<option value="">Select Municipality</option>';

                            data.forEach(d => {
                                districtSelect.innerHTML += `<option value="${d.id}">${d.name}</option>`;
                            });
                        });
                });
            }

            if (districtSelect) {
                districtSelect.addEventListener('change', function () {

                    fetch(wwwroot + '/theme/mytheme/pages/ajax/locations.php?action=municipalities&district_id=' + this.value)
                        .then(res => res.json())
                        .then(data => {

                            muniSelect.innerHTML = '<option value="">Select Municipality</option>';

                            data.forEach(m => {
                                muniSelect.innerHTML += `<option value="${m.id}">${m.name}</option>`;
                            });
                        });
                });
            }

            // ======================
            // TEMPORARY ADDRESS
            // ======================
            const tempProvince = document.querySelector('.temp-state-dropdown');
            const tempDistrict = document.querySelector('.temp-district-dropdown');
            const tempMuni = document.querySelector('.temp-municipality-dropdown');

            if (tempProvince) {
                tempProvince.addEventListener('change', function () {

                    fetch(wwwroot + '/theme/mytheme/pages/ajax/locations.php?action=districts&province_id=' + this.value)
                        .then(res => res.json())
                        .then(data => {

                            tempDistrict.innerHTML = '<option value="">Select District</option>';
                            tempMuni.innerHTML = '<option value="">Select Municipality</option>';

                            data.forEach(d => {
                                tempDistrict.innerHTML += `<option value="${d.id}">${d.name}</option>`;
                            });
                        });
                });
            }

            if (tempDistrict) {
                tempDistrict.addEventListener('change', function () {

                    fetch(wwwroot + '/theme/mytheme/pages/ajax/locations.php?action=municipalities&district_id=' + this.value)
                        .then(res => res.json())
                        .then(data => {

                            tempMuni.innerHTML = '<option value="">Select Municipality</option>';

                            data.forEach(m => {
                                tempMuni.innerHTML += `<option value="${m.id}">${m.name}</option>`;
                            });
                        });
                });
            }

        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const sameAddress = document.getElementById('sameAddress');

            sameAddress.addEventListener('change', async function () {

                if (!this.checked) return;

                const province = document.querySelector('.state-dropdown');
                const district = document.querySelector('.district-dropdown');
                const municipality = document.querySelector('.municipality-dropdown');

                const tempProvince = document.querySelector('.temp-state-dropdown');
                const tempDistrict = document.querySelector('.temp-district-dropdown');
                const tempMunicipality = document.querySelector('.temp-municipality-dropdown');

                const ward = document.querySelector('input[name="ward"]');
                const tole = document.querySelector('input[name="tole"]');

                const tempWard = document.querySelector('input[name="temp_ward"]');
                const tempTole = document.querySelector('input[name="temp_tole"]');

                // ======================
                // STEP 1: copy province
                // ======================
                tempProvince.value = province.value;

                // trigger district load
                await loadDistricts(province.value, tempDistrict, tempMunicipality);

                // ======================
                // STEP 2: copy district AFTER load
                // ======================
                tempDistrict.value = district.value;

                await loadMunicipalities(district.value, tempMunicipality);

                // ======================
                // STEP 3: copy final values
                // ======================
                tempMunicipality.value = municipality.value;

                tempWard.value = ward.value;
                tempTole.value = tole.value;

            });


            // ======================
            // Helper: load districts
            // ======================
            function loadDistricts(provinceId, districtSelect, muniSelect) {

                return fetch(`<?php echo $CFG->wwwroot; ?>/theme/mytheme/pages/ajax/locations.php?action=districts&province_id=${provinceId}`)
                    .then(res => res.json())
                    .then(data => {

                        districtSelect.innerHTML = '<option value="">Select District</option>';
                        muniSelect.innerHTML = '<option value="">Select Municipality</option>';

                        data.forEach(d => {
                            districtSelect.innerHTML += `<option value="${d.id}">${d.name}</option>`;
                        });

                    });
            }

            // ======================
            // Helper: load municipalities
            // ======================
            function loadMunicipalities(districtId, muniSelect) {

                return fetch(`<?php echo $CFG->wwwroot; ?>/theme/mytheme/pages/ajax/locations.php?action=municipalities&district_id=${districtId}`)
                    .then(res => res.json())
                    .then(data => {

                        muniSelect.innerHTML = '<option value="">Select Municipality</option>';

                        data.forEach(m => {
                            muniSelect.innerHTML += `<option value="${m.id}">${m.name}</option>`;
                        });

                    });
            }

        });
    </script>

    <script>
        document.querySelectorAll('.togglePassword').forEach(button => {
            button.addEventListener('click', function () {
                // 1. Toggle garne input field patta lagaune (teskai parent bhitra ko input)
                const input = this.parentElement.querySelector('input');

                // 2. Icon select garne
                const icon = this.querySelector('i');

                // 3. Logic: type toggle garne
                if (input.type === 'password') {
                    input.type = 'text';
                    // Icon change garne: Eye-slash bata Eye ma
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                } else {
                    input.type = 'password';
                    // Icon change garne: Eye bata Eye-slash ma
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                }
            });
        });
    </script>

    <script>
        function showGlobalError(message) {
            const box = document.getElementById('global-error-box');
            const msg = document.getElementById('global-error-message');

            msg.textContent = message;
            box.style.display = 'flex';

            // auto hide after 5 sec
            setTimeout(() => {
                box.style.display = 'none';
            }, 5000);
        }
    </script>


</body>

</html>
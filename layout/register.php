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

</head>

<body>
    <main class="p-4">
        <div class="amd-lms-login-main-container amd-lms-login-main-container-register">
            <!-- Form Container -->
            <div class="amd-lms-login-form-container">
                <div class="amd-login-top-part">
                    <a href="index2.html" class="amd-lms-course-detail-back-btn text-white">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back</span>
                    </a>

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
                            <div class="amd-lms-login-input-group"><input type="text" name="firstname" id="FirstName"
                                    required placeholder=" "><label for="FirstName">First Name<span
                                        class="text-danger mx-1">*</span></label></div>
                            <div class="amd-lms-login-input-group"><input type="text" name="middle_name" id="MiddleName"
                                    placeholder=" "><label for="MiddleName">Middle Name</label></div>
                            <div class="amd-lms-login-input-group"><input type="text" name="lastname" id="LastName"
                                    required placeholder=" "><label for="LastName">Last Name<span
                                        class="text-danger mx-1">*</span></label></div>
                        </div>

                        <div class="grid-row grid-2">
                            <div class="amd-lms-login-input-group">
                                <select name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                                <label>Gender<span class="text-danger mx-1">*</span></label>
                            </div>

                            <!-- Employment -->
                            <div class="amd-lms-login-input-group">
                                <select name="employed">
                                    <option value="">Employed?</option>
                                    <option value="yes">Yes</option>
                                    <option value="no">No</option>
                                </select>
                                <label>Employed?</label>
                            </div>
                        </div>


                        <div class="grid-row grid-2">
                            <!-- Citizenship -->
                            <div class="amd-lms-login-input-group">
                                <input type="text" name="citizenship_no" placeholder=" ">
                                <label>Citizenship No.</label>
                            </div>

                            <div class="amd-lms-login-input-group">
                                <input type="text" name="citizenship_district" placeholder=" ">
                                <label>Citizenship Issued District</label>
                            </div>
                        </div>

                        <!-- New NID/PAN fields row -->
                        <div class="grid-row grid-2">
                            <div class="amd-lms-login-input-group">
                                <input type="text" name="nid_no" id="NIDNo" placeholder=" " maxlength="16">
                                <label for="NIDNo">NID No</label>
                            </div>
                            <div class="amd-lms-login-input-group">
                                <input type="text" name="pan_no" id="PANNo" placeholder=" " maxlength="9">
                                <label for="PANNo">PAN No (9 digits)</label>
                            </div>
                        </div>

                        <div class="grid-row grid-1">
                            <div class="amd-lms-login-input-group">
                                <select name="ethnicity" id="ethnicity-select" class="ethnicity-dropdown" required>
                                    <option value="">Select Ethnicity</option>
                                    <option value="brahmin">Brahmin</option>
                                    <option value="chhetri">Chhetri</option>
                                    <option value="janajati">Janajati</option>
                                    <option value="indigenous">Indigenous</option>
                                    <option value="madheshi">Madheshi</option>
                                    <option value="dalit">Dalit</option>
                                    <option value="muslim">Muslim</option>
                                    <option value="others">Others</option>
                                </select>
                                <label>Ethnicity<span class="text-danger mx-1">*</span></label>
                                <input type="text" id="ethnicity-others-text" name="ethnicity_others"
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
                                    <option value="1">Koshi Province</option>
                                    <option value="2">Madhesh Province</option>
                                    <option value="3">Bagmati Province</option>
                                    <option value="4">Gandaki Province</option>
                                    <option value="5">Lumbini Province</option>
                                    <option value="6">Karnali Province</option>
                                    <option value="7">Sudurpashchim Province</option>
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
                            <div class="amd-lms-login-input-group"><input type="number" name="ward" placeholder=" "
                                    required><label>Permanent Ward<span class="text-danger mx-1">*</span></label></div>
                            <div class="amd-lms-login-input-group"><input type="text" name="tole"
                                    placeholder=" "><label>Permanent Tole / Street Name</label></div>
                        </div>

                        <!-- Same Address Checkbox -->
                        <div class="amd-checkbox-group" style="max-width: 320px; margin: 20px 0;">
                            <label
                                style="font-size: 1rem; color: var(--amd-dark); cursor: pointer; display: flex; align-items: center; align-items: center;">
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
                                    <option value="1">Koshi Province</option>
                                    <option value="2">Madhesh Province</option>
                                    <option value="3">Bagmati Province</option>
                                    <option value="4">Gandaki Province</option>
                                    <option value="5">Lumbini Province</option>
                                    <option value="6">Karnali Province</option>
                                    <option value="7">Sudurpashchim Province</option>
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
                            <div class="amd-lms-login-input-group"><input type="number" name="temp_ward" placeholder=" "
                                    required><label>Temporary Ward <span class="text-danger mx-1">*</span></label></div>
                            <div class="amd-lms-login-input-group"><input type="text" name="temp_tole"
                                    placeholder=" "><label>Temporary Tole / Street Name</label></div>
                        </div>

                        <!-- Contact & Professional Section -->
                        <div style="width: 100%; text-align: left; margin: 30px 0 20px 0;">
                            <h5 style="color: var(--amd-dark); margin-bottom: 10px;"><i class="fas fa-address-card"></i>
                                Contact & Professional Information</h5>
                        </div>
                        <div class="grid-row grid-3">
                            <div class="amd-lms-login-input-group"><input type="tel" name="phone_number"
                                    placeholder=" "><label>Phone Number</label></div>
                            <div class="amd-lms-login-input-group"><input type="tel" name="mobile_number"
                                    placeholder=" "><label>Mobile Number</label></div>
                            <div class="amd-lms-login-input-group"><input type="email" name="email" required
                                    placeholder=" "><label>Email Address<span class="text-danger mx-1">*</span></label>
                            </div>
                            <div class="amd-lms-login-input-group">
                                <input type="email" name="email2" required placeholder=" ">
                                <label>Confirm Email</label>
                            </div>

                            <div class="amd-lms-login-input-group">
                                <select name="age_group" id="AgeGroup" required>
                                    <option value="">-- Select Your Age Group --</option>
                                    <option value="1">Below 18</option>
                                    <option value="2">18-25</option>
                                    <option value="3">26-40</option>
                                    <option value="4">40+</option>
                                </select>
                                <label for="AgeGroup">Age Group<span class="text-danger mx-1">*</span></label>
                            </div>
                            <div class="amd-lms-login-input-group"><input type="text" name="organization_name"
                                    placeholder=" "><label>Organization Name</label></div>
                            <div class="amd-lms-login-input-group"><input type="text" name="qualification"
                                    placeholder=" "><label>Qualification</label></div>
                            <div class="amd-lms-login-input-group"><input type="text" name="designation"
                                    placeholder=" "><label>Designation</label></div>
                            <div class="amd-lms-login-input-group"><input type="text" name="expertise"
                                    placeholder=" "><label>Expertise</label></div>
                            <div class="amd-lms-login-input-group"><input type="number" name="years_experience" min="0"
                                    placeholder=" "><label>Years of Experience</label></div>

                        </div>



                        <!-- Login Information Section -->
                        <div style="width: 100%; text-align: left; margin: 10px 0;">
                            <h5 style="color: var(--amd-dark); margin-bottom: 10px;"><i class="fas fa-lock"></i>
                                Login
                                Information</h5>
                        </div>

                        <div class="grid-row grid-3">
                            <div class="amd-lms-login-input-group"><input type="text" name="username" required
                                    placeholder=" "><label>User Name</label></div>
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

</body>

</html>
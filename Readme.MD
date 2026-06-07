Cach CLear garne
http://localhost/moodle/admin/purgecaches.php

// lesson complete ko db

<!-- yo table ma basxa data // -->

-- course_modules_completion

// course complete vayapaxi
-- course_completions

<!-- self registration ko lagi -->

http://localhost/moodle/admin/settings.php?section=manageauths

---Self registration dropdown मा Email-based self-registration select गर्नुस्

## pdf vaneko resource ko file

## //certificate complete paxi matra dekhaune vanni kura

<!-- H5P integration library v127 -->

https://moodle.org/plugins/pluginversions.php?id=3429

// H5P content types install garne (BranchingScenario lagayat 32 types)
// XAMPP terminal ma run garne:
cd c:\xampp\htdocs\moodle && c:\xampp\php\php.exe admin/cli/scheduled_task.php --execute="\core\task\h5p_get_content_types_task"

Debugging garne tarika

<!-- pahile function banaune  -->

    public static function debug($var) {
        echo '<pre>';
        print_r($var);
        echo '</pre>';
    }


    <!-- ani call garne tarika  -->
    self::debug($url);

die();

<!-- Moodle ma h5p library download garne way  -->

cd c:/xampp/htdocs/moodle
c:\xampp\php\php.exe admin/cli/scheduled_task.php --execute="\core\task\h5p_get_content_types_task"

# certificate custom plugin version

- - mod_customcert_moodle45_2024042217

✅ Best Practice User Table ma Field thapne

Moodle le already solution deko xa:
Site administration → Users → Accounts → User profile fields

Tyaha bata:

New field add garna milxa:
Province
District
Municipality

# province district and mun

- yasko lagi aafnai plugin banako xu jasko name location xa

<!-- database ma data pathauna  -->

http://localhost/moodle/local/location/seed_provinces.php
http://localhost/moodle/local/location/seed_districts.php
http://localhost/moodle/local/location/seed_municipalities.php

<!-- extension -->

# Moodle pack

# new role banaune tarike

--- user-permission-define roles --

# role banauda System context enable garepaxi matra user lai role assign garna milxa

https://learntiti.amdsoft.com.np/my/ yaha jada default ma

# comment

6630187

pan number mandatory
select
citizenship district ma all district

# aba fill in the blank ni tanne quiz ma

# smtp setup garne tarika

-- Site administration → Server → Email → Outgoing mail configuration

# mark as complete button

-- yadi completion ma student mark as a complete manually tick lagako xa vani

# user changer self password change

# Happy Coding

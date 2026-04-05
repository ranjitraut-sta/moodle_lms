# Dashboard Refactor TODO

## Status: In Progress

**1. [COMPLETED] Backup original layout/dashbaord.php**
   - Created layout/dashbaord.php.backup
   
**2. [COMPLETED] Implement pages/dashboard.php**
   - Added Moodle PHP logic, data context, render templates
   
**3. [COMPLETED] Create templates/dashboard/main/sidebar.mustache**
   - Extracted sidebar HTML with mustache variables
   
**4. [COMPLETED] Create templates/dashboard/main/header.mustache**
   - Extracted header HTML with mustache variables
   
**5. [PENDING] Create templates/dashboard/main/main.mustache**
   - Extract main content with loops/data
   
**6. [PENDING] Update templates/dashboard/main/footer.mustache**
   - Add any scripts/closing
   
**7. [PENDING] Clear layout/dashbaord.php**
   - Comment out or remove monolithic code
   
**8. [PENDING] Purge Moodle caches**
   - Run php admin/cli/purcache.php
   
**9. [PENDING] Test dashboard**
   - Check /my/ or dashboard page, responsive, JS, data
   
**10. [COMPLETED] Mark all steps done**


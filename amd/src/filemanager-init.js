define(['core_filepicker'], function(FileManagerHelper) {
    return {
        init: function() {
            require(['core/first'], function() {
                var container = document.getElementById('filemanager_container');
                if (container) {
                    // DISABLED: Manual FileManagerHelper.init() conflicts with Moodle core_filepicker auto-init
                    // FileManagerHelper.init(); 
                }
            });
        }
    };
});
define(['jquery'], function($) {
    return {
        init: function() {
            // iframe पूर्ण रूपमा लोड भएपछि मात्र भित्रको तत्वहरू चलाउने
            $('#scorm_iframe').on('load', function() {
                try {
                    var iframeContents = $(this).contents();
                    
                    // १. "Scrom Package" (Title) लुकाउने
                    iframeContents.find('#mod-scorm-player-header').hide(); 
                    iframeContents.find('h2').first().hide(); // वैकल्पिक सुरक्षाको लागि
                    
                    // २. "Review mode" वा "Normal mode" टेक्स्ट लुकाउने
                    iframeContents.find('.scorm-mode-display').hide();
                    iframeContents.find('#scorm_mode').hide();
                    
                    // यदि Moodle को पुराना संस्करणहरू छन् भने प्लेन टेक्स्ट सिधै div बाट हटाउन:
                    iframeContents.find('div:contains("Review mode")').hide();
                    iframeContents.find('div:contains("Normal mode")').hide();
                    
                    // ३. माथिको खाली ठाउँ (Spacing) मिलाउने ताकि प्लेयर टपमा बसोस्
                    iframeContents.find('#page-content').css('margin-top', '0px');
                    iframeContents.find('#region-main').css('padding', '0px');

                } catch (e) {
                    console.log("Cross-origin restriction or iframe DOM issue: ", e);
                }
            });
        }
    };
});
function loadVideo(el) {
  const videoId = el.getAttribute("data-video-id");

  el.outerHTML = `
        <div class="video-wrapper is-playing">

            <iframe
                class="youtube-frame"
                src="https://www.youtube.com/embed/${videoId}?autoplay=1&rel=0&modestbranding=1&controls=0&enablejsapi=1"
                allow="autoplay; encrypted-media"
                allowfullscreen>
            </iframe>

            <div class="video-blocker"></div>

            <div class="custom-controls">
                
                <div class="progress-container">
                    <input type="range" class="progress-bar" min="0" max="100" value="0" step="0.1" oninput="seekVideo(this)">
                </div>

                <div class="control-buttons">

                    <button class="play-btn"
                            onclick="playIframeVideo(this)">
                        Play
                    </button>

                    <button class="pause-btn"
                            onclick="pauseIframeVideo(this)">
                        Pause
                    </button>

                </div>

            </div>

        </div>
    `;

  // YouTube API बाट भिडियोको समय (Current Time) र कुल समय (Duration) कति हो भनेर थाहा पाउन पोलिङ (Polling) सुरु गर्ने
  setTimeout(() => {
     setInterval(updateProgressBar, 500);
  }, 1000);
}

// भिडियोको प्रोग्रेस बार अपडेट गर्ने फङ्सन
function updateProgressBar() {
  const iframe = document.querySelector(".youtube-frame");
  if (!iframe) return;

  // YouTube लाई भिडियोको वर्तमान अवस्था सोध्ने
  iframe.contentWindow.postMessage(JSON.stringify({ event: "listening" }), "*");
  iframe.contentWindow.postMessage(JSON.stringify({ event: "command", func: "getCurrentTime" }), "*");
  iframe.contentWindow.postMessage(JSON.stringify({ event: "command", func: "getDuration" }), "*");
}

let videoDuration = 0;
let currentTime = 0;

// YouTube बाट आएको सन्देश सुन्ने र प्रोग्रेस बारको भ्यालु परिवर्तन गर्ने
window.addEventListener("message", function (event) {
  try {
     const data = JSON.parse(event.data);
     const progressBar = document.querySelector(".progress-bar");
     
     if (!progressBar) return;

     if (data.event === "infoDelivery" && data.info) {
         if (data.info.duration !== undefined) {
             videoDuration = data.info.duration;
         }
         if (data.info.currentTime !== undefined) {
             currentTime = data.info.currentTime;
             
             // प्रोग्रेस बारको प्रतिशत निकाल्ने
             if (videoDuration > 0) {
                 const percentage = (currentTime / videoDuration) * 100;
                 progressBar.value = percentage;
             }
         }
     }
  } catch (e) {
     // Non-JSON messages लाई इग्नोर गर्ने
  }
});

// प्रोग्रेस बार स्क्रोल (Drag) गर्दा भिडियो अगाडि पछाडि लैजाने फङ्सन
function seekVideo(slider) {
  const wrapper = slider.closest(".video-wrapper");
  const iframe = wrapper.querySelector(".youtube-frame");
  
  if (videoDuration > 0) {
      // स्लाइडरको % को आधारमा नयाँ सेकेन्ड निकाल्ने
      const newTime = (slider.value / 100) * videoDuration;
      
      iframe.contentWindow.postMessage(
        JSON.stringify({
          event: "command",
          func: "seekTo",
          args: [newTime, true],
        }),
        "*",
      );
  }
}

/* पुराना PLAY र PAUSE फङ्सनहरू जस्ताको तस्तै राख्नुहोस् */
function playIframeVideo(btn) {
  const wrapper = btn.closest(".video-wrapper");
  const iframe = wrapper.querySelector(".youtube-frame");
  iframe.contentWindow.postMessage(JSON.stringify({ event: "command", func: "playVideo", args: "" }), "*");
  wrapper.classList.remove("is-paused");
  wrapper.classList.add("is-playing");
}

function pauseIframeVideo(btn) {
  const wrapper = btn.closest(".video-wrapper");
  const iframe = wrapper.querySelector(".youtube-frame");
  iframe.contentWindow.postMessage(JSON.stringify({ event: "command", func: "pauseVideo", args: "" }), "*");
  wrapper.classList.remove("is-playing");
  wrapper.classList.add("is-paused");
}
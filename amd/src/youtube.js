// Youtube Video Play Huda Tesko Click lai hatako
function loadVideo(el) {
  const videoId = el.getAttribute("data-video-id");

  // सुरुमै Autoplay हुने भएकोले "is-playing" क्लास पहिल्यै थपिदिने
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
}

/*
|--------------------------------------------------------------------------
| PLAY VIDEO
|--------------------------------------------------------------------------
*/
function playIframeVideo(btn) {
  const wrapper = btn.closest(".video-wrapper");
  const iframe = wrapper.querySelector(".youtube-frame");

  iframe.contentWindow.postMessage(
    JSON.stringify({
      event: "command",
      func: "playVideo",
      args: "",
    }),
    "*",
  );

  // Class मिलाउने: playing देखिने, paused लुक्ने
  wrapper.classList.remove("is-paused");
  wrapper.classList.add("is-playing");
}

/*
|--------------------------------------------------------------------------
| PAUSE VIDEO
|--------------------------------------------------------------------------
*/
function pauseIframeVideo(btn) {
  const wrapper = btn.closest(".video-wrapper");
  const iframe = wrapper.querySelector(".youtube-frame");

  iframe.contentWindow.postMessage(
    JSON.stringify({
      event: "command",
      func: "pauseVideo",
      args: "",
    }),
    "*",
  );

  // Class मिलाउने: paused देखिने, playing लुक्ने
  wrapper.classList.remove("is-playing");
  wrapper.classList.add("is-paused");
}

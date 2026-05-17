(function () {
  "use strict";

  function initQuiz() {
    var wrappers = document.querySelectorAll(".amd-module-quiz");
    if (!wrappers.length) return;

    wrappers.forEach(function (wrap) {
      const ajaxurl = wrap.dataset.ajaxurl;
      const sesskey = wrap.dataset.sesskey;
      const quizid = wrap.dataset.quizid;
      const cmid = wrap.dataset.cmid;
      const currentAttemptId = parseInt(wrap.dataset.attemptid || "0", 10);

      // --- १. [[1]] लाई Drop Zones मा बदल्ने ---
      wrap.querySelectorAll(".amd-quiz-qtext").forEach(function (qtextElement) {
        let rawText = qtextElement.innerHTML;
        let updatedText = rawText.replace(
          /\[\[(\d+)\]\]/g,
          function (match, p1) {
            return `<span class="amd-ddwtos-inline-drop border d-inline-block mx-1" 
                          data-no="${p1}" 
                          style="min-width: 80px; height: 30px; vertical-align: middle; background: #f8f9fa; border-radius: 4px; text-align: center; line-height: 30px; padding: 0 5px;">
                    </span>`;
          },
        );
        qtextElement.innerHTML = updatedText;
      });

      // --- २. Drag and Drop Events ---
      const drags = wrap.querySelectorAll(".amd-ddwtos-drag-item");
      const drops = wrap.querySelectorAll(".amd-ddwtos-inline-drop");

      drags.forEach((drag) => {
        drag.addEventListener("dragstart", (e) => {
          e.dataTransfer.setData("text/plain", drag.innerText.trim());
          e.dataTransfer.setData("no", drag.dataset.no);
        });
      });

      drops.forEach((drop) => {
        drop.addEventListener("dragover", (e) => {
          e.preventDefault();
          drop.style.backgroundColor = "#e9ecef";
        });

        drop.addEventListener("dragleave", () => {
          drop.style.backgroundColor = "#f8f9fa";
        });

        drop.addEventListener("drop", (e) => {
          e.preventDefault();
          drop.style.backgroundColor = "#fff";

          const text = e.dataTransfer.getData("text/plain");
          const no = e.dataTransfer.getData("no");

          drop.innerText = text;
          drop.classList.add("dropped");
          drop.dataset.selectedNo = no; // सेभ गर्नको लागि भ्यालु राख्ने

          // Navigation dot मार्क गर्ने
          updateNavStatus(drop);
        });
      });

      // --- ३. MCQ & True/False Change Event ---
      wrap.addEventListener("change", function (e) {
        if (e.target.matches('input[type="radio"], input[type="checkbox"]')) {
          const qcard = e.target.closest(".amd-quiz-question");
          if (e.target.type === "radio") {
            qcard
              .querySelectorAll(".amd-lms-quiz-option")
              .forEach((opt) => opt.classList.remove("selected", "active"));
          }
          if (e.target.checked)
            e.target
              .closest(".amd-lms-quiz-option")
              .classList.add("selected", "active");

          updateNavStatus(e.target);
        }
      });

      function updateNavStatus(element) {
        const qcard = element.closest(".amd-quiz-question");
        const slot = qcard.dataset.slot;
        const navBtn = wrap.querySelector(
          `.amd-lms-quiz-qnav-btn[href="#question-${slot}"]`,
        );
        if (navBtn) navBtn.classList.add("answered");
      }

      // --- ४. Submit Logic (यही हो मुख्य भाग) ---
      const submitBtn = wrap.querySelector(".amd-quiz-submit-btn");
      if (submitBtn) {
        submitBtn.addEventListener("click", function (e) {
          e.preventDefault();
          let answers = [];

          // MCQ & True/False जम्मा गर्ने
          wrap
            .querySelectorAll(
              '.amd-quiz-question[data-type="multichoice"], .amd-quiz-question[data-type="truefalse"]',
            )
            .forEach((qcard) => {
              const slot = qcard.dataset.slot;
              qcard.querySelectorAll("input:checked").forEach((input) => {
                const option = input.closest(".amd-lms-quiz-option");
                answers.push({ slot: slot, answerid: option.dataset.answerid });
              });
            });

          // DDWTOS (Drag Drop) जम्मा गर्ने
          wrap
            .querySelectorAll('.amd-quiz-question[data-type="ddwtos"]')
            .forEach((qcard) => {
              const slot = qcard.dataset.slot;
              qcard
                .querySelectorAll(".amd-ddwtos-inline-drop")
                .forEach((drop) => {
                  if (drop.dataset.selectedNo) {
                    answers.push({
                      slot: slot,
                      ddwtosno: drop.dataset.no, // [[1]] को '1'
                      textans: drop.dataset.selectedNo, // drag item को 'no'
                    });
                  }
                });
            });

          if (
            answers.length === 0 &&
            !confirm("You haven't answered any questions. Submit anyway?")
          )
            return;

          submitBtn.disabled = true;
          submitBtn.innerText = "Submitting...";

          const formData = new URLSearchParams({
            action: "submit",
            quizid: quizid,
            cmid: cmid,
            sesskey: sesskey,
            attemptid: currentAttemptId,
            answers: JSON.stringify(answers),
          });

          fetch(ajaxurl, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: formData,
          })
            .then((r) => r.json())
            .then((data) => {
              if (data.success) location.reload();
              else {
                alert(data.error);
                submitBtn.disabled = false;
                submitBtn.innerText = "Submit Quiz";
              }
            })
            .catch((err) => {
              console.error(err);
              submitBtn.disabled = false;
            });
        });
      }

      // --- ५. Start/Retake Quiz Logic ---
      const startBtns = wrap.querySelectorAll(
        ".amd-quiz-start-btn, .amd-quiz-retake-btn",
      );
      startBtns.forEach((btn) => {
        btn.addEventListener("click", function () {
          btn.disabled = true;
          btn.innerText = "Processing...";
          fetch(ajaxurl, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({
              action: "start",
              quizid: quizid,
              cmid: cmid,
              sesskey: sesskey,
            }),
          })
            .then((r) => r.json())
            .then((data) => {
              if (data.success) location.reload();
              else alert(data.error || "Error starting quiz");
            });
        });
      });
    });
  }

  if (document.readyState === "loading")
    document.addEventListener("DOMContentLoaded", initQuiz);
  else initQuiz();
})();

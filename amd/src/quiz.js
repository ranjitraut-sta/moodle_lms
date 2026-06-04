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
      // --- ४. Submit Logic (Fixed Formatting for Moodle MCQ Matrix & DDWTOS Blanks) ---
      const submitBtns = wrap.querySelectorAll(".amd-quiz-submit-btn");
      submitBtns.forEach(submitBtn => {
        submitBtn.addEventListener("click", function (e) {
          e.preventDefault();
          let answers = [];

          // Process every single question card explicitly by its assigned type
          wrap.querySelectorAll(".amd-quiz-question").forEach((qcard) => {
            const slot = qcard.dataset.slot;
            const qtype = qcard.dataset.type;

            if (qtype === "multichoice") {
              // Is this a checkbox (multi) or radio (single)?
              const isMultiple =
                qcard.querySelectorAll('input[type="checkbox"]').length > 0;

              if (isMultiple) {
                // Send all options so we can construct a true/false matrix for the engine
                let optionsMap = {};
                qcard.querySelectorAll(".amd-mcq-option").forEach((opt) => {
                  const answerId = opt.dataset.answerid;
                  const isChecked = opt.querySelector("input").checked ? 1 : 0;
                  optionsMap[answerId] = isChecked;
                });

                answers.push({
                  slot: slot,
                  qtype: "multichoice_multi",
                  selections: optionsMap, // Map of {answerid: 1, answerid: 0, ...}
                });
              } else {
                // Single choice (radio)
                const checkedRadio = qcard.querySelector("input:checked");
                if (checkedRadio) {
                  const option = checkedRadio.closest(".amd-lms-quiz-option");
                  answers.push({
                    slot: slot,
                    qtype: "multichoice_single",
                    answerid: option.dataset.answerid,
                  });
                }
              }
            } else if (qtype === "truefalse") {
              const checkedRadio = qcard.querySelector("input:checked");
              if (checkedRadio) {
                const option = checkedRadio.closest(".amd-lms-quiz-option");
                answers.push({
                  slot: slot,
                  qtype: "truefalse",
                  answerid: option.dataset.answerid,
                });
              }
            } else if (qtype === "ddwtos") {
              let dropsMap = {};
              let hasDrops = false;

              qcard
                .querySelectorAll(".amd-ddwtos-inline-drop")
                .forEach((drop) => {
                  if (drop.dataset.selectedNo) {
                    const blankNo = drop.dataset.no; // standard layout place counter
                    const dragNo = drop.dataset.selectedNo; // assigned option counter
                    dropsMap[blankNo] = dragNo;
                    hasDrops = true;
                  }
                });

              if (hasDrops) {
                answers.push({
                  slot: slot,
                  qtype: "ddwtos",
                  drops: dropsMap, // Map of { blank_position: drag_option_no }
                });
              }
            }
          });

          if (
            answers.length === 0 &&
            !confirm("You haven't answered any questions. Submit anyway?")
          ) {
            return;
          }

          submitBtn.disabled = true;
          submitBtn.innerText = "Submitting...";

          const formData = new URLSearchParams({
            action: "submit",
            quizid: quizid,
            cmid: cmid,
            sesskey: sesskey,
            attemptid: currentAttemptId,
            answers: JSON.stringify(answers), // Formatted safely
          });

          fetch(ajaxurl, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: formData,
          })
            .then((r) => r.json())
            .then((data) => {
              if (data.success) {
                location.reload();
              } else {
                alert(data.error);
                submitBtn.disabled = false;
                submitBtn.innerText = "Submit Quiz";
              }
            })
            .catch((err) => {
              console.error(err);
              submitBtn.disabled = false;
              submitBtn.innerText = "Submit Quiz";
            });
        });
      });

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
      // --- ६. Pagination Logic ---
      const questions = wrap.querySelectorAll(".amd-quiz-question");
      if (questions.length > 0) {
        let currentIndex = 0;
        const prevBtn = wrap.querySelector("#amd-quiz-prev-btn");
        const nextBtn = wrap.querySelector("#amd-quiz-next-btn");
        const inlineSubmitBtn = wrap.querySelector("#amd-quiz-submit-btn-inline");
        
        const originalSubmitBtns = wrap.querySelectorAll(".amd-quiz-submit-btn:not(#amd-quiz-submit-btn-inline)");
        originalSubmitBtns.forEach(btn => btn.style.display = 'none');

        function showQuestion(index) {
            questions.forEach((q, i) => {
                if (i === index) {
                    q.classList.add("active");
                } else {
                    q.classList.remove("active");
                }
            });

            if (index === 0) {
                if(prevBtn) prevBtn.style.display = "none";
            } else {
                if(prevBtn) prevBtn.style.display = "inline-block";
            }

            if (index === questions.length - 1) {
                if(nextBtn) nextBtn.style.display = "none";
                if(inlineSubmitBtn) inlineSubmitBtn.style.display = "inline-block";
            } else {
                if(nextBtn) nextBtn.style.display = "inline-block";
                if(inlineSubmitBtn) inlineSubmitBtn.style.display = "none";
            }
        }

        if (prevBtn && nextBtn) {
            prevBtn.addEventListener("click", function(e) {
                e.preventDefault();
                if (currentIndex > 0) {
                    currentIndex--;
                    showQuestion(currentIndex);
                }
            });

            nextBtn.addEventListener("click", function(e) {
                e.preventDefault();
                if (currentIndex < questions.length - 1) {
                    currentIndex++;
                    showQuestion(currentIndex);
                }
            });
        }

        showQuestion(currentIndex);
      }
    });
  }

  if (document.readyState === "loading")
    document.addEventListener("DOMContentLoaded", initQuiz);
  else initQuiz();
})();

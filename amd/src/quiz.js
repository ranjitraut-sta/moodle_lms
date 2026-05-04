(function () {
  "use strict";

  function initQuiz() {
    var wrappers = document.querySelectorAll(".amd-module-quiz");
    if (!wrappers.length) return;

    wrappers.forEach(function (wrap) {
      var ajaxurl = wrap.dataset.ajaxurl;
      var sesskey = wrap.dataset.sesskey;
      var quizid = wrap.dataset.quizid;
      var cmid = wrap.dataset.cmid;
      var currentAttemptId = parseInt(wrap.dataset.attemptid || "0", 10);

      // --- Updated Selection Logic (MCQ & True/False) ---
      // Selection input vitra label vayeko vayale change event listen garnu best hunchha
      wrap.addEventListener("change", function (e) {
        var target = e.target;
        if (target.matches('input[type="radio"], input[type="checkbox"]')) {
          var optionLabel = target.closest(".amd-lms-quiz-option");
          var qcard = target.closest(".amd-quiz-question");
          var isSingle = qcard && qcard.dataset.mcqsingle === "1";
          var isRadio = target.type === "radio";

          if (isSingle || isRadio) {
            // Arko options bata selected class hataune
            qcard.querySelectorAll(".amd-lms-quiz-option").forEach(function (opt) {
              opt.classList.remove("selected", "active");
            });
          }

          if (target.checked) {
            optionLabel.classList.add("selected", "active");
          } else {
            optionLabel.classList.remove("selected", "active");
          }
          
          // Sidebar nav update (marked as answered)
          var slot = qcard.dataset.slot;
          var navBtn = wrap.querySelector('.amd-lms-quiz-qnav-btn[href="#question-' + slot + '"]');
          if (navBtn) navBtn.classList.add("answered");
        }
      });

      // --- DDWTOS Logic (Drag and Drop) ---
      wrap.querySelectorAll(".amd-ddwtos-question").forEach(function (ddwtos) {
        // ... (Keep your existing drag/drop logic here, it's fine)
      });

      // --- Start Quiz ---
      var startBtn = wrap.querySelector(".amd-quiz-start-btn");
      if (startBtn) {
        startBtn.addEventListener("click", function () {
          startBtn.disabled = true;
          startBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Starting...';

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
          .then(r => r.json())
          .then(data => {
            if (data.success) {
              location.reload(); // Hard reload is safer to sync Moodle state
            } else {
              alert(data.error || "Error starting quiz");
              startBtn.disabled = false;
            }
          });
        });
      }

      // --- Submit Quiz (Fixed Data Collection) ---
      var submitBtn = wrap.querySelector(".amd-quiz-submit-btn");
      if (submitBtn) {
        submitBtn.addEventListener("click", function (event) {
          event.preventDefault();
          var answers = [];

          wrap.querySelectorAll(".amd-quiz-question").forEach(function (qcard) {
            var slot = qcard.dataset.slot;
            var type = qcard.dataset.type;

            // Checked inputs bata answer uthaune
            var selectedInputs = qcard.querySelectorAll('input:checked');
            
            if (type === "multichoice" || type === "truefalse") {
              selectedInputs.forEach(function (input) {
                var label = input.closest(".amd-lms-quiz-option");
                answers.push({
                  slot: slot,
                  answerid: label.dataset.answerid
                });
              });
            } else if (type === "shortanswer") {
              var textVal = qcard.querySelector(".amd-shortans-input").value.trim();
              if (textVal) answers.push({ slot: slot, textans: textVal });
            }
          });

          if (!answers.length) {
            alert("Please answer at least one question.");
            return;
          }

          submitBtn.disabled = true;
          submitBtn.textContent = "Submitting...";

          fetch(ajaxurl, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({
              action: "submit",
              quizid: quizid,
              cmid: cmid,
              sesskey: sesskey,
              attemptid: currentAttemptId,
              answers: JSON.stringify(answers),
            }),
          })
          .then(r => r.json())
          .then(data => {
            if (data.success) {
               // Live Result UI update logic
               location.reload(); // Best practice for quiz completion
            } else {
               alert(data.error);
               submitBtn.disabled = false;
            }
          });
        });
      }
    });
  }

  // Init
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initQuiz);
  } else {
    initQuiz();
  }
})();
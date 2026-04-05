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

      // ── Initialize Inline DDWTOS (Fill in the blanks) ───────────────
      wrap.querySelectorAll(".amd-ddwtos-question").forEach(function (ddwtos) {
        var dragItems = ddwtos.querySelectorAll(".amd-ddwtos-drag-item");
        var inlineDrops = ddwtos.querySelectorAll(".amd-ddwtos-inline-drop");

        dragItems.forEach(function (item) {
          item.setAttribute("draggable", "true");

          item.addEventListener("dragstart", function (e) {
            e.dataTransfer.setData("text/plain", item.dataset.optionno);
            e.dataTransfer.setData("text/html", item.textContent.trim());
            item.classList.add("dragging");
          });

          item.addEventListener("dragend", function () {
            item.classList.remove("dragging");
          });

          item.addEventListener("click", function () {
            dragItems.forEach(function (i) {
              i.classList.remove("selected");
            });
            item.classList.add("selected");
          });
        });

        inlineDrops.forEach(function (drop) {
          var input = drop.querySelector(".amd-ddwtos-inline-input");
          if (!input) return;

          drop.addEventListener("dragover", function (e) {
            e.preventDefault();
            input.style.borderColor = "#0d6efd";
            input.style.backgroundColor = "#e7f1ff";
          });

          drop.addEventListener("dragleave", function () {
            input.style.borderColor = "#0d6efd";
            input.style.backgroundColor = "#fff";
          });

          drop.addEventListener("drop", function (e) {
            e.preventDefault();
            input.style.borderColor = "#198754";
            input.style.backgroundColor = "#d1e7dd";

            var optionText = e.dataTransfer.getData("text/html");
            var optionNo = e.dataTransfer.getData("text/plain");

            input.value = optionText;
            input.dataset.optionno = optionNo;
          });

          input.addEventListener("click", function () {
            var selected = ddwtos.querySelector(".amd-ddwtos-drag-item.selected");
            if (selected) {
              input.value = selected.textContent.trim();
              input.dataset.optionno = selected.dataset.optionno;
              input.style.borderColor = "#198754";
              input.style.backgroundColor = "#d1e7dd";
              selected.classList.remove("selected");
            }
          });

          input.addEventListener("dblclick", function () {
            input.value = "";
            input.dataset.optionno = "";
            input.style.borderColor = "#0d6efd";
            input.style.backgroundColor = "#fff";
          });
        });
      });

      // ── MCQ selection ────────────────────────────────────────────────
      wrap.querySelectorAll(".amd-mcq-options").forEach(function (ul) {
        ul.querySelectorAll(".amd-mcq-option").forEach(function (li) {
          li.addEventListener("click", function () {
            var qcard = li.closest(".amd-quiz-question");
            var isSingle = qcard && qcard.dataset.mcqsingle === "1";

            if (isSingle) {
              ul.querySelectorAll(".amd-mcq-option").forEach(function (o) {
                o.classList.remove("active", "list-group-item-primary");
                o.querySelector(".amd-mcq-marker").textContent = "○";
              });
              li.classList.add("active", "list-group-item-primary");
              li.querySelector(".amd-mcq-marker").textContent = "●";
            } else {
              var isActive = li.classList.toggle("active");
              li.classList.toggle("list-group-item-primary", isActive);
              li.querySelector(".amd-mcq-marker").textContent = isActive ? "●" : "○";
            }
          });
        });
      });

      // ── True/False selection ─────────────────────────────────────────
      wrap.querySelectorAll(".amd-tf-options").forEach(function (div) {
        div.querySelectorAll(".amd-tf-option").forEach(function (btn) {
          btn.addEventListener("click", function () {
            div.querySelectorAll(".amd-tf-option").forEach(function (b) {
              b.classList.remove("btn-primary");
              b.classList.add("btn-outline-secondary");
            });
            btn.classList.remove("btn-outline-secondary");
            btn.classList.add("btn-primary");
          });
        });
      });

      // ── Start quiz ───────────────────────────────────────────────────
      var startBtn = wrap.querySelector(".amd-quiz-start-btn");
      if (startBtn) {
        startBtn.addEventListener("click", function () {
          startBtn.disabled = true;
          startBtn.textContent = "Starting...";

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
            .then(function (r) {
              return r.json();
            })
            .then(function (data) {
              if (data.success) {
                currentAttemptId = data.attemptid;
                var startWrap = wrap.querySelector(".amd-quiz-start-wrap");
                if (startWrap) startWrap.classList.add("d-none");
                wrap.querySelector(".amd-quiz-body").classList.remove("d-none");
              } else {
                startBtn.disabled = false;
                startBtn.textContent = "Start Quiz";
                alert(data.error || "Could not start attempt. Please try again.");
              }
            })
            .catch(function (err) {
              console.error("Start error:", err);
              startBtn.disabled = false;
              startBtn.textContent = "Start Quiz";
              alert("Network error. Please try again.");
            });
        });
      }

      // ── Submit quiz ──────────────────────────────────────────────────
      var submitBtn = wrap.querySelector(".amd-quiz-submit-btn");
      if (submitBtn) {
        submitBtn.addEventListener("click", function (event) {
          event.preventDefault();

          var answers = [];

          wrap.querySelectorAll(".amd-quiz-question").forEach(function (qcard) {
            var slot = qcard.dataset.slot;
            var type = qcard.dataset.type;

            if (type === "multichoice") {
              var isSingle = qcard.dataset.mcqsingle === "1";
              
              if (isSingle) {
                var sel = qcard.querySelector(".amd-mcq-option.active");
                if (sel) {
                  answers.push({ 
                    slot: slot, 
                    answerid: sel.dataset.answerid 
                  });
                }
              } else {
                qcard.querySelectorAll(".amd-mcq-option.active").forEach(function (sel) {
                  answers.push({
                    slot: slot,
                    answerid: sel.dataset.answerid,
                  });
                });
              }
            } else if (type === "truefalse") {
              var sel2 = qcard.querySelector(".amd-tf-option.btn-primary");
              if (sel2) {
                answers.push({ 
                  slot: slot, 
                  answerid: sel2.dataset.answerid 
                });
              }
            } else if (type === "shortanswer") {
              var input = qcard.querySelector(".amd-shortans-input");
              if (input && input.value.trim()) {
                answers.push({ 
                  slot: slot, 
                  textans: input.value.trim() 
                });
              }
            } else if (type === "ddwtos") {
              qcard.querySelectorAll(".amd-ddwtos-inline-drop").forEach(function (drop) {
                var input = drop.querySelector(".amd-ddwtos-inline-input");
                var no = drop.dataset.no;
                
                if (input && input.dataset.optionno) {
                  answers.push({
                    slot: slot,
                    ddwtosno: no,
                    textans: input.dataset.optionno,
                  });
                }
              });
            }
          });

          console.log("Collected answers:", answers);

          if (!answers.length) {
            alert("Please answer at least one question before submitting.");
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
            .then(function (r) {
              return r.json();
            })
            .then(function (data) {
              console.log("Submit response:", data);
              
              if (!data.success) {
                alert("Submission failed: " + (data.error || "Unknown error"));
                submitBtn.disabled = false;
                submitBtn.textContent = "Submit Quiz";
                return;
              }

              // Hide quiz body
              wrap.querySelector(".amd-quiz-body").classList.add("d-none");
              
              // Hide start button area
              var startWrap = wrap.querySelector(".amd-quiz-start-wrap");
              if (startWrap) startWrap.classList.add("d-none");

              // Show result box with detailed info
              var resultBox = wrap.querySelector(".amd-quiz-live-result");
              resultBox.classList.remove("d-none", "alert-success", "alert-danger", "alert-warning");
              
              var statusClass = data.passed ? "alert-success" : "alert-warning";
              var statusText = data.passed ? "✅ Passed" : "⚠️ Needs Improvement";
              var statusIcon = data.passed ? "fa-check-circle" : "fa-exclamation-triangle";
              
              resultBox.classList.add(statusClass);
              resultBox.innerHTML = 
                '<div class="d-flex justify-content-between align-items-start mb-3">' +
                  '<div>' +
                    '<h5 class="mb-2"><i class="fas ' + statusIcon + ' me-2"></i>' + statusText + '</h5>' +
                    '<p class="mb-0"><strong>Marks:</strong> ' + data.score + ' / ' + data.maxgrade + '</p>' +
                    '<p class="mb-0"><strong>Grade:</strong> ' + data.quizgrade + ' out of ' + data.quizgrademax + ' (' + data.percentage + '%)</p>' +
                  '</div>' +
                '</div>' +
                '<div class="d-flex gap-2">' +
                  '<a href="' + data.reviewurl + '" class="btn btn-outline-primary btn-sm">' +
                    '<i class="fas fa-eye me-1"></i>Review Answers' +
                  '</a>' +
                  '<button onclick="location.reload()" class="btn btn-primary btn-sm">' +
                    '<i class="fas fa-redo me-1"></i>Retake Quiz' +
                  '</button>' +
                '</div>';
            })
            .catch(function (err) {
              console.error("Submit error:", err);
              submitBtn.disabled = false;
              submitBtn.textContent = "Submit Quiz";
              alert("Submission failed. Please try again.");
            });
        });
      }
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initQuiz);
  } else {
    initQuiz();
  }
})();
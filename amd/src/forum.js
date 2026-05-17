function toggleMainForm() {
  debugger;
  var form = document.getElementById("main-discussion-form");
  if (form) form.classList.toggle("visible");
}
function toggleReplyForm(id) {
    debugger;
    const form = document.getElementById("reply-form-" + id);
    console.log(form);

    if (!form) {
        if (window.console) console.log("Form not found for id:", id);
        return;
    }

    // जुध्ने सम्भावना भएको 'visible' को सट्टा हाम्रो आफ्नै 'show-form' क्लास टगल गर्ने
    form.classList.toggle("show-form");
}

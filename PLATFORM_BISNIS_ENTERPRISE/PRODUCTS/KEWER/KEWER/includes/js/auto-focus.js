// Auto-advance focus when select element is changed
document.addEventListener("DOMContentLoaded", function() {
    const selects = document.querySelectorAll("select");
    selects.forEach(function(select) {
        select.addEventListener("change", function() {
            // Find the next form element
            const form = this.form;
            if (form) {
                const elements = Array.from(form.elements);
                const currentIndex = elements.indexOf(this);
                
                // Find the next visible, non-disabled, non-readonly element
                for (let i = currentIndex + 1; i < elements.length; i++) {
                    const nextElement = elements[i];
                    if (nextElement &&
                        nextElement.tagName !== "BUTTON" &&
                        nextElement.type !== "hidden" &&
                        nextElement.type !== "submit" &&
                        !nextElement.disabled &&
                        !nextElement.readOnly &&
                        nextElement.offsetParent !== null) {
                        nextElement.focus();
                        break;
                    }
                }
            }
        });
    });
});

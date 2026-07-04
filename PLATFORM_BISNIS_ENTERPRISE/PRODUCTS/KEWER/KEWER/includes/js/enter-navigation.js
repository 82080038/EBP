// Enter key navigation - move focus to next field instead of submitting form
document.addEventListener("DOMContentLoaded", function() {
    const forms = document.querySelectorAll("form");
    
    forms.forEach(function(form) {
        form.addEventListener("keydown", function(event) {
            // Check if Enter key was pressed
            if (event.key === "Enter") {
                // Prevent default form submission
                event.preventDefault();
                
                // Get all form elements
                const elements = Array.from(form.elements);
                const currentIndex = elements.indexOf(event.target);
                
                // Find the next visible, non-disabled, non-readonly element
                for (let i = currentIndex + 1; i < elements.length; i++) {
                    const nextElement = elements[i];
                    if (nextElement &&
                        nextElement.tagName !== "BUTTON" &&
                        nextElement.type !== "hidden" &&
                        nextElement.type !== "submit" &&
                        nextElement.type !== "textarea" &&
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

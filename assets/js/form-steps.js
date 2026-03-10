/* assets/js/form-steps.js */
jQuery(document).ready(function ($) {
    const $form = $('#mpr-public-form');
    const $steps = $('.mpr-form-step');
    const $indicatorNodes = $('.mpr-step-node');
    let currentStep = 0;

    function updateStep() {
        $steps.removeClass('active');
        $steps.eq(currentStep).addClass('active');

        // Update progress nodes
        $indicatorNodes.each(function (index) {
            $(this).removeClass('active completed');
            if (index === currentStep) {
                $(this).addClass('active');
            } else if (index < currentStep) {
                $(this).addClass('completed');
            }
        });

        // Scroll to top of form
        $('html, body').animate({
            scrollTop: $("#mpr-public-form-container").offset().top - 100
        }, 500);
    }

    function validateStep(stepIndex) {
        let isValid = true;
        const $currentStepEl = $steps.eq(stepIndex);

        // Basic required field check
        $currentStepEl.find('input[required], select[required], textarea[required]').each(function () {
            if (!this.checkValidity()) {
                isValid = false;
                $(this).addClass('mpr-invalid');
                // Auto focus first invalid field
                if (!$(this).is(':focus')) {
                    this.reportValidity();
                }
            } else {
                $(this).removeClass('mpr-invalid');
            }
        });

        return isValid;
    }

    $('.mpr-btn-next').on('click', function (e) {
        e.preventDefault();
        if (validateStep(currentStep)) {
            currentStep++;
            if (currentStep >= $steps.length) currentStep = $steps.length - 1;
            updateStep();
        }
    });

    $('.mpr-btn-prev').on('click', function (e) {
        e.preventDefault();
        currentStep--;
        if (currentStep < 0) currentStep = 0;
        updateStep();
    });

    // Remove invalid class on input
    $form.on('input change', '.mpr-invalid', function () {
        if (this.checkValidity()) {
            $(this).removeClass('mpr-invalid');
        }
    });
});

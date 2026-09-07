/**
 * DriveRent - Live Price Calculator, QR Code Payment & Multi-Step Booking Script
 */

document.addEventListener('DOMContentLoaded', () => {
    const pickupDateInput = document.getElementById('pickup_date');
    const returnDateInput = document.getElementById('return_date');
    const pricePerDayVal = document.getElementById('car_price_per_day');

    // Display elements
    const dispRentalDays = document.getElementById('disp_rental_days');
    const dispSubtotal = document.getElementById('disp_subtotal');
    const dispTax = document.getElementById('disp_tax');
    const dispDeposit = document.getElementById('disp_deposit');
    const dispTotal = document.getElementById('disp_total');

    // Input hidden form fields
    const inputRentalDays = document.getElementById('input_rental_days');
    const inputSubtotal = document.getElementById('input_subtotal');
    const inputTax = document.getElementById('input_tax');
    const inputDeposit = document.getElementById('input_deposit');
    const inputTotal = document.getElementById('input_total_amount');

    // Set today as min date
    const todayStr = new Date().toISOString().split('T')[0];
    if (pickupDateInput) {
        pickupDateInput.min = todayStr;
        pickupDateInput.addEventListener('change', () => {
            if (returnDateInput) {
                returnDateInput.min = pickupDateInput.value;
                if (returnDateInput.value && returnDateInput.value < pickupDateInput.value) {
                    returnDateInput.value = pickupDateInput.value;
                }
            }
            calculateLivePrice();
        });
    }

    if (returnDateInput) {
        returnDateInput.min = todayStr;
        returnDateInput.addEventListener('change', calculateLivePrice);
    }

    function calculateLivePrice() {
        if (!pickupDateInput || !returnDateInput || !pricePerDayVal) return;

        const pickupDate = new Date(pickupDateInput.value);
        const returnDate = new Date(returnDateInput.value);
        const pricePerDay = parseFloat(pricePerDayVal.value) || 0;

        if (isNaN(pickupDate.getTime()) || isNaN(returnDate.getTime()) || returnDate < pickupDate) {
            return;
        }

        const diffTime = Math.abs(returnDate - pickupDate);
        let days = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        if (days < 1) days = 1; // minimum 1 day

        const subtotal = pricePerDay * days;
        const tax = parseFloat((subtotal * 0.18).toFixed(2)); // 18% GST
        const deposit = 5000.00;
        const total = subtotal + tax + deposit;

        // Format to INR Currency
        const fmt = (num) => '₹' + num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        if (dispRentalDays) dispRentalDays.innerText = days + (days === 1 ? ' Day' : ' Days');
        if (dispSubtotal) dispSubtotal.innerText = fmt(subtotal);
        if (dispTax) dispTax.innerText = fmt(tax);
        if (dispDeposit) dispDeposit.innerText = fmt(deposit);
        if (dispTotal) dispTotal.innerText = fmt(total);

        // Update hidden inputs
        if (inputRentalDays) inputRentalDays.value = days;
        if (inputSubtotal) inputSubtotal.value = subtotal.toFixed(2);
        if (inputTax) inputTax.value = tax.toFixed(2);
        if (inputDeposit) inputDeposit.value = deposit.toFixed(2);
        if (inputTotal) inputTotal.value = total.toFixed(2);

        // Update QR Code with exact total amount
        updateQrCodeUrl(total);
    }

    function updateQrCodeUrl(amount) {
        const upiQrImg = document.getElementById('upi_qr_image');
        if (upiQrImg) {
            const upiData = `upi://pay?pa=driverent@okaxis&pn=DriveRent+Auto&am=${amount.toFixed(2)}&cu=INR`;
            upiQrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(upiData)}`;
        }
    }

    // Trigger initial calculation if values present
    if (pickupDateInput && returnDateInput && pickupDateInput.value && returnDateInput.value) {
        calculateLivePrice();
    }

    // PAYMENT METHOD SWITCHER LOGIC
    const paymentRadios = document.querySelectorAll('.payment-radio');
    const panelUpi = document.getElementById('panel-upi');
    const panelCard = document.getElementById('panel-card');
    const panelNetbanking = document.getElementById('panel-netbanking');
    const panelCounter = document.getElementById('panel-counter');

    paymentRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            hideAllPaymentPanels();
            const val = radio.value;

            if (val === 'UPI Instant') {
                if (panelUpi) panelUpi.classList.remove('d-none');
            } else if (val === 'Credit / Debit Card') {
                if (panelCard) panelCard.classList.remove('d-none');
            } else if (val === 'NetBanking') {
                if (panelNetbanking) panelNetbanking.classList.remove('d-none');
            } else if (val === 'Pay at Pickup') {
                if (panelCounter) panelCounter.classList.remove('d-none');
            }
        });
    });

    function hideAllPaymentPanels() {
        if (panelUpi) panelUpi.classList.add('d-none');
        if (panelCard) panelCard.classList.add('d-none');
        if (panelNetbanking) panelNetbanking.classList.add('d-none');
        if (panelCounter) panelCounter.classList.add('d-none');
    }

    // QR TIMER COUNTDOWN (4:59)
    let qrTimeLeft = 299; // 5 minutes
    const qrTimerDisplay = document.getElementById('qr_timer');
    if (qrTimerDisplay) {
        setInterval(() => {
            if (qrTimeLeft <= 0) {
                qrTimeLeft = 300; // auto-refresh
            }
            qrTimeLeft--;
            const mins = Math.floor(qrTimeLeft / 60).toString().padStart(2, '0');
            const secs = (qrTimeLeft % 60).toString().padStart(2, '0');
            qrTimerDisplay.innerText = `${mins}:${secs}`;
        }, 1000);
    }

    // Multi-Step Stepper Navigation
    const stepNextBtns = document.querySelectorAll('.btn-step-next');
    const stepPrevBtns = document.querySelectorAll('.btn-step-prev');
    const bookingSteps = document.querySelectorAll('.booking-step-pane');
    const stepperItems = document.querySelectorAll('.step-item');

    stepNextBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const currentStep = parseInt(btn.getAttribute('data-step'));
            const targetStep = currentStep + 1;

            // Validate current step form inputs
            const currentPane = document.getElementById('step-pane-' + currentStep);
            if (currentPane) {
                const requiredInputs = currentPane.querySelectorAll('[required]');
                let valid = true;
                requiredInputs.forEach(input => {
                    if (!input.value.trim()) {
                        input.classList.add('is-invalid');
                        valid = false;
                    } else {
                        input.classList.remove('is-invalid');
                    }
                });

                if (!valid) {
                    if (typeof showToast === 'function') {
                        showToast('Missing Fields', 'Please fill in all required fields to proceed.', 'error');
                    }
                    return;
                }
            }

            // Update Summary on Step 5
            if (targetStep === 5) {
                const selectedMethod = document.querySelector('input[name="payment_method"]:checked')?.value || 'Instant UPI';
                const totalAmt = inputTotal ? parseFloat(inputTotal.value) || 0 : 0;

                const summaryMethod = document.getElementById('summary_payment_method');
                const summaryAmt = document.getElementById('summary_total_amount');

                if (summaryMethod) summaryMethod.innerText = selectedMethod;
                if (summaryAmt) summaryAmt.innerText = '₹' + totalAmt.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            switchStep(currentStep, targetStep);
        });
    });

    stepPrevBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const currentStep = parseInt(btn.getAttribute('data-step'));
            const targetStep = currentStep - 1;
            switchStep(currentStep, targetStep);
        });
    });

    // Allow jumping back to earlier steps by clicking stepper items
    stepperItems.forEach(item => {
        item.addEventListener('click', () => {
            const targetStep = parseInt(item.getAttribute('data-step-num'));
            const activeItem = document.querySelector('.step-item.active');
            const currentStep = activeItem ? parseInt(activeItem.getAttribute('data-step-num')) : 1;
            if (targetStep < currentStep) {
                switchStep(currentStep, targetStep);
            }
        });
    });

    function switchStep(fromStep, toStep) {
        bookingSteps.forEach(pane => pane.classList.add('d-none'));
        const targetPane = document.getElementById('step-pane-' + toStep);
        if (targetPane) targetPane.classList.remove('d-none');

        stepperItems.forEach(item => {
            const stepNum = parseInt(item.getAttribute('data-step-num'));
            if (stepNum === toStep) {
                item.classList.add('active');
                item.classList.remove('completed');
            } else if (stepNum < toStep) {
                item.classList.remove('active');
                item.classList.add('completed');
            } else {
                item.classList.remove('active', 'completed');
            }
        });

        window.scrollTo({ top: 150, behavior: 'smooth' });
    }

    // CONFIRM & PAY NOW BUTTON - INTERACTIVE PAYMENT MODAL
    const btnConfirmPayNow = document.getElementById('btnConfirmPayNow');
    const multiStepForm = document.getElementById('multiStepBookingForm');

    let isBookingSubmitting = false;

    if (btnConfirmPayNow && multiStepForm) {
        btnConfirmPayNow.addEventListener('click', (e) => {
            e.preventDefault();

            if (isBookingSubmitting) {
                return false;
            }

            const termsCheck = document.getElementById('termsCheck');
            if (termsCheck && !termsCheck.checked) {
                if (typeof showToast === 'function') {
                    showToast('Terms Required', 'Please accept the Terms & Conditions to proceed.', 'error');
                } else {
                    alert('Please accept the Terms & Conditions to proceed.');
                }
                return;
            }

            // Immediately lock button so user cannot double-click or submit 5 times
            isBookingSubmitting = true;
            btnConfirmPayNow.disabled = true;
            btnConfirmPayNow.style.pointerEvents = 'none';
            btnConfirmPayNow.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing Reservation...';

            const selectedMethod = document.querySelector('input[name="payment_method"]:checked')?.value || 'Instant UPI';
            const totalAmt = inputTotal ? parseFloat(inputTotal.value) || 0 : 0;
            const formattedTotal = '₹' + totalAmt.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            const modalPayMethod = document.getElementById('modal_pay_method');
            const modalPayAmount = document.getElementById('modal_pay_amount');
            const paymentModalElem = document.getElementById('paymentGatewayModal');

            if (modalPayMethod) modalPayMethod.innerText = selectedMethod;
            if (modalPayAmount) modalPayAmount.innerText = formattedTotal;

            if (paymentModalElem && typeof bootstrap !== 'undefined') {
                const modal = new bootstrap.Modal(paymentModalElem);
                modal.show();

                // Start Animated Payment Sequence
                const statusText = document.getElementById('payment_status_text');
                const stepDesc = document.getElementById('payment_step_desc');
                const progressBar = document.getElementById('paymentProgressBar');
                const procBox = document.getElementById('modal_payment_processing');
                const succBox = document.getElementById('modal_payment_success');

                if (procBox) procBox.classList.remove('d-none');
                if (succBox) succBox.classList.add('d-none');

                setTimeout(() => {
                    if (statusText) statusText.innerText = "Verifying UPI / Bank Transaction...";
                    if (stepDesc) stepDesc.innerText = "Receiving instant authorization from payment gateway...";
                    if (progressBar) progressBar.style.width = "65%";
                }, 1000);

                setTimeout(() => {
                    if (statusText) statusText.innerText = "Transaction Verified!";
                    if (progressBar) progressBar.style.width = "100%";
                    if (procBox) procBox.classList.add('d-none');
                    if (succBox) succBox.classList.remove('d-none');

                    // Submit form after 1.5 seconds
                    setTimeout(() => {
                        multiStepForm.submit();
                    }, 1200);
                }, 2000);
            } else {
                // Fallback direct submit if modal JS isn't loaded
                multiStepForm.submit();
            }
        });
    }
});

// GLOBAL HELPER FUNCTIONS
function copyUpiId() {
    const upiId = document.getElementById('merchant_upi_id')?.innerText || 'driverent@okaxis';
    navigator.clipboard.writeText(upiId).then(() => {
        if (typeof showToast === 'function') {
            showToast('Copied!', `UPI ID ${upiId} copied to clipboard.`, 'success');
        } else {
            alert(`UPI ID ${upiId} copied to clipboard!`);
        }
    });
}

function verifyVpa() {
    const vpaInput = document.getElementById('user_vpa_input');
    if (!vpaInput || !vpaInput.value.trim()) {
        alert('Please enter a valid UPI ID (e.g. name@upi)');
        return;
    }
    const val = vpaInput.value.trim();
    if (!val.includes('@')) {
        alert('Invalid UPI ID format. Must include @ (e.g. 9876543210@paytm)');
        return;
    }
    alert(`UPI ID "${val}" verified successfully! Click "Confirm & Pay Now" to complete payment.`);
}


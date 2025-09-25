document.addEventListener('DOMContentLoaded', () => {
    // --- Configuration ---
    const JAZZ_CASH_ACCOUNT = "03001234567"; // ⚠️ Your JazzCash account number
    const BACKEND_URL = "/.netlify/functions/verify";// Your server URL

    // --- DOM Elements ---
    const qrCodeElement = document.getElementById('qrcode');
    const amountInputElement = document.getElementById('amount-input'); // New amount input
    const tidInputElement = document.getElementById('tid-input');
    const verifyBtnElement = document.getElementById('verify-btn');
    const statusMessageElement = document.getElementById('status-message');

    // --- Generate QR Code ---
    if (qrCodeElement) {
        new QRCode(qrCodeElement, { text: JAZZ_CASH_ACCOUNT, width: 200, height: 200 });
    }

    // --- Event Listener for Verify Button ---
    if (verifyBtnElement) {
        verifyBtnElement.addEventListener('click', async () => {
            const amountPaid = amountInputElement.value.trim(); // Get amount from input
            const transactionId = tidInputElement.value.trim();

            if (!transactionId || !amountPaid) {
                alert("Please enter both the Amount and the Transaction ID.");
                return;
            }

            statusMessageElement.textContent = "Verifying, please wait...";
            statusMessageElement.className = '';
            verifyBtnElement.disabled = true;

            try {
                const response = await fetch(BACKEND_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        tid: transactionId,
                        amount: amountPaid // Send the user-entered amount
                    })
                });

                const result = await response.json();
                if (response.ok && result.status === 'success') {
                    statusMessageElement.textContent = result.message;
                    statusMessageElement.className = 'success';
                } else {
                    throw new Error(result.message || 'Verification failed.');
                }
            } catch (error) {
                statusMessageElement.textContent = `Error: ${error.message}`;
                statusMessageElement.className = 'error';
            } finally {
                verifyBtnElement.disabled = false;
            }
        });
    }
});
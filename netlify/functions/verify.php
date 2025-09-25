const imaps = require('imap-simple');

// --- Configuration ---
// IMPORTANT: For security, use Netlify Environment Variables for these, not hardcoded values.
const imapConfig = {
    imap: {
        user: 'wahidzaman786987@gmail.com',
        password: 'ahzj jzfy evkg qbte', // Your App Password
        host: 'imap.gmail.com',
        port: 993,
        tls: true,
        authTimeout: 3000
    }
};
const jazzcashSender = 'JazzCashAlert@jazzcash.com.pk';


// This is the main function Netlify will run
exports.handler = async (event, context) => {
    // Only allow POST requests
    if (event.httpMethod !== 'POST') {
        return { statusCode: 405, body: 'Method Not Allowed' };
    }

    try {
        const body = JSON.parse(event.body);
        const tid = body.tid;
        const amount = body.amount;

        if (!tid || !amount) {
            return {
                statusCode: 400,
                body: JSON.stringify({ message: 'TID and amount are required.' })
            };
        }

        const connection = await imaps.connect(imapConfig);
        await connection.openBox('INBOX');

        // Search for unread emails from the specific sender
        const searchCriteria = ['UNSEEN', ['FROM', jazzcashSender]];
        const fetchOptions = { bodies: ['TEXT'], markSeen: true };
        
        const messages = await connection.search(searchCriteria, fetchOptions);
        let paymentFound = false;

        for (const item of messages) {
            const emailBody = item.parts.filter(part => part.which === 'TEXT')[0].body;

            // Updated verification logic for the new email format
            const amountString = `of ${amount}PKR`;
            const tidString = `TID: ${tid}`;

            if (emailBody.includes(amountString) && emailBody.includes(tidString)) {
                paymentFound = true;
                break; // Exit loop once found
            }
        }
        
        connection.end();

        if (paymentFound) {
            return {
                statusCode: 200,
                body: JSON.stringify({ status: 'success', message: 'Payment confirmed successfully!' })
            };
        } else {
            return {
                statusCode: 404,
                body: JSON.stringify({ status: 'error', message: 'Payment not found. Check details or try again.' })
            };
        }

    } catch (error) {
        console.error('Error:', error);
        return {
            statusCode: 500,
            body: JSON.stringify({ status: 'error', message: 'An internal server error occurred.' })
        };
    }
};
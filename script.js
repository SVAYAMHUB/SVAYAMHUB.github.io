document.getElementById('payment-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const cardNumber = document.getElementById('card-number').value;
    const cardHolder = document.getElementById('card-holder').value;
    const expiryDate = document.getElementById('expiry-date').value;
    const cvv = document.getElementById('cvv').value;

    const paymentDetails = {
        cardNumber,
        cardHolder,
        expiryDate,
        cvv
    };

    fetch('/process_payment', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(paymentDetails)
    })
    .then(response => response.json())
    .then(data => {
        alert('Payment successful');
    })
    .catch(error => {
        alert('Payment failed');
    });
});

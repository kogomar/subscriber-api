document.getElementById('subscribeForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const email = document.getElementById('email').value;
    const repository = document.getElementById('repository').value;
    const apiKey = document.getElementById('apiKey').value;
    const btn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');
    const loader = document.getElementById('loader');
    const status = document.getElementById('statusMessage');

    status.style.display = 'none';
    status.className = 'status-message';
    loader.style.display = 'block';
    btnText.innerText = 'Processing...';
    btn.disabled = true;

    try {
        const response = await fetch('/api/subscribe', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'App-Api-Key': apiKey
            },
            body: JSON.stringify({ email, repository })
        });

        const data = await response.json();
        status.style.display = 'block';

        if (data.success) {
            status.classList.add('status-success');
            status.innerText = 'Successfully subscribed! Watch your inbox.';
            document.getElementById('subscribeForm').reset();
        } else {
            status.classList.add('status-error');
            if (Array.isArray(data.errors) && data.errors.length > 0) {
                if (data.errors.length === 1) {
                    status.innerText = data.errors[0];
                } else {
                    status.innerHTML = '<b>Errors:</b><ul style="text-align: left; margin-top: 5px; list-style-position: inside;">' +
                        data.errors.map(err => `<li>${err}</li>`).join('') + 
                        '</ul>';
                }
            } else {
                status.innerText = 'Something went wrong';
            }
        }
    } catch (err) {
        status.style.display = 'block';
        status.classList.add('status-error');
        status.innerText = 'Network error. Is the server running?';
    } finally {
        loader.style.display = 'none';
        btnText.innerText = 'Subscribe Now';
        btn.disabled = false;
    }
});

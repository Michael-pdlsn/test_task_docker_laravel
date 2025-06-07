document.getElementById('whois-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const domain = document.getElementById('domain').value;
    const resultEl = document.getElementById('whois-result');
    resultEl.textContent = '';
    fetch(whoisLookupRoute, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ domain })
    })
        .then(async res => {
            let data;
            try {
                data = await res.json();
            } catch (e) {
                resultEl.textContent = 'Помилка при запиті';
                return;
            }
            if (!res.ok) {
                resultEl.textContent = data.error || 'Помилка при запиті';
            } else if (data.data) {
                resultEl.textContent = data.data;
            } else {
                resultEl.textContent = 'Дані не знайдено';
            }
        })
        .catch(() => {
            resultEl.textContent = 'Помилка при запиті';
        });
});


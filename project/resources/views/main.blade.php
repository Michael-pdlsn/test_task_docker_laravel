<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>Whois</title>
    <link rel="stylesheet" href="../css/whois.css">
</head>
<body>
<div class="whois-form-container">
    <h1>Whois test</h1>
    <form id="whois-form">
        <input type="text" name="domain" id="domain" placeholder="Введіть домен" required>
        <button type="submit">Перевірити</button>
    </form>
    <pre id="whois-result"></pre>
</div>
<script>
    const whoisLookupRoute = "{{ route('whois.lookup') }}";
    const csrfToken = "{{ csrf_token() }}";
</script>
<script src="../js/whois.js"></script>
</body>
</html>


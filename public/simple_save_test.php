<!DOCTYPE html>
<html>
<head><title>Simple Funnel Save Test</title></head>
<body>
<h1>Simple Funnel Save</h1>
<form id="saveForm">
    <input type="hidden" name="id" value="2">
    <p>Name: <input type="text" name="name" value="WordPress Starter Kit Launch TEST"></p>
    <p>Goal: <select name="goal"><option value="download" selected>download</option></select></p>
    <p>Welcome Sequence: <input type="number" name="welcome_sequence_id" value="21"></p>
    <button type="submit">Save</button>
</form>
<h2 id="result"></h2>

<script>
document.getElementById('saveForm').addEventListener('submit', function(e) {
    e.preventDefault();
    let formData = new FormData(this);
    let data = {};
    formData.forEach((v, k) => data[k] = v);
    
    document.getElementById('result').innerText = 'Saving...';
    
    fetch('/simple-save-funnel/2', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(r => r.text())
    .then(text => {
        document.getElementById('result').innerText = 'Response: ' + text;
    })
    .catch(err => {
        document.getElementById('result').innerText = 'Error: ' + err;
    });
});
</script>
</body>
</html>
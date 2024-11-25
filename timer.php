<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<div id="datetime" style="position: fixed; top: 12px; right: 15px; z-index: 9999; font-size: 16px; font-weight: bold; color: #23A7AB;  padding: 5px 10px; "></div>

<script>
    function updateDateTime() {
        const now = new Date();
        const date = now.toLocaleDateString('en-US', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' });
        const time = now.toLocaleTimeString('en-US', { hour12: true, hour: 'numeric', minute: 'numeric' });

        const datetimeDisplay = document.getElementById('datetime');
        datetimeDisplay.textContent = `${date} ${time}`;
    }

    updateDateTime(); // Update date and time immediately on page load
    setInterval(updateDateTime, 1000); // Update date and time every second
</script>
</body>
</html>
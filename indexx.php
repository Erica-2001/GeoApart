<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Offline | Sorry!</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-6">
  <div class="bg-white rounded-2xl shadow-lg p-8 max-w-md text-center">
    <div class="mb-6">
      <!-- Optional: Add an image or icon here -->
      <svg class="w-20 h-20 mx-auto text-red-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M12 3C7.029 3 3 7.029 3 12s4.029 9 9 9 9-4.029 9-9-4.029-9-9-9z"/>
      </svg>
    </div>
    <h1 class="text-2xl font-bold text-gray-800 mb-2">Application is Offline</h1>
    <p class="text-gray-600 mb-6">We're sorry, it looks like the system is currently unavailable or you're offline. Please try again later.</p>
    <a href="/" class="bg-blue-600 text-white px-4 py-2 rounded-full hover:bg-blue-700 transition">Try Again</a>
  </div>
</body>
</html>

<script>
if (!navigator.onLine) {
  window.location.href = '/offline.html';
}
</script>